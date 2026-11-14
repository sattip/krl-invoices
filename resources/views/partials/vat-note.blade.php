{{-- VAT statement next to prices; rendered only once the owner sets config('legal.pricing.vat_mode'). --}}
@php($vatMode = config('legal.pricing.vat_mode'))
@if(in_array($vatMode, ['included', 'excluded'], true))
    <div class="{{ $class ?? 'small text-muted' }}" data-vat-mode="{{ $vatMode }}">{{ __('landing.vat_'.$vatMode) }}</div>
@endif
