@extends('adminlte::page')

@section('title', 'Χρέωση & Χρήση')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Χρέωση & Χρήση</h1>
        @if($subscription)
            <a href="{{ route('billing.portal') }}" class="btn btn-outline-primary">
                <i class="fas fa-external-link-alt mr-1"></i> Πύλη πελάτη Stripe
            </a>
        @endif
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('warning') }}
        </div>
    @endif

    <div class="row">
        <!-- Current Plan -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Τρέχον πακέτο</h3>
                </div>
                <div class="card-body">
                    @if($plan)
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge badge-{{ $plan->isEnterprise() ? 'purple' : ($plan->isProfessional() ? 'primary' : 'info') }} p-2 mr-3">
                                {{ $plan->name }}
                            </span>
                            <div>
                                <strong>{{ $plan->formatted_price }} / μήνα</strong>
                                @include('partials.vat-note')
                            </div>
                        </div>

                        @if($subscription)
                            <p class="mb-2">
                                <strong>Κατάσταση:</strong>
                                @if($subscription->isActive())
                                    <span class="badge badge-success">Ενεργή</span>
                                @elseif($subscription->isCanceled())
                                    <span class="badge badge-warning">Σε ακύρωση</span>
                                @elseif($subscription->isPastDue())
                                    <span class="badge badge-danger">Ληξιπρόθεσμη</span>
                                @else
                                    <span class="badge badge-secondary">{{ match($subscription->status) {
                                        'unpaid' => 'Απλήρωτη',
                                        'trialing' => 'Δοκιμαστική περίοδος',
                                        'incomplete' => 'Μη ολοκληρωμένη',
                                        'incomplete_expired' => 'Έληξε (μη ολοκληρωμένη)',
                                        'canceled' => 'Ακυρωμένη',
                                        'paused' => 'Σε παύση',
                                        default => ucfirst((string) $subscription->status),
                                    } }}</span>
                                @endif
                            </p>

                            @if($subscription->isCanceled() && !$subscription->hasEnded())
                                <p class="text-warning mb-2">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Η συνδρομή σας θα λήξει στις {{ date_el($subscription->current_period_end) }}
                                </p>
                                <form action="{{ route('billing.resume') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-undo mr-1"></i> Επανενεργοποίηση συνδρομής
                                    </button>
                                </form>
                            @else
                                <p class="mb-2">
                                    <strong>Επόμενη χρέωση:</strong> {{ date_el($subscription->current_period_end) }}
                                </p>
                            @endif
                        @endif

                        <hr>

                        <a href="{{ route('billing.plans') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-up mr-1"></i> Αλλαγή πακέτου
                        </a>

                        @if($subscription && $subscription->isActive() && !$subscription->isCanceled())
                            <form action="{{ route('billing.cancel') }}" method="POST" class="d-inline ml-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger"
                                        onclick="return confirm('Είστε βέβαιοι ότι θέλετε να ακυρώσετε τη συνδρομή σας; Η ακύρωση ισχύει στο τέλος της τρέχουσας περιόδου χρέωσης.')">
                                    Ακύρωση συνδρομής
                                </button>
                            </form>
                        @endif
                    @else
                        <p class="text-muted mb-3">Δεν έχετε ενεργή συνδρομή.</p>
                        <a href="{{ route('billing.plans') }}" class="btn btn-primary">
                            <i class="fas fa-rocket mr-1"></i> Εγγραφή σε πακέτο
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Usage This Month -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Χρήση τρέχοντος μήνα</h3>
                </div>
                <div class="card-body">
                    @if($plan)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Παραστατικά που χρησιμοποιήθηκαν</span>
                            <strong>{{ number_el($usage['used']) }} / {{ number_el($usage['limit']) }}</strong>
                        </div>

                        <div class="progress mb-3" style="height: 20px;">
                            @php
                                $progressClass = $usage['percentage'] >= 90 ? 'bg-danger' :
                                                ($usage['percentage'] >= 80 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="progress-bar {{ $progressClass }}"
                                 role="progressbar"
                                 style="width: {{ $usage['percentage'] }}%"
                                 aria-valuenow="{{ $usage['percentage'] }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                {{ number_el($usage['percentage']) }}%
                            </div>
                        </div>

                        <p class="mb-0">
                            Απομένουν <strong>{{ number_el($usage['remaining']) }}</strong> παραστατικά για τον τρέχοντα μήνα
                        </p>

                        @if($usage['percentage'] >= 80)
                            <hr>
                            <div class="alert alert-{{ $usage['percentage'] >= 90 ? 'danger' : 'warning' }} mb-0">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                @if($usage['percentage'] >= 100)
                                    Έχετε φτάσει το μηνιαίο όριο του πακέτου σας.
                                    <a href="{{ route('billing.plans') }}">Αναβαθμίστε το πακέτο σας</a> για να συνεχίσετε την επεξεργασία παραστατικών.
                                @else
                                    Πλησιάζετε το μηνιαίο όριο του πακέτου σας.
                                    Εξετάστε την <a href="{{ route('billing.plans') }}">αναβάθμιση</a> για να αποφύγετε διακοπή της υπηρεσίας.
                                @endif
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">Εγγραφείτε σε ένα πακέτο για να ξεκινήσει η καταμέτρηση της χρήσης.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ενέργειες χρέωσης</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('billing.plans') }}" class="btn btn-block btn-outline-primary">
                                <i class="fas fa-tags mr-1"></i> Προβολή πακέτων
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('billing.invoices') }}" class="btn btn-block btn-outline-info">
                                <i class="fas fa-file-invoice mr-1"></i> Ιστορικό πληρωμών
                            </a>
                        </div>
                        <div class="col-md-4">
                            @if($subscription)
                                <a href="{{ route('billing.portal') }}" class="btn btn-block btn-outline-secondary">
                                    <i class="fas fa-credit-card mr-1"></i> Αλλαγή τρόπου πληρωμής
                                </a>
                            @else
                                <button class="btn btn-block btn-outline-secondary" disabled>
                                    <i class="fas fa-credit-card mr-1"></i> Αλλαγή τρόπου πληρωμής
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
