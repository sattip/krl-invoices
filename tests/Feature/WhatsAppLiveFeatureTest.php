<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use App\Models\WhatsAppSettings;
use App\Notifications\WhatsApp\InvoiceFailed;
use App\Notifications\WhatsApp\InvoiceProcessed;
use App\Notifications\WhatsApp\UsageWarning;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Regression cover for the live WaSenderAPI feature (9559c32) after the Greek/OBS merge. */
class WhatsAppLiveFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $company = Company::create(['name' => 'Synthetic WA Co', 'email' => 'wa-co@example.test', 'is_active' => true]);

        return User::factory()->create(['company_id' => $company->id, 'role' => 'owner']);
    }

    public function test_settings_page_and_greek_menu_item_render(): void
    {
        $owner = $this->owner();

        $this->actingAs($owner)->get(route('company.whatsapp'))
            ->assertOk()
            ->assertSee('Ρυθμίσεις WhatsApp');
    }

    public function test_api_key_and_webhook_secret_are_encrypted_and_readable(): void
    {
        $owner = $this->owner();
        $settings = WhatsAppSettings::forCompany($owner->company_id);
        $settings->update(['api_key' => 'synthetic-key-123', 'webhook_secret' => 'synthetic-secret-456']);

        $raw = $settings->fresh()->getAttributes();
        $this->assertNotSame('synthetic-key-123', $raw['api_key']);
        $this->assertSame('synthetic-key-123', $settings->fresh()->api_key);
        $this->assertSame('synthetic-secret-456', $settings->fresh()->webhook_secret);
        $this->assertNull(WhatsAppSettings::forCompany($owner->company_id)->fresh()->forceFill(['api_key' => null])->api_key);
    }

    public function test_failed_invoice_notification_goes_to_wasenderapi_only_when_enabled(): void
    {
        Http::fake(['api.wasenderapi.com/*' => Http::response(['success' => true], 200)]);
        $owner = $this->owner();
        $settings = WhatsAppSettings::forCompany($owner->company_id);

        $owner->notify(new InvoiceFailed('synthetic.pdf', 'Σφάλμα κατά την επεξεργασία. Δοκιμάστε ξανά.'));
        Http::assertNothingSent();

        $settings->update([
            'is_enabled' => true, 'api_key' => 'synthetic-key', 'phone_number' => '6900000000',
            'notification_settings' => array_fill_keys(array_keys(WhatsAppSettings::defaultNotificationSettings()), true),
        ]);
        $owner->notify(new InvoiceFailed('synthetic.pdf', 'Σφάλμα κατά την επεξεργασία. Δοκιμάστε ξανά.'));

        Http::assertSent(fn ($r) => str_contains($r->url(), 'api.wasenderapi.com/api/v1/messages/text')
            && $r['to'] === '306900000000'
            && str_contains($r['message'], 'synthetic.pdf'));
    }

    public function test_configured_settings_page_is_fully_greek_and_renders_without_network(): void
    {
        Http::fake();
        $owner = $this->owner();
        WhatsAppSettings::forCompany($owner->company_id)->update([
            'api_key' => 'synthetic-key', 'phone_number' => '306900000000', 'is_enabled' => true,
            'is_connected' => true, 'last_connected_at' => now(),
        ]);

        $html = $this->actingAs($owner)->get(route('company.whatsapp'))->assertOk()
            ->assertSee('Σύνδεση με το WaSenderAPI')
            ->assertSee('Κλειδί API του WaSenderAPI')
            ->assertSee('Ποιες ειδοποιήσεις θα λαμβάνετε')
            ->assertSee('Ενεργοποίηση ειδοποιήσεων WhatsApp')
            ->assertSee('Αποθήκευση ρυθμίσεων')
            ->assertSee('Δοκιμαστικό μήνυμα')
            ->assertSee('Αποστολή δοκιμαστικού μηνύματος')
            ->assertSee('Κατάσταση σύνδεσης')
            ->assertSee('Συνδεδεμένο')
            ->assertSee('Έλεγχος σύνδεσης')
            ->assertSee('Οδηγός ρύθμισης')
            ->assertSee('Έχει οριστεί')
            ->assertSee('https://wasenderapi.com', false)
            ->assertSee('name="api_key"', false)
            ->assertSee('name="notification_settings[invoice_processed]"', false)
            ->getContent();

        foreach (['API Configuration', 'Save Settings', 'Send Test Message', 'Connection Status', 'Test Connection',
            'Setup Guide', 'Notification Settings', 'Enable WhatsApp Notifications', 'Not Configured', 'Not Set',
            'Default Recipient Phone', 'Hello from InvoiceAI', 'Testing Connection', 'Message Sent', 'Failed to Send',
            'Please try again', 'Last connected', 'Get your API key'] as $english) {
            $this->assertStringNotContainsString($english, $html, "English UI text left: {$english}");
        }
        $this->assertStringNotContainsString('synthetic-key', $html, 'API key must never be rendered');
        Http::assertNothingSent();
    }

    public function test_update_flash_and_test_endpoints_are_greek_and_hide_provider_errors(): void
    {
        Http::fake(['api.wasenderapi.com/*' => Http::response(['message' => 'Synthetic provider error'], 401)]);
        $owner = $this->owner();

        $this->actingAs($owner)->put(route('company.whatsapp.update'), ['phone_number' => '306900000000', 'is_enabled' => 1])
            ->assertRedirect(route('company.whatsapp'))->assertSessionHas('success', 'Οι ρυθμίσεις WhatsApp αποθηκεύτηκαν.');

        WhatsAppSettings::forCompany($owner->company_id)->update(['api_key' => 'synthetic-key']);

        $this->actingAs($owner)->postJson(route('company.whatsapp.test'))
            ->assertJson(['success' => false])
            ->assertJsonPath('message', 'Η σύνδεση με το WaSenderAPI απέτυχε. Ελέγξτε το κλειδί API και δοκιμάστε ξανά.');
        $this->actingAs($owner)->postJson(route('company.whatsapp.send-test'), ['phone' => '306900000000', 'message' => 'Δοκιμή'])
            ->assertJson(['success' => false])
            ->assertJsonMissing(['message' => 'Synthetic provider error']);
        Http::assertSent(fn ($r) => str_starts_with($r->url(), 'https://api.wasenderapi.com/api/v1/'));
    }

    public function test_whatsapp_message_texts_are_greek(): void
    {
        $owner = $this->owner();
        $invoice = new Invoice(['invoice_number' => 'Α-1', 'issuer_name' => 'Συνθετικός Εκδότης', 'grand_total' => 124, 'currency' => 'EUR']);

        $processed = (new InvoiceProcessed($invoice))->toWhatsApp($owner);
        $this->assertStringContainsString('Το τιμολόγιο επεξεργάστηκε', $processed);
        $this->assertStringContainsString('Συνθετικός Εκδότης', $processed);
        $this->assertStringContainsString('Αποτυχία επεξεργασίας τιμολογίου', (new InvoiceFailed('a.pdf', 'x'))->toWhatsApp($owner));
        $this->assertStringContainsString('μηνιαίου ορίου', (new UsageWarning(40, 50, 80))->toWhatsApp($owner));
    }
}
