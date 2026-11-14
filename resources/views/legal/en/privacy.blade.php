@extends('legal.layout')
@php($c = config('legal.company'))

@section('title', 'Privacy Policy')
@section('description', 'How OBS TECHNOLOGIES Ε.Ε. processes data in the InvoiceAI service.')

@section('content')
<h1>Privacy Policy</h1>
<p class="updated">Last updated: {{ config('legal.privacy_updated') }}</p>
@include('legal.en._note', ['greekRoute' => 'legal.privacy'])

<p>This policy explains which data the InvoiceAI service processes, for what purpose, with which providers and for how long. It describes the service as currently implemented.</p>

<h2>1. Who is responsible</h2>
<p><strong>{{ $c['name'] }}</strong>, {{ $c['address'] }}, Greece, VAT {{ $c['vat_eu'] }}, GEMI {{ $c['gemi'] }}.
    Contact for data protection matters: <a href="mailto:{{ $c['support_email'] }}">{{ $c['support_email'] }}</a>.</p>
<ul>
    <li><strong>Controller</strong>: OBS, for account, subscription, communication and security data.</li>
    <li><strong>Processor</strong>: OBS, for personal data contained in the invoices you upload. Your business is the controller of that data (see section 7 of the <a href="{{ route('legal.terms.en') }}">Terms of Use</a>).</li>
</ul>

<h2>2. Data we process</h2>
<table>
    <thead><tr><th>Category</th><th>What it includes</th><th>Purpose and legal basis</th></tr></thead>
    <tbody>
    <tr><td>Account</td><td>Name, email, password (stored only as a hash), company name, role, email verification, time and version of Terms acceptance.</td><td>Creating and running the account: performance of contract (Art. 6(1)(b) GDPR).</td></tr>
    <tr><td>Company details</td><td>Whatever you enter in the settings (e.g. name, email, phone, address, VAT number).</td><td>Providing the service: performance of contract.</td></tr>
    <tr><td>Invoices</td><td>The files you upload, the original file name, the extracted data (issuer, customer, VAT numbers, addresses, lines, amounts) and the raw response of the AI model.</td><td>Extracting and presenting data on your behalf, as processor.</td></tr>
    <tr><td>API tokens</td><td>Token name and hash, last-used date.</td><td>API access: performance of contract.</td></tr>
    <tr><td>Subscription and payments</td><td>Stripe customer and subscription identifiers, plan, status, billing period, monthly usage. You enter card details directly with Stripe; we do <strong>not</strong> receive or store them.</td>
        <td>Charging the subscription: performance of contract; keeping accounting records: legal obligation (Art. 6(1)(c)).</td></tr>
    <tr><td>Technical data</td><td>IP address and browser in the login session, server and application error logs.</td><td>Security, abuse prevention and troubleshooting: legitimate interest (Art. 6(1)(f)).</td></tr>
    <tr><td>WhatsApp notifications (optional)</td><td>If a company administrator enables them: the WaSenderAPI account API key and webhook secret (stored encrypted), the recipient phone number and notification settings. Messages contain details such as invoice number and issuer, amounts, file name, or subscription payment amount and status.</td><td>Notifications you requested: performance of contract.</td></tr>
    <tr><td>Communication</td><td>Whatever you send us by email.</td><td>Replying and support: performance of contract or legitimate interest.</td></tr>
    </tbody>
</table>
<p>We do not use analytics, advertising or tracking tools and do not send marketing messages. Emails sent by the service relate only to running the account (e.g. email verification, password reset, welcome message).</p>

<h2>3. How AI extraction works</h2>
<ul>
    <li>When you upload an invoice, the file content is sent via API to Anthropic, which provides the Claude model, so that structured data can be returned.</li>
    <li>Extraction is automated but makes no decisions about individuals; results must be checked by you or your accountant.</li>
    <li>If extraction fails, the file is deleted from our server immediately.</li>
    <li>Retention and use of data by Anthropic is governed by its commercial terms and privacy policy (<a href="https://www.anthropic.com/legal/commercial-terms" rel="noopener" target="_blank">anthropic.com/legal</a>).</li>
