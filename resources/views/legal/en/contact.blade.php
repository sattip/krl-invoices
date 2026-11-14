@extends('legal.layout')
@php($c = config('legal.company'))

@section('title', 'Contact')
@section('description', 'Contact and company details of InvoiceAI.')

@section('content')
<h1>Contact</h1>
<p>InvoiceAI is a service by {{ $c['brand'] }}.</p>

<h2>Support</h2>
<p>Technical questions, account, billing and data protection requests:<br>
    <a href="mailto:{{ $c['support_email'] }}">{{ $c['support_email'] }}</a></p>

<h2>General enquiries and partnerships</h2>
<p><a href="mailto:{{ $c['info_email'] }}">{{ $c['info_email'] }}</a> · Tel. <a href="tel:{{ preg_replace('/\s+/', '', $c['phone']) }}">{{ $c['phone'] }}</a></p>

<h2>Company details</h2>
<p>
    <strong>{{ $c['name'] }}</strong><br>
    {{ $c['address'] }}, Greece<br>
    VAT {{ $c['vat_eu'] }} · GEMI {{ $c['gemi'] }}<br>
    <a href="{{ $c['website'] }}" rel="noopener" target="_blank">{{ preg_replace('#^https?://#', '', $c['website']) }}</a>
</p>
@endsection
