<?php

/*
|--------------------------------------------------------------------------
| Legal / corporate facts for InvoiceAI (operated by OBS TECHNOLOGIES Ε.Ε.)
|--------------------------------------------------------------------------
|
| Single source for the legal pages, footer and emails. Change values here
| (not in the views). Facts marked "verified" were confirmed on 2026-11-14:
|  - name, VAT number, support/info email: confirmed by the owner.
|  - name, VAT number, GEMI, status «Ενεργή» and address: confirmed 2026-11-14 in the
|    public GEMI registry (publicity.businessportal.gr, ΓΕΜΗ 163724403000).
|    Owner confirmed «Συρράκου 5» on 2026-11-14; postal code/city match VIES.
|    Use this confirmed address below; GEMI still showed the former address.
|  - phone: published on https://www.obstechnologies.gr only.
|
| Bump the versions/dates whenever the text of a policy changes materially.
*/

return [
    'company' => [
        'name' => 'OBS TECHNOLOGIES Ε.Ε.',
        'brand' => 'OBS Technologies',
        'vat' => '801807920',
        'vat_eu' => 'EL801807920',
        'gemi' => '163724403000',
        'address' => 'Συρράκου 5, 104 44 Αθήνα',
        'phone' => '+30 211 800 5101',
        'support_email' => 'support@obstechnologies.com',
        'info_email' => 'info@obstechnologies.com',
        'website' => 'https://obstechnologies.gr',
    ],

    'service' => [
        'name' => 'InvoiceAI',
        'url' => 'https://invoiceai.gr',
    ],

    /*
     | George confirmed on 2026-11-14 that 29/79/199 EUR INCLUDE VAT.
     | These are gross totals: do not add VAT again at checkout.
     | Live Stripe price amounts/currency must still be verified before launch.
     | Explicit deployment override is available for future approved policy changes.
     */
    'pricing' => [
        'vat_mode' => env('INVOICEAI_PRICE_VAT_MODE', 'included'),
    ],

    // 2026-11-14b: §5 free trial clause (14 days / 10 invoices total, no card, no auto renewal).
    'terms_version' => '2026-11-14b',
    'terms_updated' => '14/11/2026',
    'privacy_updated' => '14/11/2026',
    'cookies_updated' => '14/11/2026',

];
