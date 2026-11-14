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

## Stack
- Laravel 12, Breeze (auth controllers), AdminLTE (`jeroennoten/laravel-adminlte`) for the logged-in area, Tailwind for auth pages,
  Bootstrap/AdminLTE CSS for the landing page (`public/css/landing.css`).
- AI extraction: `app/Services/ClaudeInvoiceExtractor.php` (Anthropic PHP SDK, model from `CLAUDE_MODEL`).
- Billing: Stripe Checkout + Billing Portal (`app/Services/StripeService.php`); Stripe credentials are stored in DB (`stripe_settings`), not `.env`.
- Multi-tenant: `BelongsToCompany` trait + `CompanyScope` global scope on invoices.

## Conventions
- Languages: Greek default at `/`, English at `/en` (public pages: home, terms, privacy, cookies, contact). English route = Greek route name + `.en`;
  use `lroute()` for localized links and `locale_alternates()` / `partials/seo-head` for canonical + reciprocal hreflang.
  `App\Http\Middleware\SetLocale`: route default `locale` wins and is remembered in session (guest auth pages follow it);
  the logged-in app (AdminLTE) and emails are Greek-only and always render `el`.
  Translations: `lang/{el,en}/landing.php`, `lang/en.json` (Greek source strings → English), `lang/el.json` (Breeze English keys → Greek), `lang/el/*.php`, `lang/vendor/adminlte/el`.
- Prices: VAT wording is controlled by `config('legal.pricing.vat_mode')` (env `INVOICEAI_PRICE_VAT_MODE` = included|excluded); unset = no VAT statement.
  Checkout passes no tax settings to Stripe; `StripeService::createProductAndPrice` still creates prices in **USD** (inconsistent with € display).
- Formatting helpers (`app/helpers.php`): `money_el()`, `number_el()`, `date_el()` (Europe/Athens display, UTC storage).
- Legal/corporate facts live in `config/legal.php` only. Legal pages: `resources/views/legal/*` (routes `legal.terms|privacy|cookies|contact`).
  Footer with legal links: `resources/views/partials/legal-links.blade.php` (used by landing, auth, Breeze layouts, AdminLTE footer).
- Invoice files: new uploads go to the private `local` disk (`storage/app/private/invoices`), served only via `invoices.file` (web, auth+company scope)
  and `/api/invoices/{id}/file` (Sanctum). Legacy files on the `public` disk are still readable until `php artisan invoices:privatize-files --apply` is run.
- Do not log invoice content or model responses (personal data).
- Only strictly necessary cookies exist (session, XSRF-TOKEN, remember_web_*). Adding analytics/marketing requires an opt-in consent mechanism first.
- Landing page must not contain unverified claims (accuracy %, customer counts, testimonials, guarantees, free trial, native ERP connectors, "Most Popular").
  Plan feature lists are fixed in the views; the DB `plans.features` column holds legacy English marketing items and is not displayed.
- Screenshots/QA: `public` served with `php -S`, Playwright (`playwright-core` from the OpenClaw install + system Chrome); AdminLTE pages need a tall viewport, not fullPage.

## Tests
- `php artisan test`. Known pre-existing failures (also on 9632f01): PasswordResetTest ×3 (custom notification class), RegistrationTest (Stripe flow), ExampleTest (no DB).
- `tests/Feature/GreekLegalTest.php` covers legal pages, Greek UI, terms acceptance, private file access, deletion and the privatize command.
