{{--
    Legal footer used on every page type (landing, auth, AdminLTE app pages, legal pages).
    Framework-agnostic: inline styles only, so it renders the same with Tailwind or Bootstrap.
    $variant: 'auth' | 'app' | 'legal'
--}}
@php
    $legal = config('legal.company');
    $variant = $variant ?? 'auth';
    $muted = $variant === 'app' ? '#6c757d' : '#6b7280';
    $en = app()->getLocale() === 'en';
@endphp
<div class="obs-legal-links" style="font-size:12.5px;line-height:1.6;color:{{ $muted }};text-align:center;padding:{{ $variant === 'app' ? '0' : '16px 12px 20px' }};">
    <nav aria-label="{{ $en ? 'Legal information' : 'Νομικές πληροφορίες' }}" style="margin-bottom:4px;">
        <a href="{{ lroute('legal.terms') }}" style="color:inherit;text-decoration:underline;margin:0 6px;">{{ $en ? 'Terms of Use' : 'Όροι Χρήσης' }}</a>
        <a href="{{ lroute('legal.privacy') }}" style="color:inherit;text-decoration:underline;margin:0 6px;">{{ $en ? 'Privacy Policy' : 'Πολιτική Απορρήτου' }}</a>
        <a href="{{ lroute('legal.cookies') }}" style="color:inherit;text-decoration:underline;margin:0 6px;">{{ $en ? 'Cookie Policy' : 'Πολιτική Cookies' }}</a>
        <a href="{{ lroute('legal.contact') }}" style="color:inherit;text-decoration:underline;margin:0 6px;">{{ $en ? 'Contact' : 'Επικοινωνία' }}</a>
        @if($variant !== 'app')
            @include('partials.lang-switch', ['style' => 'color:inherit;text-decoration:underline;margin:0 6px;'])
        @endif
    </nav>
    <div>
        &copy; {{ date('Y') }} InvoiceAI {{ $en ? 'by' : 'από την' }} <a href="https://obstechnologies.gr" rel="noopener" target="_blank" style="color:inherit;text-decoration:underline;">OBS Technologies</a>
        · {{ $legal['name'] }} · {{ $en ? 'VAT' : 'ΑΦΜ' }} {{ $en ? $legal['vat_eu'] : $legal['vat'] }} · {{ $en ? 'GEMI' : 'ΓΕΜΗ' }} {{ $legal['gemi'] }}
        · <a href="mailto:{{ $legal['support_email'] }}" style="color:inherit;">{{ $legal['support_email'] }}</a>
    </div>
</div>
