<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\WhatsAppSettings;
use App\Notifications\WhatsApp\InvoiceFailed;
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
}
