{{--
    Legal footer used on every page type (landing, auth, AdminLTE app pages, legal pages).
    Framework-agnostic: inline styles only, so it renders the same with Tailwind or Bootstrap.
    $variant: 'auth' | 'app' | 'legal'
--}}
@php
    $legal = config('legal.company');
    $variant = $variant ?? 'auth';
    $muted = $variant === 'app' ? '#6c757d' : '#6b7280';
@endphp
<div class="obs-legal-links" style="font-size:12.5px;line-height:1.6;color:{{ $muted }};text-align:center;padding:{{ $variant === 'app' ? '0' : '16px 12px 20px' }};">
    <nav aria-label="Νομικές πληροφορίες" style="margin-bottom:4px;">
        <a href="{{ route('legal.terms') }}" style="color:inherit;text-decoration:underline;margin:0 6px;">Όροι Χρήσης</a>
        <a href="{{ route('legal.privacy') }}" style="color:inherit;text-decoration:underline;margin:0 6px;">Πολιτική Απορρήτου</a>
        <a href="{{ route('legal.cookies') }}" style="color:inherit;text-decoration:underline;margin:0 6px;">Πολιτική Cookies</a>
        <a href="{{ route('legal.contact') }}" style="color:inherit;text-decoration:underline;margin:0 6px;">Επικοινωνία</a>
    </nav>
    <div>
        &copy; {{ date('Y') }} InvoiceAI · {{ $legal['name'] }} · ΑΦΜ {{ $legal['vat'] }} · ΓΕΜΗ {{ $legal['gemi'] }}
        · <a href="mailto:{{ $legal['support_email'] }}" style="color:inherit;">{{ $legal['support_email'] }}</a>
    </div>
</div>
