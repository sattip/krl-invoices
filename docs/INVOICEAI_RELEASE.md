# InvoiceAI — Greek UI + OBS legal pages: release / backup / rollback

Branch: `feat/el-obs-reconcile-9559-20261114` (base **`9559c32`** = what production actually runs, release dir `releases/59520543`).
`9632f01` is NOT the production baseline: production runs server-only commit `9559c32` (WhatsApp/WaSenderAPI, not on GitHub `main`)
plus uncommitted server edits (see §0c). The earlier branch `feat/el-obs-legal-20261114` (a83c5e7) is merged into this branch; do not deploy it alone.
Nothing has been pushed. **Forge Quick Deploy is ON for `main`: merging/pushing to `main` deploys immediately.**

## Confirmed corporate address
George confirmed on 2026-11-14: **Συρράκου 5, 104 44 Αθήνα** (postal code/city match VIES).
`config/legal.php` is the source for Greek/English legal pages and contact details. The older GEMI address must not be published in this release.

## 0. Prerequisites (owner / Marina) — before or together with the deploy
Production `.env` (Forge → site → Environment). Values read on 2026-11-14 via an allowlisted, non-secret key audit:

| Key | Now | Set to | Why |
|---|---|---|---|
| `APP_DEBUG` | `true` | `false` | **Security: debug pages expose stack traces/env on any error.** Fix independently of this release, ASAP. |
| `APP_URL` | `http://invoiceai.gr` | `https://invoiceai.gr` | Correct links in emails/URLs. |
| `APP_NAME` | `Laravel` | `InvoiceAI` | Email sender name; session cookie becomes `invoiceai-session` (logs everyone out once). |
| `MAIL_MAILER` | `log` | real provider (SMTP/M365/etc.) | Today no email is delivered (verification, password reset, billing); full emails incl. reset links are written to `laravel.log`. Add the provider to the Privacy Policy processor table. |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | e.g. `support@obstechnologies.com` | Must be a sender the mail provider allows. |
| `LOG_STACK` / `LOG_LEVEL` | `single` / `debug` | `daily` / `warning`, `LOG_DAILY_DAYS=14` | Bounded log retention (Privacy Policy says logs are kept only as long as needed). |
| `APP_LOCALE` | `en` | `el` (optional) | Code now forces `el`; env value is ignored. |

## 0b. Confirmed VAT policy and outstanding Stripe verification
George confirmed on 2026-11-14 at 18:31 Athens that **29/79/199 EUR include VAT**. The code defaults to `included`; set `INVOICEAI_PRICE_VAT_MODE=included` explicitly in production as well. Do not add VAT a second time. Currency/amount verification below remains a launch gate.

### Historical pre-confirmation findings
- The code adds **no tax** at Stripe Checkout (no `automatic_tax`, no `tax_rates`): the customer pays the Stripe price as-is.
- The live Stripe price objects (`plans.stripe_price_id`) could not be inspected (Stripe credentials are in the production DB, not read).
  The former production `StripeService::createProductAndPrice()` created prices in **USD** although the site shows €: if "Sync plans" was used, checkout may charge USD. The candidate fixes new prices to EUR but does not mutate existing prices.
- Verify live price currency/amount using read-only access. The owner-confirmed VAT policy is inclusive. Run `php artisan config:cache` after the scoped environment merge. The final release must display VAT inclusion on both languages and all pricing surfaces.

### Currency fix — migration implications
- `StripeService::createProductAndPrice` now creates **EUR** prices (was hard-coded USD). It runs only for plans **without** `stripe_price_id`
  (Admin → "Sync plans"); existing price IDs, products and subscriptions are untouched, and Stripe prices are immutable.
