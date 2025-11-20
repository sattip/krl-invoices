<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Display company settings for the current user's company.
     */
    public function settings()
    {
        $company = current_company();

        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not associated with any company.');
        }

        return view('company.settings', compact('company'));
    }

    /**
     * Update company settings.
     */
    public function updateSettings(Request $request)
    {
        $company = current_company();

        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not associated with any company.');
        }

        // Only owners and admins can update settings
        if (!$request->user()->isAdmin()) {
            abort(403, 'Only company administrators can update settings.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'vat_number' => 'nullable|string|max:50',
        ]);

        $company->update($validated);

        return redirect()->route('company.settings')
            ->with('success', 'Company settings updated successfully.');
    }

    /**
     * Display all companies (super admin only).
     */
    public function index()
    {
        $companies = Company::withCount(['users', 'invoices'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        return view('admin.companies.create');
    }

    /**
     * Store a new company.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'vat_number' => 'nullable|string|max:50',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255|unique:users,email',
            'owner_password' => 'required|string|min:8',
        ]);

        $company = DB::transaction(function () use ($validated) {
            $company = Company::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'vat_number' => $validated['vat_number'] ?? null,
            ]);

            User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => bcrypt($validated['owner_password']),
                'company_id' => $company->id,
                'role' => 'owner',
            ]);

            return $company;
        });

        return redirect()->route('admin.companies.index')
            ->with('success', "Company '{$company->name}' created successfully.");
    }

    /**
     * Show company details.
     */
    public function show(Company $company)
    {
        $company->load(['users', 'invoices' => function ($query) {
            $query->withoutGlobalScopes()->latest()->take(10);
        }]);

        return view('admin.companies.show', compact('company'));
    }

    /**
     * Show the form for editing a company.
     */
    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update a company.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'vat_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $company->update($validated);

        return redirect()->route('admin.companies.index')
            ->with('success', "Company '{$company->name}' updated successfully.");
    }

    /**
     * Assign an existing user to a company.
     */
    public function assignUser(Request $request, Company $company)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:owner,admin,member',
        ]);

        $user = User::find($validated['user_id']);
        $user->update([
            'company_id' => $company->id,
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.companies.show', $company)
            ->with('success', "User '{$user->name}' assigned to company.");
    }
}
