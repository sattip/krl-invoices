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
- UI language is Greek only. `config('app.locale')` is fixed to `el` (not env-driven). Translations: `lang/el/*.php`, `lang/el.json`, `lang/vendor/adminlte/el`.
- Formatting helpers (`app/helpers.php`): `money_el()`, `number_el()`, `date_el()` (Europe/Athens display, UTC storage).
- Legal/corporate facts live in `config/legal.php` only. Legal pages: `resources/views/legal/*` (routes `legal.terms|privacy|cookies|contact`).
  Footer with legal links: `resources/views/partials/legal-links.blade.php` (used by landing, auth, Breeze layouts, AdminLTE footer).
- Invoice files: new uploads go to the private `local` disk (`storage/app/private/invoices`), served only via `invoices.file` (web, auth+company scope)
  and `/api/invoices/{id}/file` (Sanctum). Legacy files on the `public` disk are still readable until `php artisan invoices:privatize-files --apply` is run.
- Do not log invoice content or model responses (personal data).
- Only strictly necessary cookies exist (session, XSRF-TOKEN, remember_web_*). Adding analytics/marketing requires an opt-in consent mechanism first.
- Landing page must not contain unverified claims (accuracy %, customer counts, testimonials, guarantees, free trial, native ERP connectors).

## Tests
- `php artisan test`. Known pre-existing failures (also on 9632f01): PasswordResetTest ×3 (custom notification class), RegistrationTest (Stripe flow), ExampleTest (no DB).
- `tests/Feature/GreekLegalTest.php` covers legal pages, Greek UI, terms acceptance, private file access, deletion and the privatize command.