- If the live `stripe_price_id`s turn out to be USD: create new EUR prices in the Stripe Dashboard (or clear the plan's `stripe_price_id` and sync),
  update `plans.stripe_price_id`, and decide separately how to move existing USD subscribers (Stripe subscription item price change; owner decision).
- Read-only verification of the live prices was **not possible** from here: the live Stripe key is stored only in the production DB
  (`stripe_settings`), there is no SSH access, the only local Stripe key is a test-mode key, and Forge site commands would execute code on production.
  Check in Stripe Dashboard → Product catalogue → each InvoiceAI price: currency, amount, tax behaviour.

## 0c. Actual live baseline (read-only capture 2026-11-14)
- HEAD `9559c326db1a040348dcb048e411fa1c90fda69d` (parent 9632f01): WhatsApp settings page/routes (`company.whatsapp*`), WaSenderAPI service,
  notification channel + 5 notifications, `whatsapp_settings` migration (already ran in prod, batch 5), Stripe webhook payment notifications.
- Dirty tracked edits on the server: (1) `WhatsAppSettings` accessors `?$value` → `?string $value` (the committed file is a PHP parse error) —
  **carried into this branch**; (2) `composer.json` adds path repo `./packages/obstechnologies/invoiceai` + `obstechnologies/invoiceai:*` —
  **not carried**: `composer.lock` was never updated and `vendor/` has no such package, so it is not loaded in production today;
  a fresh Forge release would not contain the untracked `packages/` dir and `composer install` would stop on a lock mismatch.
  The package (22 files, own routes `api/invoiceai/*`, own migrations) needs a separate owner decision/review before it is activated.
  Protected source snapshot (no .git) is kept outside the repo by obs-dev.
- Fixed in this branch (live bugs): model table name `whatsapp_settings` (Eloquent defaulted to `whats_app_settings`, so the live settings page
  and notifications fail), `InvoiceProcessed` message fields (`issuer_name`/`grand_total`).
- WhatsApp UI is Greek: settings page (labels, buttons, help, status, SweetAlert dialogs), controller flash/JSON messages,
  WhatsApp message texts and webhook fallbacks. Provider/technical names (WaSenderAPI, API, Session ID, URLs, field names) unchanged.
  Test endpoints show only generic Greek errors; raw provider/exception text is logged, never shown.
- Private package `obstechnologies/invoiceai` stays **inactive exactly as in the live runtime** (not in composer.json/lock, no `packages/` dir).
- Security: lock-only updates within majors (composer audit 56 → 0, npm audit 14 → 0 at 2026-11-14); `composer.json`/`package.json` unchanged.
- The server's Git remote URL contains an inline credential; prefer a Forge deploy key and rotate that token (owner action).

## 0d. Free trial release (branch `feat/invoiceai-free-trial-20261114`, base `c04c1a3`)
Owner-approved offer (George, 2026-11-14 23:05): **14 days, 10 invoices total, no card, no automatic charge, new registrations only, existing subscriptions unchanged.**
- Migration (runs automatically on deploy via `migrate --force`): `2026_11_14_000002_create_trial_entitlements_table` — **new table only**
  (`company_id` unique FK cascade, `starts_at`, `ends_at`, `invoice_limit`, `invoices_used`, timestamps, index `ends_at`). No change to `subscriptions`,
  `plans`, `invoice_usage`, no backfill. Existing companies get no trial row.
- Optional env: `INVOICEAI_TRIAL_ENABLED=false` hides/rejects the trial for new sign-ups (default on). Run `php artisan config:cache` after env changes.
- No Stripe settings/prices change. Trial sign-up creates no Stripe customer/subscription and sends no email (verification is not enforced; unchanged).
- Post-deploy checks (read-only + one synthetic sign-up only if the owner approves a test account):
  `/register?utm_source=obstechnologies.gr&utm_medium=referral&utm_campaign=invoice-ai-page` → 200, trial card selected, button «Έναρξη δωρεάν δοκιμής»;
  `/terms` §5 shows the trial clause; `/en/terms` likewise; only session/XSRF cookies.
  DB: `select count(*) from trial_entitlements` grows only with new sign-ups; `subscriptions` unchanged.
- Rollback: switch the release symlink back (§4). The new table is unused by older code; dropping it is optional
  (`php artisan migrate:rollback --step=1` would delete trial rows — only if explicitly wanted). Trial users created meanwhile would then have no entitlement
  (old code: no subscription → upload blocked, data still readable).

## 1. Backup (before deploy)
Protected verified backup already exists: `/home/forge/.obs-backups/invoiceai-20261114-phase1` (site + DB + env + live-drift patch).
On the server (forge user), keep outside the web root:
```bash
cd /home/forge/invoiceai.gr
mysqldump --single-transaction --routines <db> > ~/backups/invoiceai-$(date +%F-%H%M).sql   # DB credentials from .env, not argv-logged
tar -czf ~/backups/invoiceai-storage-$(date +%F-%H%M).tgz storage/app                          # includes public/invoices + private
cp .env ~/backups/invoiceai-env-$(date +%F-%H%M)                                              # chmod 600
readlink current   # note the current release dir for rollback
```

## 2. Deploy
1. Review the diff (`git diff 9559c32...feat/el-obs-reconcile-9559-20261114`; note the live server edits in §0c), push the branch, merge to `main` (Quick Deploy runs the standard script:
   composer, `npm ci && npm run build`, `optimize`, `storage:link`, `migrate --force`).
2. Migration in this release (additive only): `2026_11_14_000001_add_terms_acceptance_to_users_table` → nullable `users.terms_accepted_at`, `users.terms_version`.
3. After deploy, move legacy invoice files out of the public web path (dry-run first):
   ```bash
   php artisan invoices:privatize-files          # dry run: counts public_only / already_private / missing
   php artisan invoices:privatize-files --apply  # copy → SHA-256 verify → delete public copy
   ```
   Until `--apply` runs, old uploads remain reachable at `https://invoiceai.gr/storage/invoices/<random>.pdf` (pre-existing exposure).
   New uploads already go to `storage/app/private/invoices` and are served only via authenticated routes.

## 3. Verify (read-only)
- `/`, `/terms`, `/privacy`, `/cookies`, `/contact`, `/login`, `/register` → 200, `<html lang="el">`, footer with Terms/Privacy/Cookies.
- `/en`, `/en/terms`, `/en/privacy`, `/en/cookies`, `/en/contact` → 200, `<html lang="en">`; canonical = own URL; reciprocal `hreflang` el/en/x-default.
- `/oroi-xrisis` → 301 `/terms`, `/politiki-aporritou` → 301 `/privacy`.
- Logged-in: dashboard, invoices list/show (PDF preview loads via `/invoices/{id}/file`), billing, profile, API docs.
- `curl -I https://invoiceai.gr/storage/invoices/<known old file>` → 404 after `--apply`.
- No requests to fonts.googleapis.com / fonts.bunny.net in the browser network tab.

## 4. Rollback
- Code: **do not redeploy 9632f01** (it would drop the live WhatsApp feature). The exact previous state (9559c32 + server edits) only exists in
  release dir `releases/59520543` (and the protected backup), so roll back by switching the symlink, not by a Git redeploy:
  `ln -s /home/forge/invoiceai.gr/releases/59520543 /home/forge/invoiceai.gr/current.rollback && mv -Tf /home/forge/invoiceai.gr/current.rollback /home/forge/invoiceai.gr/current`
  Keep that release dir (check Forge release retention before deploying). Then rebuild caches from that release only (`php8.3 artisan config:cache` etc.).
  The added columns are nullable and unused by the old code — no DB rollback needed. (`php artisan migrate:rollback --step=1` only if explicitly wanted.)
- Files: if old code is restored after `--apply`, old `Storage::url()` previews break for moved files. Restore with
  `rsync -a storage/app/private/invoices/ storage/app/public/invoices/` (copies back; nothing is deleted).
- Full restore: DB dump + storage tarball from step 1.

## API change (backwards compatible keys)
- `GET /api/invoices/{id}` keeps the `file_url` key; its value now points to the authenticated endpoint `GET /api/invoices/{id}/file`
  (Bearer token required) instead of a public `/storage/...` URL. Integrations that downloaded `file_url` without a token must send the token.
