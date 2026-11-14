<?php

namespace Tests\Feature;

use App\Exceptions\InvoiceExtractionException;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceUsage;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TrialEntitlement;
use App\Models\User;
use App\Services\ClaudeInvoiceExtractor;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Local free trial: 14 days from registration, max 10 invoices in total, no card, no Stripe,
 * no auto renewal, new self-service registrations only. Synthetic data only: Stripe is a
 * strict mock (any call fails), the extractor is a fake, mail/notifications are faked and
 * HTTP egress is blocked by TestCase.
 */
class FreeTrialTest extends TestCase
{
    use RefreshDatabase;

    private const UTM = 'utm_source=obstechnologies.gr&utm_medium=referral&utm_campaign=invoice-ai-page';

    private int $extractions = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Notification::fake();
        Storage::fake('local');
        Storage::fake('public');

        // Strict mock: any Stripe call (customer, checkout, subscription) makes the test fail.
        $this->mock(StripeService::class);

        $this->extractions = 0;
        $this->fakeExtractor();
    }

    // ---------------------------------------------------------------- registration page

    public function test_register_page_defaults_to_free_trial_with_clear_terms_in_greek(): void
    {
        $this->plans();

        $html = $this->get('/register')->assertOk()->assertSee('<html lang="el">', false)->getContent();

        $this->assertMatchesRegularExpression('/value="trial"[^>]*\bchecked\b/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="\d+"[^>]*\bchecked\b/', $html);
        foreach (['Δωρεάν δοκιμή 14 ημερών', 'Έως 10 τιμολόγια συνολικά', 'Χωρίς κάρτα · χωρίς αυτόματη ανανέωση ή χρέωση', 'Έναρξη δωρεάν δοκιμής', 'ή επιλέξτε πακέτο συνδρομής'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        // Paid plans stay available.
        foreach (['Starter', 'Professional', 'Enterprise'] as $name) {
            $this->assertStringContainsString($name, $html);
        }
    }

    public function test_utm_link_keeps_trial_default_and_sets_no_tracking_cookie(): void
    {
        $this->plans();

        $response = $this->get('/register?'.self::UTM)->assertOk();
        $html = $response->getContent();

        $this->assertMatchesRegularExpression('/value="trial"[^>]*\bchecked\b/', $html);
        $this->assertStringNotContainsString('utm_', $html);
        foreach ($response->headers->getCookies() as $cookie) {
            $this->assertContains($cookie->getName(), [config('session.cookie'), 'XSRF-TOKEN'], 'unexpected cookie '.$cookie->getName());
        }
    }

    public function test_explicit_plan_query_preselects_that_paid_plan(): void
    {
        $plans = $this->plans();

        $html = $this->get('/register?plan=enterprise&'.self::UTM)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/value="'.$plans['enterprise']->id.'"[^>]*\bchecked\b/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="trial"[^>]*\bchecked\b/', $html);

        // Array/garbage plan query falls back to the trial, no error.
        $this->get('/register?plan[]=1')->assertOk();
    }

    public function test_register_page_in_english(): void
    {
        $this->plans();
        $this->get('/en');

        $this->get('/register')->assertOk()->assertSee('<html lang="en">', false)
            ->assertSee('Free 14-day trial')->assertSee('Up to 10 invoices in total')
            ->assertSee('No card · no automatic renewal or charge')->assertSee('Start free trial');
    }

    public function test_trial_hidden_and_rejected_when_disabled(): void
    {
        $this->plans();
        config(['subscription.trial.enabled' => false]);

        $this->get('/register')->assertOk()->assertDontSee('value="trial"', false)->assertDontSee('Δωρεάν δοκιμή');

        $this->post('/register', $this->form())->assertSessionHasErrors('plan_id');
        $this->assertGuest();
        $this->assertSame(0, Company::count());
    }

    // ---------------------------------------------------------------- trial registration

    public function test_trial_registration_creates_company_user_and_entitlement_without_stripe_or_mail(): void
    {
        Carbon::setTestNow('2026-11-14 21:30:00');

        $response = $this->post('/register?'.self::UTM, $this->form());

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'trial-owner@example.test')->firstOrFail();
        $company = $user->company;
        $this->assertSame('owner', $user->role);
        $this->assertSame('Synthetic Trial Co', $company->name);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertSame(config('legal.terms_version'), $user->terms_version);
        $this->assertTrue(password_verify('Synthetic-Pass-2026!', $user->password));

        $trial = $company->trialEntitlement;
        $this->assertSame('2026-11-14 21:30:00', $trial->starts_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-28 21:30:00', $trial->ends_at->format('Y-m-d H:i:s'));
        $this->assertSame(10, $trial->invoice_limit);
        $this->assertSame(0, $trial->invoices_used);

        // No paid objects at all.
        $this->assertSame(0, Subscription::count());
        $this->assertNull(session('registration'));
        Mail::assertNothingSent();
        Mail::assertNothingQueued();
        Notification::assertNothingSent();

        // Usable immediately, without email verification/SMTP.
        app()->forgetInstance('current_company');
        $this->get('/dashboard')->assertOk()->assertSee('data-trial-status="active"', false)
            ->assertSee('0 / 10')->assertSee('28/11/2026');
        $this->get(route('invoices.create'))->assertOk()->assertSee('Δωρεάν δοκιμή');
        $this->get(route('billing.plans'))->assertOk()->assertSee('δεν μετατρέπεται αυτόματα σε συνδρομή');
    }

    public function test_tampered_trial_parameters_are_ignored_or_rejected(): void
    {
        $plans = $this->plans();

        $this->post('/register', $this->form() + [
            'trial_days' => 999, 'days' => 999, 'invoice_limit' => 1000, 'invoices_used' => -50,
            'ends_at' => '2099-01-01', 'starts_at' => '2099-01-01', 'trial_ends_at' => '2099-01-01',
            'stripe_customer_id' => 'cus_synthetic', 'status' => 'active', 'company_id' => 999,
        ])->assertRedirect(route('dashboard'));

        $trial = TrialEntitlement::sole();
        $this->assertSame(10, $trial->invoice_limit);
        $this->assertSame(0, $trial->invoices_used);
        $this->assertEqualsWithDelta(14 * 86400, $trial->starts_at->diffInSeconds($trial->ends_at), 1);
        $this->assertSame(0, Subscription::count());
        $this->resetAuth();

        // (" trial" is trimmed by TrimStrings to the same trial choice, so it is not a bypass.)
        foreach (['TRIAL', 'trial-x', 'trial1', ['trial'], '', 'free', (string) ($plans['enterprise']->id + 100)] as $i => $bad) {
            $this->post('/register', ['plan_id' => $bad, 'email' => "bad{$i}@example.test"] + $this->form())
                ->assertSessionHasErrors('plan_id');
        }
        $this->assertSame(1, Company::count());
        $this->assertSame(1, TrialEntitlement::count());
    }

    public function test_trial_registration_still_requires_terms_and_validation(): void
    {
        $form = $this->form();
        unset($form['terms']);

        $this->post('/register', $form)->assertSessionHasErrors('terms');
        $this->post('/register', ['password_confirmation' => 'x'] + $this->form())->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertSame(0, Company::count() + User::count() + TrialEntitlement::count());
    }

    public function test_existing_email_cannot_get_a_second_trial(): void
    {
        $this->post('/register', $this->form())->assertRedirect(route('dashboard'));
        $this->resetAuth();

        $this->post('/register', ['company_name' => 'Other Co'] + $this->form())->assertSessionHasErrors('email');
        $this->assertSame(1, TrialEntitlement::count());
    }

    public function test_registration_is_throttled(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/register', []);
        }

        $this->post('/register', [])->assertStatus(429);
    }

    // ---------------------------------------------------------------- paid flow unchanged

    public function test_paid_plan_registration_flow_is_unchanged(): void
    {
        $plans = $this->plans();

        $this->post('/register?'.self::UTM, ['plan_id' => $plans['professional']->id] + $this->form())
            ->assertRedirect(route('register.checkout'));

        $this->assertGuest();
        $this->assertSame(0, Company::count() + User::count() + TrialEntitlement::count());
        $this->assertSame((string) $plans['professional']->id, (string) session('registration.plan_id'));
        $this->assertArrayNotHasKey('password', session('registration'));

        // Inactive plans cannot be chosen.
        $plans['starter']->update(['is_active' => false]);
        $this->post('/register', ['plan_id' => $plans['starter']->id, 'email' => 'x@example.test'] + $this->form())
            ->assertSessionHasErrors('plan_id');
    }

    public function test_paid_company_without_trial_keeps_monthly_limit_behaviour(): void
    {
        [$user, $company] = $this->paidCompany(limit: 2);

        $this->upload($user)->assertRedirect();
        $this->upload($user)->assertRedirect();
        $this->assertSame(2, InvoiceUsage::where('company_id', $company->id)->sole()->count);

        $this->upload($user)->assertSessionHas('error');
        $this->api($user)->assertStatus(403)->assertJsonPath('code', 'invoice_limit_reached');
        $this->assertSame(2, Invoice::withoutGlobalScopes()->count());
        $this->assertSame(0, TrialEntitlement::count());
    }

    public function test_paid_subscription_takes_precedence_over_a_trial(): void
    {
        [$user, $company] = $this->trialCompany(used: 10);
        $plan = Plan::create(['name' => 'Starter', 'slug' => 'starter', 'price' => 29, 'invoice_limit' => 50, 'is_active' => true]);
        Subscription::create([
            'company_id' => $company->id, 'plan_id' => $plan->id, 'stripe_subscription_id' => 'sub_synthetic',
            'stripe_customer_id' => 'cus_synthetic', 'status' => 'active',
            'current_period_start' => now()->subDay(), 'current_period_end' => now()->addMonth(),
        ]);

        $this->upload($user)->assertRedirect();
        $this->api($user)->assertCreated();

        $this->assertSame(2, InvoiceUsage::where('company_id', $company->id)->sole()->count);
        $this->assertSame(10, $company->trialEntitlement->fresh()->invoices_used);
    }

    public function test_company_without_trial_or_subscription_is_still_blocked(): void
    {
        $company = Company::create(['name' => 'No Plan Co', 'email' => 'np@example.test', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'owner']);

        $this->upload($user)->assertRedirect(route('billing.plans'));
        $this->api($user)->assertStatus(403)->assertJsonPath('code', 'no_subscription');
        $this->assertSame(0, $this->extractions);
    }

    // ---------------------------------------------------------------- quota: 10th / 11th

    public function test_tenth_invoice_is_accepted_and_eleventh_rejected_across_web_and_api(): void
    {
        [$user, $company] = $this->trialCompany();

        for ($i = 1; $i <= 5; $i++) {
            $this->upload($user)->assertRedirect();
        }
        for ($i = 6; $i <= 10; $i++) {
            $this->api($user)->assertCreated();
        }

        $this->assertSame(10, $company->trialEntitlement->fresh()->invoices_used);
        $this->assertSame(10, Invoice::withoutGlobalScopes()->where('company_id', $company->id)->count());

        $this->api($user)->assertStatus(403)->assertJsonPath('code', 'trial_limit_reached')
            ->assertJsonPath('usage.used', 10)->assertJsonPath('usage.remaining', 0);
        $this->upload($user)->assertRedirect(route('billing.plans'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'και τα 10 τιμολόγια'));

        $this->assertSame(10, $this->extractions);
        $this->assertSame(10, Invoice::withoutGlobalScopes()->count());
        // Trial usage never feeds the monthly counter.
        $this->assertSame(0, InvoiceUsage::count());

        app()->forgetInstance('current_company');
        $this->actingAs($user->fresh())->get('/dashboard')->assertSee('data-trial-status="limit"', false);
    }

    public function test_batch_larger_than_remaining_is_rejected_before_any_extraction(): void
    {
        [$user, $company] = $this->trialCompany(used: 8);

        $this->upload($user, 3)->assertRedirect(route('billing.plans'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'απομένουν 2 από 10'));
        $this->assertSame(0, $this->extractions);
        $this->assertSame(8, $company->trialEntitlement->fresh()->invoices_used);

        $this->upload($user, 2)->assertRedirect();
        $this->assertSame(10, $company->trialEntitlement->fresh()->invoices_used);
    }

    public function test_limit_is_total_across_months_not_monthly(): void
    {
        Carbon::setTestNow('2026-11-25 10:00:00');
        [$user, $company] = $this->trialCompany();

        Carbon::setTestNow('2026-11-30 23:00:00');
        for ($i = 0; $i < 6; $i++) {
            $this->api($user)->assertCreated();
        }

        Carbon::setTestNow('2026-12-01 09:00:00'); // new calendar month, still inside the 14 days
        for ($i = 0; $i < 4; $i++) {
            $this->upload($user)->assertRedirect();
        }
        $this->api($user)->assertStatus(403)->assertJsonPath('code', 'trial_limit_reached');

        $this->assertSame(10, $company->trialEntitlement->fresh()->invoices_used);
        $this->assertSame(0, InvoiceUsage::count());
    }

    public function test_deleting_an_invoice_does_not_give_a_slot_back(): void
    {
        [$user, $company] = $this->trialCompany(used: 9);

        $this->api($user)->assertCreated();
        $invoice = Invoice::withoutGlobalScopes()->where('company_id', $company->id)->sole();

        app()->forgetInstance('current_company');
        $this->actingAs($user->fresh())->delete(route('invoices.destroy', $invoice))->assertRedirect();
        $this->assertSame(0, Invoice::withoutGlobalScopes()->count());

        $this->api($user)->assertStatus(403)->assertJsonPath('code', 'trial_limit_reached');
    }

    public function test_failed_extraction_releases_the_slot(): void
    {
        [$user, $company] = $this->trialCompany(used: 9);
        $this->fakeExtractor(fail: true);

        $this->api($user)->assertStatus(422);
        $this->upload($user)->assertSessionHasErrors('invoice_files');
        $this->assertSame(9, $company->trialEntitlement->fresh()->invoices_used);
        Storage::disk('local')->assertDirectoryEmpty('invoices');

        $this->fakeExtractor();
        $this->api($user)->assertCreated();
        $this->assertSame(10, $company->trialEntitlement->fresh()->invoices_used);
    }

    public function test_reservation_is_atomic_against_stale_concurrent_readers(): void
    {
        [, $company] = $this->trialCompany(used: 9);

        // Two requests that both read "9 used" before either writes.
        $a = TrialEntitlement::find($company->trialEntitlement->id);
        $b = TrialEntitlement::find($company->trialEntitlement->id);
        $this->assertNull($a->denial());
        $this->assertNull($b->denial());

        $this->assertTrue($a->reserveSlot());
        $this->assertFalse($b->reserveSlot());
        $this->assertSame(10, TrialEntitlement::find($a->id)->invoices_used);

        // Release never goes below zero.
        $t = TrialEntitlement::find($a->id);
        $t->forceFill(['invoices_used' => 0])->save();
        $t->releaseSlot();
        $this->assertSame(0, $t->fresh()->invoices_used);
    }

    // ---------------------------------------------------------------- expiry

    public function test_trial_ends_exactly_fourteen_days_after_signup(): void
    {
        Carbon::setTestNow('2026-11-14 12:00:00');
        $this->post('/register', $this->form())->assertRedirect(route('dashboard'));
        $user = User::where('email', 'trial-owner@example.test')->sole();
        $this->resetAuth();

        Carbon::setTestNow('2026-11-28 11:59:59');
        $this->upload($user)->assertRedirect();
        $this->api($user)->assertCreated();

        Carbon::setTestNow('2026-11-28 12:00:00');
        $this->api($user)->assertStatus(403)->assertJsonPath('code', 'trial_expired')
            ->assertJsonPath('usage.remaining', 0);
        $this->upload($user)->assertRedirect(route('billing.plans'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'έληξε') && str_contains($m, 'Δεν έγινε καμία χρέωση'));

        Carbon::setTestNow('2027-01-15 12:00:00'); // months later: still closed, no monthly reset
        $this->api($user)->assertStatus(403)->assertJsonPath('code', 'trial_expired');

        $this->assertSame(2, $this->extractions);
        $this->assertSame(2, $user->company->trialEntitlement->fresh()->invoices_used);

        // Data stays readable after expiry; dashboard explains the state.
        $invoice = Invoice::withoutGlobalScopes()->where('company_id', $user->company_id)->first();
        app()->forgetInstance('current_company');
        $this->actingAs($user->fresh())->get('/dashboard')->assertOk()->assertSee('data-trial-status="expired"', false)
            ->assertSee('Δεν έγινε καμία χρέωση');
        $this->get(route('invoices.show', $invoice))->assertOk();
        $this->get(route('invoices.file', $invoice))->assertOk();
    }

    public function test_reservation_rechecks_expiry_in_the_database(): void
    {
        [, $company] = $this->trialCompany();
        $trial = $company->trialEntitlement;

        Carbon::setTestNow($trial->ends_at->copy()->addSecond());
        $this->assertFalse($trial->reserveSlot());
        $this->assertSame(0, $trial->fresh()->invoices_used);
    }

    // ---------------------------------------------------------------- isolation / security

    public function test_trial_usage_and_invoices_stay_per_company(): void
    {
        [$userA, $companyA] = $this->trialCompany(used: 9, name: 'Trial A');
        [$userB, $companyB] = $this->trialCompany(name: 'Trial B');

        $this->api($userA)->assertCreated();
        $this->api($userA)->assertStatus(403);
        $this->api($userB)->assertCreated();

        $this->assertSame(10, $companyA->trialEntitlement->fresh()->invoices_used);
        $this->assertSame(1, $companyB->trialEntitlement->fresh()->invoices_used);

        $invoiceA = Invoice::withoutGlobalScopes()->where('company_id', $companyA->id)->sole();
        app()->forgetInstance('current_company');
        $this->actingAs($userB->fresh())->get(route('invoices.file', $invoiceA))->assertNotFound();
        $this->getJson('/api/invoices/'.$invoiceA->id)->assertNotFound();

        // Stored privately.
        $this->assertSame(Invoice::FILE_DISK, 'local');
        Storage::disk('local')->assertExists($invoiceA->file_path);
        Storage::disk('public')->assertMissing($invoiceA->file_path);
    }

    public function test_every_extraction_entry_point_is_quota_gated(): void
    {
        // Web upload and REST API are the only ingestion paths (WhatsApp is outbound notifications only).
        // A new entry point (e.g. inbound WhatsApp media) must use governingTrial()/reserveSlot() and be added here.
        $callers = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path())) as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.php')
                && preg_match('/->extract\(/', file_get_contents($file->getPathname()))) {
                $callers[] = str_replace(app_path().'/', '', $file->getPathname());
            }
        }
        sort($callers);

        $this->assertSame(['Http/Controllers/Api/InvoiceController.php', 'Http/Controllers/InvoiceController.php'], $callers);
        foreach ($callers as $caller) {
            $code = file_get_contents(app_path($caller));
            $this->assertStringContainsString('governingTrial()', $code);
            $this->assertStringContainsString('reserveSlot()', $code);
            $this->assertStringContainsString('releaseSlot()', $code);
        }
    }

    public function test_trial_entitlement_is_not_mass_assignable(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\MassAssignmentException::class);

        TrialEntitlement::create(['company_id' => 1, 'invoice_limit' => 1000]);
    }

    public function test_existing_companies_get_no_trial_and_legal_pages_state_terms(): void
    {
        [$user] = $this->paidCompany(limit: 5);
        $this->assertNull($user->company->trialEntitlement);

        $this->get('/terms')->assertOk()->assertSee('Δωρεάν δοκιμή')->assertSee('14 ημερών')
            ->assertSee('10')->assertSee('δεν ανανεώνεται');
        $this->get('/en/terms')->assertOk()->assertSee('Free trial')->assertSee('neither renews');
        $this->get('/')->assertOk()->assertSee('data-trial-note', false)->assertSee('χωρίς κάρτα');
        $this->get('/en')->assertOk()->assertSee('Free 14-day trial');
    }

    // ---------------------------------------------------------------- helpers

    private function form(): array
    {
        return [
            'name' => 'Synthetic Owner', 'email' => 'trial-owner@example.test', 'company_name' => 'Synthetic Trial Co',
            'password' => 'Synthetic-Pass-2026!', 'password_confirmation' => 'Synthetic-Pass-2026!',
            'plan_id' => 'trial', 'terms' => '1',
        ];
    }

    /** @return array<string, Plan> */
    private function plans(): array
    {
        $out = [];
        foreach ([['starter', 29, 50], ['professional', 79, 200], ['enterprise', 199, 1000]] as $i => [$slug, $price, $limit]) {
            $out[$slug] = Plan::create(['name' => ucfirst($slug), 'slug' => $slug, 'price' => $price, 'invoice_limit' => $limit,
                'is_active' => true, 'sort_order' => $i, 'stripe_price_id' => 'price_synthetic_'.$slug]);
        }

        return $out;
    }

    /** @return array{0: User, 1: Company} */
    private function trialCompany(int $used = 0, string $name = 'Synthetic Trial'): array
    {
        $company = Company::create(['name' => $name, 'email' => md5($name).'@example.test', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'owner']);
        TrialEntitlement::startFor($company)->forceFill(['invoices_used' => $used])->save();

        return [$user, $company];
    }

    /** @return array{0: User, 1: Company} */
    private function paidCompany(int $limit): array
    {
        $company = Company::create(['name' => 'Paid Co', 'email' => 'paid@example.test', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'owner']);
        $plan = Plan::create(['name' => 'Tiny', 'slug' => 'tiny', 'price' => 1, 'invoice_limit' => $limit, 'is_active' => true]);
        Subscription::create([
            'company_id' => $company->id, 'plan_id' => $plan->id, 'stripe_subscription_id' => 'sub_paid_synthetic',
            'stripe_customer_id' => 'cus_paid_synthetic', 'status' => 'active',
            'current_period_start' => now()->subDay(), 'current_period_end' => now()->addMonth(),
        ]);

        return [$user, $company];
    }

    private function resetAuth(): void
    {
        $auth = $this->app['auth'];
        $auth->guard('web')->logout();
        $auth->forgetGuards();
        $auth->shouldUse('web');
    }

    /** Web upload as a fresh request (no cached company/relations from earlier requests). */
    private function upload(User $user, int $files = 1)
    {
        $this->resetAuth();
        app()->forgetInstance('current_company');

        $uploads = [];
        for ($i = 0; $i < $files; $i++) {
            $uploads[] = UploadedFile::fake()->create("synthetic-{$i}.pdf", 10, 'application/pdf');
        }

        return $this->actingAs($user->fresh())->from(route('invoices.create'))
            ->post(route('invoices.store'), ['invoice_files' => $uploads]);
    }

    private function api(User $user)
    {
        $this->resetAuth();
        app()->forgetInstance('current_company');

        return $this->actingAs($user->fresh(), 'sanctum')->postJson('/api/invoices', [
            'file' => UploadedFile::fake()->create('synthetic.pdf', 10, 'application/pdf'),
        ]);
    }

    private function fakeExtractor(bool $fail = false): void
    {
        $counter = function () {
            $this->extractions++;
        };

        $this->app->instance(ClaudeInvoiceExtractor::class, new class($fail, $counter) extends ClaudeInvoiceExtractor
        {
            public function __construct(private bool $fail, private \Closure $counter) {}

            public function extract(string $filePath, string $disk = 'local'): array
            {
                ($this->counter)();

                if ($this->fail) {
                    throw new InvoiceExtractionException('Δεν αναγνωρίστηκε εκδότης στο έγγραφο.');
                }

                return [
                    'invoice_number' => 'SYN-'.random_int(1000, 9999), 'invoice_date' => '2026-11-14',
                    'issuer' => ['name' => 'Synthetic Issuer', 'vat_number' => '000000000'],
                    'customer' => ['name' => 'Synthetic Customer'], 'currency' => 'EUR',
                    'totals' => ['subtotal' => 10, 'vat_total' => 2.4, 'grand_total' => 12.4],
                    'line_items' => [], 'discounts' => [], 'other_charges' => [],
                ];
            }
        });
    }
}
