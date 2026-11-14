@extends('legal.layout')
@php($c = config('legal.company'))

@section('title', 'Terms of Use')
@section('description', 'Terms of use of the InvoiceAI service by OBS TECHNOLOGIES Ε.Ε.')

@section('content')
<h1>InvoiceAI Terms of Use</h1>
<p class="updated">Last updated: {{ config('legal.terms_updated') }} · Version {{ config('legal.terms_version') }}</p>
@include('legal.en._note', ['greekRoute' => 'legal.terms'])

<h2>1. Provider</h2>
<p>The InvoiceAI service (<a href="{{ config('legal.service.url') }}">{{ config('legal.service.url') }}</a>) is provided by
    <strong>{{ $c['name'] }}</strong>, {{ $c['address'] }}, Greece, VAT {{ $c['vat_eu'] }}, GEMI {{ $c['gemi'] }},
    email <a href="mailto:{{ $c['support_email'] }}">{{ $c['support_email'] }}</a> (“we” or “OBS”).</p>
<p>The service is intended for businesses and professionals. By creating an account you confirm that you act on behalf of the business you register and are authorised to bind it.</p>

<h2>2. What the service does</h2>
<ul>
    <li>You upload invoice files (PDF, JPG or PNG, up to 10 MB each) in the app or through the REST API.</li>
    <li>The file is sent to an AI model of a third-party provider (Anthropic), which returns structured data: issuer, customer, VAT numbers, document number and date, line items, discounts, other charges, VAT and totals.</li>
    <li>The data is stored in your company account, shown in the app and made available as JSON through the API, so you can move it into your own accounting or ERP system with your own integration.</li>
</ul>

<h2>3. What the service does not do</h2>
<div class="note">
    <p><strong>Results are generated automatically and may contain errors or omissions.</strong> They must be checked by you or your accountant before any booking, payment, filing or other accounting/tax use.</p>
</div>
<ul>
    <li>It does not issue invoices and does not submit data to AADE / myDATA or any other public authority.</li>
    <li>It does not provide accounting, tax or legal advice and does not check whether documents are lawful or correct.</li>
    <li>It does not include ready-made (native) connectors for specific ERPs. Any integration is built on the REST API, at your responsibility or under a separate implementation agreement.</li>
    <li>It does not guarantee any specific accuracy rate.</li>
</ul>

<h2>4. Account</h2>
<ul>
    <li>Provide true and current details and keep your password and API tokens safe. Tokens give full access to your company’s invoices; revoke them immediately if leaked.</li>
    <li>You are responsible for actions taken with your account’s credentials.</li>
</ul>

<h2>5. Subscription and payment</h2>
<ul>
    <li>Use is based on a monthly subscription to the plans shown on the pricing page. Each plan has a monthly invoice limit; only successfully processed invoices count.</li>
    <li>Payment is made by card through Stripe. The final amount is shown on the Stripe payment page before you confirm. The subscription renews automatically every month.</li>
    <li>You can cancel from the “Billing &amp; usage” page. Cancellation takes effect at the end of the current billing period; until then access continues.</li>
    <li>If a payment fails, access continues for a grace period of {{ config('subscription.grace_period_days') }} days, after which uploading new invoices is restricted until the payment is settled.</li>
    <li>For refund requests or billing questions contact <a href="mailto:{{ $c['support_email'] }}">{{ $c['support_email'] }}</a>. Your statutory rights apply.</li>
</ul>

<h2>6. Data you upload</h2>
<ul>
    <li>Upload only documents you are entitled to process. Invoices may contain personal data of third parties (e.g. names, addresses, VAT numbers of individuals); your business is the controller of that data.</li>
    <li>You keep all rights to your documents and data. You grant us only the rights needed to provide the service.</li>
    <li>You can delete an invoice at any time; its extracted data and file are deleted with it.</li>
</ul>

<h2>7. Processing on your behalf</h2>
<p>For personal data contained in the invoices you upload, OBS acts as processor (Article 28 GDPR) on behalf of your business. In particular we:</p>
<ul>
    <li>process it only to provide the service, following your instructions as expressed through your use of the app and the API;</li>
    <li>ensure that persons with access are bound by confidentiality;</li>
    <li>use the sub-processors listed in the <a href="{{ route('legal.privacy.en') }}#processors">Privacy Policy</a> and will inform you of changes;</li>
    <li>assist you, as far as reasonable, with data subject requests and personal data breaches, which we will report to you without undue delay;</li>
    <li>delete your company’s data at the end of the relationship upon your request, unless the law requires it to be kept.</li>
</ul>
<p>If you need a separate data processing agreement (DPA), contact <a href="mailto:{{ $c['info_email'] }}">{{ $c['info_email'] }}</a>.</p>

<h2>8. Acceptable use</h2>
<p>You may not upload unlawful or malicious content, attempt unauthorised access, circumvent usage limits, overload the service, or resell it without a written agreement. We may suspend an account that breaches these terms, informing you where possible.</p>

<h2>9. Availability and changes to the service</h2>
<p>We make reasonable efforts to keep the service running, without guaranteeing uninterrupted availability or specific processing times. Processing also depends on third-party providers (e.g. Anthropic, Stripe). Maintenance or feature changes may take place.</p>

<h2>10. Liability</h2>
<p>To the extent permitted by law, OBS is not liable for damage caused by using extracted data without the review described in section 3, or for interruptions caused by third-party providers or force majeure. Liability for wilful misconduct or gross negligence, or where the law does not allow exclusion, is not excluded.</p>

<h2>11. Termination and deletion</h2>
<ul>
    <li>After the subscription is cancelled, the account and company data are not deleted automatically, so you can retrieve them.</li>
    <li>You can delete your personal account from the “Profile” page. The invoices you uploaded, their files and your API tokens are deleted with it. For full deletion of all company data, email <a href="mailto:{{ $c['support_email'] }}">{{ $c['support_email'] }}</a>.</li>
</ul>

<h2>12. Changes to these terms</h2>
<p>We may amend these terms. For material changes we will inform customers in good time before they take effect. Continued use after that date means acceptance; otherwise you may cancel the subscription.</p>

<h2>13. Governing law</h2>
<p>These terms are governed by Greek law. The courts of Athens have jurisdiction, subject to mandatory provisions of law.</p>

<h2>14. Contact</h2>
<p>Support: <a href="mailto:{{ $c['support_email'] }}">{{ $c['support_email'] }}</a> · General: <a href="mailto:{{ $c['info_email'] }}">{{ $c['info_email'] }}</a> · Tel. {{ $c['phone'] }} · {{ $c['address'] }}, Greece</p>
@endsection
