{{-- Language switch: links to the /en twin on public pages, else to /lang/{locale}. --}}
@php
    $alternates = locale_alternates();
    $isEn = app()->getLocale() === 'en';
    $target = $isEn ? 'el' : 'en';
    $href = $alternates[$target] ?? route('locale.switch', $target);
@endphp
<a href="{{ $href }}" hreflang="{{ $target }}" lang="{{ $target }}" class="{{ $class ?? '' }}" style="{{ $style ?? '' }}">{{ $isEn ? 'Ελληνικά' : 'English' }}</a>
