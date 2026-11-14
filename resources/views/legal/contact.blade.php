@extends('legal.layout')
@php($c = config('legal.company'))

@section('title', 'Επικοινωνία')
@section('description', 'Στοιχεία επικοινωνίας και εταιρικά στοιχεία του InvoiceAI.')

@section('content')
<h1>Επικοινωνία</h1>
<p>Το InvoiceAI είναι υπηρεσία της {{ $c['brand'] }}.</p>

<h2>Υποστήριξη</h2>
<p>Για τεχνικά θέματα, λογαριασμό, χρεώσεις και αιτήματα για προσωπικά δεδομένα:<br>
    <a href="mailto:{{ $c['support_email'] }}">{{ $c['support_email'] }}</a></p>

<h2>Γενικές πληροφορίες και συνεργασίες</h2>
<p><a href="mailto:{{ $c['info_email'] }}">{{ $c['info_email'] }}</a> · Τηλ. <a href="tel:{{ preg_replace('/\s+/', '', $c['phone']) }}">{{ $c['phone'] }}</a></p>

<h2>Εταιρικά στοιχεία</h2>
<p>
    <strong>{{ $c['name'] }}</strong><br>
    {{ $c['address'] }}<br>
    ΑΦΜ {{ $c['vat'] }} · ΓΕΜΗ {{ $c['gemi'] }}<br>
    <a href="{{ $c['website'] }}" rel="noopener" target="_blank">{{ preg_replace('#^https?://#', '', $c['website']) }}</a>
</p>
@endsection
