<?php

namespace Tests\Feature;

use App\Exceptions\InvoiceExtractionException;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\User;
use App\Services\ClaudeInvoiceExtractor;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tenant isolation (CompanyScope fail-closed), user-safe error messages and
 * EUR Stripe prices. Synthetic data only; Anthropic and Stripe are faked.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_company_sees_no_invoices_on_web(): void
    {
        Storage::fake('local');
        $invoice = $this->otherCompanyInvoice();
        $orphan = User::factory()->create(['company_id' => null, 'role' => 'member']);

        $this->actingAs($orphan);
        $this->get('/invoices')->assertOk()->assertDontSee('SYNTH-SECRET-ISSUER');
        $this->get('/invoices/'.$invoice->id)->assertNotFound();
        $this->get('/invoices/'.$invoice->id.'/file')->assertNotFound();
        $this->delete('/invoices/'.$invoice->id)->assertNotFound();
        $this->assertNotNull(Invoice::withoutGlobalScopes()->find($invoice->id));
    }

    public function test_user_without_company_sees_no_invoices_on_api(): void
    {
        Storage::fake('local');
        $invoice = $this->otherCompanyInvoice();
        Sanctum::actingAs(User::factory()->create(['company_id' => null, 'role' => 'member']));

        $this->getJson('/api/invoices')->assertOk()->assertJsonPath('data.total', 0);
        $this->getJson('/api/invoices/'.$invoice->id)->assertNotFound();
        $this->get('/api/invoices/'.$invoice->id.'/file')->assertNotFound();
        $this->deleteJson('/api/invoices/'.$invoice->id)->assertNotFound();
    }

    public function test_user_without_company_cannot_upload(): void
    {
        Storage::fake('local');
        $this->app->instance(ClaudeInvoiceExtractor::class, $this->extractorThrowing(new \RuntimeException('must not be called')));
        $orphan = User::factory()->create(['company_id' => null, 'role' => 'member']);

        $this->actingAs($orphan)
            ->post('/invoices', ['invoice_files' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')]])
            ->assertRedirect(route('dashboard'));

        Sanctum::actingAs($orphan);
        $this->postJson('/api/invoices', ['file' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])
            ->assertForbidden()->assertJsonPath('code', 'no_company');

        $this->assertSame(0, Invoice::withoutGlobalScopes()->count());
    }

    public function test_company_user_is_limited_to_own_company(): void
    {
        Storage::fake('local');
        $invoice = $this->otherCompanyInvoice();
        $company = Company::create(['name' => 'Synthetic Own', 'email' => 'own@example.test', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'owner']);

        $this->actingAs($user)->get('/invoices/'.$invoice->id)->assertNotFound();
    }

    public function test_super_admin_without_company_keeps_cross_company_access(): void
    {
        Storage::fake('local');
        $invoice = $this->otherCompanyInvoice();
        $admin = User::factory()->create(['company_id' => null, 'is_super_admin' => true]);

        $this->actingAs($admin)->get('/invoices/'.$invoice->id)->assertOk()->assertSee('SYNTH-SECRET-ISSUER');
        $this->get('/invoices/'.$invoice->id.'/file')->assertOk();
    }

    public function test_unexpected_errors_are_not_shown_or_logged_with_details(): void
    {
        Storage::fake('local');
        Log::spy();
        $this->app->instance(ClaudeInvoiceExtractor::class,
            $this->extractorThrowing(new \RuntimeException('SQLSTATE insert values (SYNTH-SECRET-VALUE, 123456789)')));

        $company = Company::create(['name' => 'Synthetic Err', 'email' => 'err@example.test', 'is_active' => true]);
        $admin = User::factory()->create(['company_id' => $company->id, 'is_super_admin' => true]);

        $web = $this->actingAs($admin)->post('/invoices', ['invoice_files' => [UploadedFile::fake()->create('err.pdf', 10, 'application/pdf')]]);
        $web->assertSessionHasErrors('invoice_files');
        $this->assertStringNotContainsString('SYNTH-SECRET-VALUE', json_encode(session('errors')->getMessages(), JSON_UNESCAPED_UNICODE));

        Sanctum::actingAs($admin);
        $api = $this->postJson('/api/invoices', ['file' => UploadedFile::fake()->create('err.pdf', 10, 'application/pdf')]);
        $api->assertStatus(422);
        $this->assertStringNotContainsString('SYNTH-SECRET-VALUE', $api->getContent());

        Log::shouldHaveReceived('error')->withArgs(
            fn ($message, $context = []) => ! str_contains(json_encode($context), 'SYNTH-SECRET-VALUE')
        );
        Log::shouldNotHaveReceived('error', [\Mockery::any(), \Mockery::on(fn ($c) => str_contains(json_encode($c), 'SYNTH-SECRET-VALUE'))]);
    }

    public function test_safe_extraction_messages_are_still_shown(): void
    {
        Storage::fake('local');
        $this->app->instance(ClaudeInvoiceExtractor::class,
            $this->extractorThrowing(new InvoiceExtractionException('Δεν αναγνωρίστηκε εκδότης στο έγγραφο.')));
        $company = Company::create(['name' => 'Synthetic Safe', 'email' => 'safe@example.test', 'is_active' => true]);
        $admin = User::factory()->create(['company_id' => $company->id, 'is_super_admin' => true]);

        Sanctum::actingAs($admin);
        $response = $this->postJson('/api/invoices', ['file' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertJsonFragment(['success' => false]);
        $this->assertStringContainsString('Δεν αναγνωρίστηκε εκδότης', $response->json('message'));
    }

    public function test_stripe_prices_are_created_in_eur_with_exact_cents(): void
    {
        $fake = new class('sk_test_synthetic_not_a_real_key') extends \Stripe\StripeClient
        {
            public array $calls = [];

            public function __get($name)
            {
                $client = $this;

                return new class($client, $name)
                {
                    public function __construct(private $client, private string $service) {}

                    public function create(array $params)
                    {
                        $this->client->calls[] = [$this->service, $params];

                        return (object) ['id' => ($this->service === 'prices' ? 'price_synthetic' : 'prod_synthetic')];
                    }
                };
            }
        };

        $service = app(StripeService::class);
        (new \ReflectionProperty(StripeService::class, 'client'))->setValue($service, $fake);

        $plan = new Plan(['name' => 'Professional', 'slug' => 'professional', 'price' => 79.99, 'invoice_limit' => 200]);
        $this->assertSame('price_synthetic', $service->createProductAndPrice($plan));

        [$service1, $productParams] = $fake->calls[0];
        [$service2, $priceParams] = $fake->calls[1];
        $this->assertSame(['products', 'prices'], [$service1, $service2]);
        $this->assertSame('eur', $priceParams['currency']);
        $this->assertSame(7999, $priceParams['unit_amount']);
        $this->assertArrayNotHasKey('tax_behavior', $priceParams);
    }

    public function test_stripe_locale_follows_page_language(): void
    {
        app()->setLocale('en');
        $this->assertSame('en', StripeService::stripeLocale());
        app()->setLocale('el');
        $this->assertSame('el', StripeService::stripeLocale());
    }

    public function test_unsupported_currency_is_rejected_before_any_stripe_call(): void
    {
        config(['subscription.currency' => 'usd']);
        $this->expectException(\InvalidArgumentException::class);
        StripeService::currency();
    }

    private function otherCompanyInvoice(): Invoice
    {
        $other = Company::create(['name' => 'Synthetic Other', 'email' => 'other@example.test', 'is_active' => true]);
        $owner = User::factory()->create(['company_id' => $other->id, 'role' => 'owner']);
        Storage::disk('local')->put('invoices/other.pdf', '%PDF-synthetic');

        return Invoice::withoutGlobalScopes()->create([
            'user_id' => $owner->id, 'company_id' => $other->id,
            'issuer_name' => 'SYNTH-SECRET-ISSUER', 'file_path' => 'invoices/other.pdf', 'original_filename' => 'other.pdf',
        ]);
    }

    private function extractorThrowing(\Throwable $e): ClaudeInvoiceExtractor
    {
        return new class($e) extends ClaudeInvoiceExtractor
        {
            public function __construct(private \Throwable $toThrow) {}

            public function extract(string $filePath, string $disk = 'local'): array
            {
                throw $this->toThrow;
            }
        };
    }
}
