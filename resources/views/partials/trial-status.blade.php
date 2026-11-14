{{-- Local free trial status (Greek-only app). Shown only when the trial governs uploads (no active paid subscription). --}}
@php
    $trial = isset($company) && $company ? $company->governingTrial() : null;
@endphp
@if($trial)
    @php
        $expired = $trial->isExpired();
        $full = ! $expired && $trial->remaining() <= 0;
    @endphp
    <div class="row" data-trial-status="{{ $expired ? 'expired' : ($full ? 'limit' : 'active') }}">
        <div class="col-12">
            @if($expired || $full)
                <div class="alert alert-warning">
                    <i class="fas fa-hourglass-end mr-2"></i>
                    <strong>{{ $expired ? 'Η δωρεάν δοκιμή έληξε' : 'Ολοκληρώθηκαν τα τιμολόγια της δωρεάν δοκιμής' }}</strong> —
                    @if($expired)
                        έληξε στις {{ date_el($trial->ends_at, true) }}.
                    @else
                        χρησιμοποιήσατε και τα {{ number_el($trial->invoice_limit) }} τιμολόγια.
                    @endif
                    Δεν έγινε καμία χρέωση. Τα τιμολόγια που ανεβάσατε παραμένουν διαθέσιμα.
                    Για να ανεβάσετε νέα, <a href="{{ route('billing.plans') }}">επιλέξτε πακέτο συνδρομής</a>.
                </div>
            @else
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-gift mr-2"></i>Δωρεάν δοκιμή</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Τιμολόγια δοκιμής</span>
                            <strong>{{ number_el($trial->invoices_used) }} / {{ number_el($trial->invoice_limit) }}</strong>
                        </div>
                        <div class="progress mb-3" style="height: 20px;">
                            @php $pct = $trial->invoice_limit > 0 ? min(100, round($trial->invoices_used / $trial->invoice_limit * 100)) : 100; @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%"
                                 aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">{{ number_el($pct) }}%</div>
                        </div>
                        <p class="mb-2">
                            Απομένουν <strong>{{ number_el($trial->remaining()) }}</strong> τιμολόγια ·
                            λήγει στις <strong>{{ date_el($trial->ends_at, true) }}</strong>
                        </p>
                        <p class="text-muted mb-0">
                            Χωρίς κάρτα, χωρίς αυτόματη ανανέωση ή χρέωση. Η δοκιμή δεν μετατρέπεται αυτόματα σε συνδρομή·
                            χρέωση γίνεται μόνο αν επιλέξετε πακέτο και ολοκληρώσετε την πληρωμή μέσω Stripe.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
