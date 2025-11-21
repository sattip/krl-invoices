<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Mail\WelcomeCompanyOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CompanyWizardController extends Controller
{
    /**
     * Step 1: Company Information
     */
    public function step1()
    {
        $wizardData = session('company_wizard', []);
        
        return view('admin.companies.wizard.step1', [
            'wizardData' => $wizardData,
        ]);
    }

    /**
     * Store Step 1 data
     */
    public function storeStep1(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:500',
            'company_vat' => 'nullable|string|max:50',
        ]);

        $wizardData = session('company_wizard', []);
        $wizardData = array_merge($wizardData, $validated);
        session(['company_wizard' => $wizardData]);

        return redirect()->route('admin.companies.wizard.step2');
    }

    /**
     * Step 2: Owner Account
     */
    public function step2()
    {
        $wizardData = session('company_wizard', []);
        
        if (empty($wizardData['company_name'])) {
            return redirect()->route('admin.companies.wizard.step1')
                ->with('error', 'Please complete step 1 first.');
        }

        return view('admin.companies.wizard.step2', [
            'wizardData' => $wizardData,
        ]);
    }

    /**
     * Store Step 2 data
     */
    public function storeStep2(Request $request)
    {
        $validated = $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255|unique:users,email',
            'owner_password' => 'nullable|string|min:8',
            'auto_generate_password' => 'nullable|boolean',
        ]);

        // Generate password if requested
        if ($request->boolean('auto_generate_password') || empty($validated['owner_password'])) {
            $validated['owner_password'] = Str::random(12);
            $validated['password_was_generated'] = true;
        } else {
            $validated['password_was_generated'] = false;
        }

        $wizardData = session('company_wizard', []);
        $wizardData = array_merge($wizardData, $validated);
        session(['company_wizard' => $wizardData]);

        return redirect()->route('admin.companies.wizard.step3');
    }

    /**
     * Step 3: Plan & Grace Period
     */
    public function step3()
    {
        $wizardData = session('company_wizard', []);
        
        if (empty($wizardData['owner_email'])) {
            return redirect()->route('admin.companies.wizard.step2')
                ->with('error', 'Please complete step 2 first.');
        }

        $plans = Plan::active()->ordered()->get();

        return view('admin.companies.wizard.step3', [
            'wizardData' => $wizardData,
            'plans' => $plans,
        ]);
    }

    /**
     * Store Step 3 data and create everything
     */
    public function storeStep3(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'grace_period_end' => 'required|date|after:today',
        ]);

        $wizardData = session('company_wizard', []);
        
        if (empty($wizardData['owner_email'])) {
            return redirect()->route('admin.companies.wizard.step1')
                ->with('error', 'Session expired. Please start again.');
        }

        $wizardData = array_merge($wizardData, $validated);

        try {
            DB::transaction(function () use ($wizardData) {
                // Create Company
                $company = Company::create([
                    'name' => $wizardData['company_name'],
                    'email' => $wizardData['company_email'] ?? null,
                    'phone' => $wizardData['company_phone'] ?? null,
                    'address' => $wizardData['company_address'] ?? null,
                    'vat_number' => $wizardData['company_vat'] ?? null,
                    'is_active' => true,
                ]);

                // Create Owner User
                $user = User::create([
                    'name' => $wizardData['owner_name'],
                    'email' => $wizardData['owner_email'],
                    'password' => Hash::make($wizardData['owner_password']),
                    'company_id' => $company->id,
                    'role' => 'owner',
                ]);

                // Create Manual Subscription
                $plan = Plan::find($wizardData['plan_id']);
                
                Subscription::create([
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'stripe_subscription_id' => 'manual_' . Str::uuid(),
                    'stripe_customer_id' => null,
                    'status' => 'active',
                    'is_manual' => true,
                    'grace_period_end' => $wizardData['grace_period_end'],
                    'current_period_start' => now(),
                    'current_period_end' => $wizardData['grace_period_end'],
                ]);

                // Store created data in session for complete page
                session(['company_wizard_result' => [
                    'company' => $company,
                    'user' => $user,
                    'plan' => $plan,
                    'password' => $wizardData['owner_password'],
                    'password_was_generated' => $wizardData['password_was_generated'] ?? false,
                    'grace_period_end' => $wizardData['grace_period_end'],
                ]]);

                // Send welcome email
                Mail::to($user->email)->queue(new WelcomeCompanyOwner(
                    $user,
                    $company,
                    $plan,
                    $wizardData['owner_password'],
                    $wizardData['grace_period_end']
                ));
            });

            // Clear wizard session data
            session()->forget('company_wizard');

            return redirect()->route('admin.companies.wizard.complete');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create company: ' . $e->getMessage());
        }
    }

    /**
     * Complete: Show success page
     */
    public function complete()
    {
        $result = session('company_wizard_result');
        
        if (!$result) {
            return redirect()->route('admin.companies.index')
                ->with('error', 'No wizard data found.');
        }

        // Clear result from session after displaying
        session()->forget('company_wizard_result');

        return view('admin.companies.wizard.complete', [
            'company' => $result['company'],
            'user' => $result['user'],
            'plan' => $result['plan'],
            'password' => $result['password'],
            'passwordWasGenerated' => $result['password_was_generated'],
            'gracePeriodEnd' => $result['grace_period_end'],
        ]);
    }

    /**
     * Cancel wizard and clear session
     */
    public function cancel()
    {
        session()->forget('company_wizard');
        session()->forget('company_wizard_result');
        
        return redirect()->route('admin.companies.index')
            ->with('info', 'Company creation cancelled.');
    }
}
