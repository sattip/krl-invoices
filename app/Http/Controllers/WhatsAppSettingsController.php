<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppSettings;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppSettingsController extends Controller
{
    /**
     * Display the WhatsApp settings form.
     */
    public function index()
    {
        $company = current_company();

        if (! $company) {
            return redirect()->route('dashboard')
                ->with('error', 'Ο λογαριασμός σας δεν είναι συνδεδεμένος με εταιρεία.');
        }

        $settings = WhatsAppSettings::forCompany($company->id);

        return view('company.whatsapp-settings', compact('settings', 'company'));
    }

    /**
     * Update WhatsApp settings.
     */
    public function update(Request $request)
    {
        $company = current_company();

        if (! $company) {
            return redirect()->route('dashboard')
                ->with('error', 'Ο λογαριασμός σας δεν είναι συνδεδεμένος με εταιρεία.');
        }

        // Only owners and admins can update settings
        if (! $request->user()->isAdmin()) {
            abort(403, 'Μόνο οι διαχειριστές της εταιρείας μπορούν να αλλάξουν τις ρυθμίσεις WhatsApp.');
        }

        $validated = $request->validate([
            'api_key' => 'nullable|string|max:500',
            'session_id' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'phone_display_name' => 'nullable|string|max:255',
            'is_enabled' => 'boolean',
            'notification_settings' => 'array',
            'notification_settings.*' => 'boolean',
        ]);

        $settings = WhatsAppSettings::forCompany($company->id);

        // Only update API key if a new one is provided
        if (! empty($validated['api_key'])) {
            $settings->api_key = $validated['api_key'];
        }

        $settings->fill([
            'session_id' => $validated['session_id'] ?? $settings->session_id,
            'phone_number' => $validated['phone_number'] ?? $settings->phone_number,
            'phone_display_name' => $validated['phone_display_name'] ?? $settings->phone_display_name,
            'is_enabled' => $validated['is_enabled'] ?? false,
            'notification_settings' => $validated['notification_settings'] ?? WhatsAppSettings::defaultNotificationSettings(),
        ]);

        $settings->save();

        return redirect()->route('company.whatsapp')
            ->with('success', 'Οι ρυθμίσεις WhatsApp αποθηκεύτηκαν.');
    }

    /**
     * Test WhatsApp connection.
     */
    public function testConnection(Request $request)
    {
        $company = current_company();

        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Ο λογαριασμός σας δεν είναι συνδεδεμένος με εταιρεία.'], 400);
        }

        $settings = WhatsAppSettings::forCompany($company->id);

        if (! $settings->api_key) {
            return response()->json(['success' => false, 'message' => 'API key not configured.'], 400);
        }

        try {
            $service = new WhatsAppService($company);
            $result = $service->testConnection();

            if ($result['success']) {
                $settings->update([
                    'is_connected' => true,
                    'connection_status' => 'connected',
                    'last_connected_at' => now(),
                    'account_details' => $result['data'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Η σύνδεση με το WaSenderAPI πέτυχε.',
                    'data' => $result['data'] ?? null,
                ]);
            }

            $settings->update([
                'is_connected' => false,
                'connection_status' => 'failed',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Η σύνδεση με το WaSenderAPI απέτυχε. Ελέγξτε το κλειδί API και δοκιμάστε ξανά.',
            ], 400);

        } catch (\Exception $e) {
            Log::error('WhatsApp connection test failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            $settings->update([
                'is_connected' => false,
                'connection_status' => 'error',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Δεν ήταν δυνατός ο έλεγχος της σύνδεσης. Δοκιμάστε ξανά αργότερα.',
            ], 500);
        }
    }

    /**
     * Send a test message.
     */
    public function sendTestMessage(Request $request)
    {
        $company = current_company();

        if (! $company) {
            return response()->json(['success' => false, 'message' => 'Ο λογαριασμός σας δεν είναι συνδεδεμένος με εταιρεία.'], 400);
        }

        $validated = $request->validate([
            'phone' => 'required|string|max:50',
            'message' => 'required|string|max:1000',
        ]);

        try {
            $service = new WhatsAppService($company);
            $result = $service->sendMessage($validated['phone'], $validated['message']);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Το δοκιμαστικό μήνυμα στάλθηκε.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Η αποστολή απέτυχε. Ελέγξτε τον αριθμό και τη σύνδεση με το WaSenderAPI.',
            ], 400);

        } catch (\Exception $e) {
            Log::error('WhatsApp test message failed', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Δεν ήταν δυνατή η αποστολή. Δοκιμάστε ξανά αργότερα.',
            ], 500);
        }
    }
}
