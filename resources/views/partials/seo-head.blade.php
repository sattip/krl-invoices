{{--
    Localized <title>, description, canonical and reciprocal hreflang for public pages.
    Usage: @include('partials.seo-head', ['title' => ..., 'description' => ...])
--}}
@php
    $alternates = locale_alternates();
    $canonical = $alternates[app()->getLocale()] ?? url()->current();
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">
@if($alternates)
<link rel="alternate" hreflang="el" href="{{ $alternates['el'] }}">
<link rel="alternate" hreflang="en" href="{{ $alternates['en'] }}">
<link rel="alternate" hreflang="x-default" href="{{ $alternates['el'] }}">
@endif
<meta property="og:type" content="website">
<meta property="og:site_name" content="InvoiceAI">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_US' : 'el_GR' }}">
<meta property="og:locale:alternate" content="{{ app()->getLocale() === 'en' ? 'el_GR' : 'en_US' }}">
