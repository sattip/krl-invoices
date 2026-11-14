@extends('legal.layout')
@php($sessionCookie = config('session.cookie'))

@section('title', 'Cookie Policy')
@section('description', 'Cookies used by the InvoiceAI service.')

@section('content')
<h1>Cookie Policy</h1>
<p class="updated">Last updated: {{ config('legal.cookies_updated') }}</p>
@include('legal.en._note', ['greekRoute' => 'legal.cookies'])

<p>InvoiceAI uses <strong>only strictly necessary cookies</strong>, without which login and form protection do not work.
    We do not use analytics, advertising or social media cookies and do not load tracking tools.
    Strictly necessary cookies do not require consent (Article 4(5) of Greek Law 3471/2006), so we do not show a consent banner.</p>

<table>
    <thead><tr><th>Cookie</th><th>Purpose</th><th>Duration</th></tr></thead>
    <tbody>
    <tr><td><code>{{ $sessionCookie }}</code></td><td>Keeps your session (login, registration steps). HttpOnly.</td><td>{{ config('session.lifetime') }} minutes after your last action</td></tr>
    <tr><td><code>XSRF-TOKEN</code></td><td>Protects forms against CSRF attacks.</td><td>Same as the session</td></tr>
    <tr><td><code>remember_web_…</code></td><td>Only if you tick “Remember me” at login; keeps you logged in.</td><td>Until you log out or up to 400 days</td></tr>
    </tbody>
</table>

<h2>Payments through Stripe</h2>
<p>At checkout you are taken to Stripe’s page (checkout.stripe.com), which uses its own cookies to complete the payment and prevent fraud, according to <a href="https://stripe.com/cookies-policy/legal" rel="noopener" target="_blank">Stripe’s cookie policy</a>.</p>

<h2>How to control them</h2>
<p>You can delete or block cookies in your browser settings. If you block the necessary cookies, logging in to the app will not work.</p>

<p>If non-essential cookies (e.g. analytics) are added in the future, they will be enabled only after your explicit consent, with rejecting and withdrawing as easy as accepting.</p>

<p>More about data processing: <a href="{{ route('legal.privacy.en') }}">Privacy Policy</a>.</p>
@endsection