</ul>

<h2 id="processors">4. Providers (recipients) and transfers</h2>
<table>
    <thead><tr><th>Provider</th><th>Role</th><th>Location</th></tr></thead>
    <tbody>
    <tr><td>Hetzner Online GmbH</td><td>Hosting of the server, database and invoice files.</td><td>Germany (Falkenstein data centre)</td></tr>
    <tr><td>Anthropic PBC</td><td>AI model used to extract data from invoices.</td><td>USA</td></tr>
    <tr><td>Stripe</td><td>Payments and subscription management; receives name, email, company name and the payment details you enter on its page. For some processing (e.g. fraud prevention) it acts as an independent controller.</td><td>EU / USA</td></tr>
    <tr><td>WaSenderAPI (only if WhatsApp notifications are enabled)</td><td>Sends WhatsApp notifications through the WaSenderAPI account your company connects; receives the recipient number and the message text, which is delivered via WhatsApp.</td><td>As per the provider's terms</td></tr>
    <tr><td>Laravel (Forge)</td><td>Server management and deployment tool with technical access to the server.</td><td>USA</td></tr>
    </tbody>
</table>
<p>Where data is transferred outside the European Economic Area (in particular to the USA), the transfer relies on the Chapter V GDPR safeguards provided in each provider’s terms (e.g. the EU-US Data Privacy Framework adequacy decision or standard contractual clauses). We do not sell data or share it for advertising. We may disclose data to authorities only where required by law.</p>

<h2>5. How long we keep data</h2>
<ul>
    <li><strong>Invoices and extracted data:</strong> until you delete them. Deleting an invoice removes its data and file. They are not deleted automatically when the subscription ends; for full deletion of your company’s data email <a href="mailto:{{ $c['support_email'] }}">{{ $c['support_email'] }}</a>.</li>
    <li><strong>User account:</strong> until you delete it from “Profile”. The invoices you uploaded, their files and your API tokens are deleted with it. The company subscription is not cancelled automatically.</li>
    <li><strong>Login session:</strong> expires after {{ config('session.lifetime') }} minutes of inactivity, unless you choose “Remember me”.</li>
    <li><strong>Billing records:</strong> as long as tax law requires.</li>
    <li><strong>Logs:</strong> as long as needed for security and troubleshooting. They do not contain invoice files or extracted invoice data; on errors they may contain the file name and the error message.</li>
    <li><strong>Server backups</strong>, where they exist, rotate; deleted data drops out of them as they rotate.</li>
</ul>

<h2>6. Security</h2>
<p>Access is over HTTPS. Passwords and API tokens are stored only as hashes. Each company’s data is kept separate, and invoice files are shown only to logged-in users of the same company or through a valid API token. No measure is perfectly secure; if we detect a breach affecting you, we will inform you as required by law.</p>

<h2>7. Your rights</h2>
<p>You have the rights of access, rectification, erasure, restriction, portability and objection, where applicable. Send your request to <a href="mailto:{{ $c['support_email'] }}">{{ $c['support_email'] }}</a>; we will reply within one month. Many of these you can exercise yourself in the app (editing your profile, deleting invoices, exporting data through the API, deleting your account).</p>
<p>If you are a person whose details appear on an invoice uploaded by one of our customers, please contact that business first; we will help it respond.</p>
<p>You also have the right to lodge a complaint with the Hellenic Data Protection Authority (<a href="https://www.dpa.gr" rel="noopener" target="_blank">www.dpa.gr</a>).</p>

<h2>8. Cookies</h2>
<p>We use only strictly necessary cookies for login and security. See the <a href="{{ route('legal.cookies.en') }}">Cookie Policy</a>.</p>

<h2>9. Changes</h2>
<p>We will update this policy when processing or providers change. The date at the top shows the latest change.</p>
@endsection
