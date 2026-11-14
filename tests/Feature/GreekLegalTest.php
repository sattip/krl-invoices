<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Greek UI, legal pages, explicit Terms acceptance and private invoice files.
 * Synthetic data only; no provider (Anthropic/Stripe) calls are made.
 */
class GreekLegalTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_render_in_greek_with_company_facts(): void
    {
        foreach (['/terms' => 'Όροι Χρήσης', '/privacy' => 'Πολιτική Απορρήτου', '/cookies' => 'Πολιτική Cookies', '/contact' => 'Επικοινωνία'] as $url => $heading) {
            $response = $this->get($url);
            $response->assertOk()
                ->assertSee('<html lang="el">', false)
                ->assertSee($heading)
                ->assertSee('OBS TECHNOLOGIES Ε.Ε.')
                ->assertSee('801807920')
                ->assertSee('support@obstechnologies.com');
        }

        $this->get('/oroi-xrisis')->assertRedirect('/terms');
        $this->get('/politiki-aporritou')->assertRedirect('/privacy');
    }

    public function test_homepage_is_greek_and_has_no_unsupported_claims(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('<html lang="el">', false)
            ->assertSee(route('legal.terms'), false)
            ->assertSee(route('legal.privacy'), false)
            ->assertSee(route('legal.cookies'), false)
            ->assertSee('ΑΦΜ 801807920');

        foreach (['99.5%', '10,000', '500+', 'money-back', 'Free Trial', 'No credit card', 'Maria K.', 'BLACKFRIDAY', 'fonts.googleapis.com'] as $claim) {
            $response->assertDontSee($claim, false);
        }
    }

    public function test_auth_pages_are_greek_and_link_legal_pages(): void
    {
        $this->get('/login')->assertOk()->assertSee('<html lang="el">', false)
            ->assertSee('Σύνδεση')->assertSee(route('legal.privacy'), false);

        $this->get('/register')->assertOk()->assertSee('<html lang="el">', false)
            ->assertSee('Αποδέχομαι τους')
            ->assertSee(route('legal.terms'), false)
            ->assertDontSee('free trial', false);
    }

    public function test_registration_requires_explicit_terms_acceptance(): void
    {
        $plan = $this->plan();

        $this->post('/register', $this->registration($plan))
            ->assertSessionHasErrors(['terms' => 'Για να συνεχίσετε πρέπει να αποδεχτείτε τους Όρους Χρήσης.']);

        $this->assertNull(session('registration'));
    }

    public function test_registration_session_keeps_hash_not_plaintext_password(): void
    {
        $plan = $this->plan();

        $this->post('/register', $this->registration($plan) + ['terms' => '1'])
            ->assertRedirect(route('register.checkout'));

        $registration = session('registration');
        $this->assertArrayNotHasKey('password', $registration);
        $this->assertTrue(password_verify('Synthetic-Pass-2026!', $registration['password_hash']));
        $this->assertSame(config('legal.terms_version'), $registration['terms_version']);
        $this->assertNotEmpty($registration['terms_accepted_at']);
    }

    public function test_validation_messages_are_greek(): void
    {
        $this->post('/register', [])->assertSessionHasErrors([
            'email' => 'Το πεδίο email είναι υποχρεωτικό.',
        ]);
    }

    public function test_invoice_file_is_private_and_company_scoped(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        [$owner, $invoice] = $this->invoiceFor('Synthetic Α');
        [$stranger] = $this->invoiceFor('Synthetic Β', withInvoice: false);

        $this->get(route('invoices.file', $invoice))->assertRedirect('/login');

        $this->actingAs($owner)->get(route('invoices.file', $invoice))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');

        auth()->logout();
        app()->forgetInstance('current_company');

        $this->actingAs($stranger)->get(route('invoices.file', $invoice))->assertNotFound();
    }

    public function test_api_file_endpoint_requires_token_and_company(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        [$owner, $invoice] = $this->invoiceFor('Synthetic API');
        [$stranger] = $this->invoiceFor('Synthetic API other', withInvoice: false);

        $this->getJson('/api/invoices/'.$invoice->id.'/file')->assertUnauthorized();

        \Laravel\Sanctum\Sanctum::actingAs($owner);
        $this->get('/api/invoices/'.$invoice->id.'/file')->assertOk();
        $this->getJson('/api/invoices/'.$invoice->id)->assertOk()
            ->assertJsonPath('data.file_url', url('/api/invoices/'.$invoice->id.'/file'));

        app()->forgetInstance('current_company');
        \Laravel\Sanctum\Sanctum::actingAs($stranger);
        $this->get('/api/invoices/'.$invoice->id.'/file')->assertNotFound();
    }

    public function test_deleting_user_removes_their_invoice_files(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        [$owner, $invoice] = $this->invoiceFor('Synthetic Γ');
        Storage::disk('public')->put('invoices/legacy-synthetic.pdf', '%PDF-legacy');
        Invoice::withoutGlobalScopes()->create([
            'user_id' => $owner->id,
            'company_id' => $owner->company_id,
            'issuer_name' => 'Synthetic legacy issuer',
            'file_path' => 'invoices/legacy-synthetic.pdf',
        ]);

        $owner->createToken('synthetic');
        $owner->delete();

        $this->assertSame(0, \Laravel\Sanctum\PersonalAccessToken::count());

        Storage::disk('local')->assertMissing($invoice->file_path);
        Storage::disk('public')->assertMissing('invoices/legacy-synthetic.pdf');
    }

    public function test_privatize_command_moves_legacy_public_files(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        [$owner] = $this->invoiceFor('Synthetic Δ', withInvoice: false);
        Storage::disk('public')->put('invoices/old-synthetic.pdf', '%PDF-old');
        Invoice::withoutGlobalScopes()->create([
            'user_id' => $owner->id,
            'company_id' => $owner->company_id,
            'issuer_name' => 'Synthetic old issuer',
            'file_path' => 'invoices/old-synthetic.pdf',
        ]);

        $this->artisan('invoices:privatize-files')->assertSuccessful();
        Storage::disk('public')->assertExists('invoices/old-synthetic.pdf');

        $this->artisan('invoices:privatize-files', ['--apply' => true])->assertSuccessful();
        Storage::disk('public')->assertMissing('invoices/old-synthetic.pdf');
        $this->assertSame('%PDF-old', Storage::disk('local')->get('invoices/old-synthetic.pdf'));
    }

    public function test_greek_money_and_date_helpers(): void
    {
        $this->assertSame('1.234,50 €', money_el(1234.5));
        $this->assertSame('29,00 €', money_el(29));
        $this->assertSame('10,00 USD', money_el(10, 'usd'));
        $this->assertSame('1.000', number_el(1000));
        $this->assertSame('14/11/2026', date_el('2026-11-14 10:00:00'));
        $this->assertSame('—', date_el(null));
    }

    private function plan(): Plan
    {
        return Plan::create([
            'name' => 'Starter', 'slug' => 'starter', 'price' => 29, 'invoice_limit' => 50,
            'is_active' => true, 'sort_order' => 1,
        ]);
    }

    private function registration(Plan $plan): array
    {
        return [
            'name' => 'Synthetic User', 'email' => 'synthetic-user@example.test', 'company_name' => 'Synthetic Co',
            'password' => 'Synthetic-Pass-2026!', 'password_confirmation' => 'Synthetic-Pass-2026!', 'plan_id' => $plan->id,
        ];
    }

    /** @return array{0: User, 1?: Invoice} */
    private function invoiceFor(string $companyName, bool $withInvoice = true): array
    {
        $company = Company::create(['name' => $companyName, 'email' => 'c'.md5($companyName).'@example.test', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'owner']);

        if (! $withInvoice) {
            return [$user];
        }

        $path = 'invoices/'.md5($companyName).'.pdf';
        Storage::disk(Invoice::FILE_DISK)->put($path, '%PDF-synthetic');

        $invoice = Invoice::withoutGlobalScopes()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'issuer_name' => 'Synthetic issuer',
            'file_path' => $path,
            'original_filename' => 'synthetic.pdf',
        ]);

        return [$user, $invoice];
    }
}
