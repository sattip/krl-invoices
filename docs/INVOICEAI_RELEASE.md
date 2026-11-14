# InvoiceAI — Greek UI + OBS legal pages: release / backup / rollback

Branch: `feat/el-obs-legal-20261114` (base `9632f01` = what production runs, Forge deployment 59520543).
Nothing has been pushed. **Forge Quick Deploy is ON for `main`: merging/pushing to `main` deploys immediately.**

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

## 0b. Blocking facts before showing prices with VAT wording
- The code adds **no tax** at Stripe Checkout (no `automatic_tax`, no `tax_rates`): the customer pays the Stripe price as-is.
- The live Stripe price objects (`plans.stripe_price_id`) could not be inspected (Stripe credentials are in the production DB, not read).
  `StripeService::createProductAndPrice()` creates prices in **USD** although the site shows €: if "Sync plans" was used, checkout may charge USD.
- Owner must confirm (a) live price currency/amount in the Stripe Dashboard and (b) whether 29/79/199 € include 24% VAT.
  Then set `INVOICEAI_PRICE_VAT_MODE=included` (or `excluded` — only if checkout is also changed to add VAT) in `.env` and run `php artisan config:cache`.
  Until then no VAT statement is shown (preview omits the unverified claim).

## 1. Backup (before deploy)
On the server (forge user), keep outside the web root:
```bash
cd /home/forge/invoiceai.gr
mysqldump --single-transaction --routines <db> > ~/backups/invoiceai-$(date +%F-%H%M).sql   # DB credentials from .env, not argv-logged
tar -czf ~/backups/invoiceai-storage-$(date +%F-%H%M).tgz storage/app                          # includes public/invoices + private
cp .env ~/backups/invoiceai-env-$(date +%F-%H%M)                                              # chmod 600
readlink current   # note the current release dir for rollback
```

## 2. Deploy
1. Review the diff (`git diff 9632f01...feat/el-obs-legal-20261114`), push the branch, merge to `main` (Quick Deploy runs the standard script:
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
- Code: Forge → Deployments → redeploy commit `9632f01` (or `git revert` the merge on `main`; Quick Deploy redeploys).
  The added columns are nullable and unused by the old code — no DB rollback needed. (`php artisan migrate:rollback --step=1` only if explicitly wanted.)
- Files: if old code is restored after `--apply`, old `Storage::url()` previews break for moved files. Restore with
  `rsync -a storage/app/private/invoices/ storage/app/public/invoices/` (copies back; nothing is deleted).
- Full restore: DB dump + storage tarball from step 1.

## API change (backwards compatible keys)
- `GET /api/invoices/{id}` keeps the `file_url` key; its value now points to the authenticated endpoint `GET /api/invoices/{id}/file`
  (Bearer token required) instead of a public `/storage/...` URL. Integrations that downloaded `file_url` without a token must send the token.
