# AGENTS.md — InvoiceAI (invoiceai.gr)

Codebase facts for agents working on this repo (verified 2026-11-14).

## Production / deploy
- Forge server `perfexapi` (Hetzner, Falkenstein DE, shared with other sites), site `invoiceai.gr`.
- Repo `sattip/krl-invoices`, branch `main`. **Forge Quick Deploy is ON: any push to `main` deploys to production.**
  Work on feature branches only; never push `main` without the owner's approval.
- Zero-downtime releases (`/home/forge/invoiceai.gr/current` → release dir), shared path `storage/`.
- Deploy script: `composer install --no-dev` → `npm ci && npm run build` → `artisan optimize` → `storage:link` → `migrate --force` → activate → restart queues.
  So every new migration runs automatically on deploy.
- PHP 8.3 on server, MySQL 8. Local dev may use PHP 8.5 (only deprecation notices).

- **Actual production baseline is `9559c32` (server-only commit, WhatsApp/WaSenderAPI) + server edits, not GitHub `main`/9632f01.**
  See docs/INVOICEAI_RELEASE.md §0c. Rollback = symlink to the previous release dir, never a redeploy of 9632f01.
- WhatsApp: per-company `whatsapp_settings` (API key/webhook secret encrypted), `App\Services\WhatsAppService` → api.wasenderapi.com,
  `App\Notifications\Channels\WhatsAppChannel`. UI/messages are Greek (tests/Feature/WhatsAppLiveFeatureTest). Never send raw exception text or invoice content beyond the documented summary.
- `packages/obstechnologies/invoiceai` exists only untracked on the server and is NOT installed; don't add it to composer.json without review.

## Stack
- Laravel 12, Breeze (auth controllers), AdminLTE (`jeroennoten/laravel-adminlte`) for the logged-in area, Tailwind for auth pages,
  Bootstrap/AdminLTE CSS for the landing page (`public/css/landing.css`).
- AI extraction: `app/Services/ClaudeInvoiceExtractor.php` (Anthropic PHP SDK, model from `CLAUDE_MODEL`).
- Billing: Stripe Checkout + Billing Portal (`app/Services/StripeService.php`); Stripe credentials are stored in DB (`stripe_settings`), not `.env`.
- Multi-tenant: `BelongsToCompany` trait + `CompanyScope` global scope on invoices. The scope **fails closed**: no company context →
  no rows, except super admins (intended cross-company access) and CLI/queue code. Users without a company cannot upload (web redirect, API 403 `no_company`).
- User-facing errors: only `App\Exceptions\InvoiceExtractionException` messages are shown; other exceptions get a generic Greek message and
  are logged by class/location only (DB errors can contain invoice values). Billing errors: generic message + `report($e)`.
- Stripe: new prices are created in `config('subscription.currency')` (only `eur` allowed) with exact cents; no tax settings.
  Checkout/Portal `locale` follows the page language.

## Conventions
- Languages: Greek default at `/`, English at `/en` (public pages: home, terms, privacy, cookies, contact). English route = Greek route name + `.en`;
  use `lroute()` for localized links and `locale_alternates()` / `partials/seo-head` for canonical + reciprocal hreflang.
  `App\Http\Middleware\SetLocale`: route default `locale` wins and is remembered in session (guest auth pages follow it);
  the logged-in app (AdminLTE) and emails are Greek-only and always render `el`.
  Translations: `lang/{el,en}/landing.php`, `lang/en.json` (Greek source strings → English), `lang/el.json` (Breeze English keys → Greek), `lang/el/*.php`, `lang/vendor/adminlte/el`.
- Prices: VAT wording is controlled by `config('legal.pricing.vat_mode')` (env `INVOICEAI_PRICE_VAT_MODE` = included|excluded); unset = no VAT statement.
  Checkout passes no tax settings to Stripe.
- Formatting helpers (`app/helpers.php`): `money_el()`, `number_el()`, `date_el()` (Europe/Athens display, UTC storage).
- Legal/corporate facts live in `config/legal.php` only. Legal pages: `resources/views/legal/*` (routes `legal.terms|privacy|cookies|contact`).
  Footer with legal links: `resources/views/partials/legal-links.blade.php` (used by landing, auth, Breeze layouts, AdminLTE footer).
- Invoice files: new uploads go to the private `local` disk (`storage/app/private/invoices`), served only via `invoices.file` (web, auth+company scope)
  and `/api/invoices/{id}/file` (Sanctum). Legacy files on the `public` disk are still readable until `php artisan invoices:privatize-files --apply` is run.
- Do not log invoice content or model responses (personal data).
- Only strictly necessary cookies exist (session, XSRF-TOKEN, remember_web_*). Adding analytics/marketing requires an opt-in consent mechanism first.
- Landing page must not contain unverified claims (accuracy %, customer counts, testimonials, guarantees, native ERP connectors, "Most Popular").
  The free trial below is a real, server-enforced feature (owner-approved 2026-11-14); describe it only with its exact terms.
  Plan feature lists are fixed in the views; the DB `plans.features` column holds legacy English marketing items and is not displayed.
- Screenshots/QA: `public` served with `php -S`, Playwright (`playwright-core` from the OpenClaw install + system Chrome); AdminLTE pages need a tall viewport, not fullPage.

## Free trial (owner-approved 2026-11-14)
- Offer: **14 days from account creation, max 10 invoices in total, no card, no Stripe objects, no auto renewal/charge, new self-service registrations only**.
  Existing companies/subscriptions are unchanged. Values live in `config('subscription.trial')`; `INVOICEAI_TRIAL_ENABLED=false` hides it for new sign-ups
  (already granted trials keep their own end date/limit).
- `/register` defaults to the trial card (`plan_id=trial`); only an explicit `?plan=` / old input preselects a paid plan, UTM params change nothing.
  Trial sign-up = one DB transaction: company + owner (terms recorded) + `trial_entitlements` row (`TrialEntitlement::startFor`, values from config only),
  then login → dashboard. Paid plans keep the Stripe Checkout flow unchanged (paid `plan_id` must be an active plan). POST /register is throttled 10/10 min/IP.
- Enforcement: `Company::governingTrial()` (only when there is **no active paid subscription** — paid always wins). Every extraction path must
  `reserveSlot()` (one conditional UPDATE: `ends_at > now` and `invoices_used < invoice_limit`) before storing/extracting and `releaseSlot()` on failure.
  Today the only paths are web `InvoiceController::store` and API `Api\InvoiceController::store` (WhatsApp is outbound notifications only);
  `FreeTrialTest::test_every_extraction_entry_point_is_quota_gated` fails if a new `->extract(` caller appears. Trial usage never touches `invoice_usage`
  (no monthly reset); deleting an invoice does not return a slot. Expired/full → web redirect to billing.plans, API 403 `trial_expired` / `trial_limit_reached`.
  Existing invoices stay readable after expiry.
- Email verification is not enforced (User does not implement MustVerifyEmail; unchanged), so trials work with `MAIL_MAILER=log`. No mail is sent at trial sign-up.
- UI: trial card on register (el/en), `partials/trial-status` on dashboard, billing, plans and upload page (Greek app), Terms §5 el/en (terms_version `2026-11-14b`),
  landing pricing note el/en. Tests: `tests/Feature/FreeTrialTest.php`.

## Tests
- `vendor/bin/phpunit` (PHP 8.3). Known pre-existing failures (identical on the live baseline 9559c32, 2026-11-14): PasswordResetTest ×3 (custom notification class), RegistrationTest (posts no plan/terms), ExampleTest (no DB).
- `tests/Feature/GreekLegalTest.php` covers legal pages, Greek UI, terms acceptance, private file access, deletion and the privatize command.
