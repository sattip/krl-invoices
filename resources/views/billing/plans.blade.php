@extends('adminlte::page')

@section('title', 'Πακέτα συνδρομής')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Επιλέξτε πακέτο</h1>
        <a href="{{ route('billing.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Επιστροφή στη χρέωση
        </a>
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

    @if(session('info'))
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('info') }}
        </div>
    @endif

    @include('partials.trial-status', ['company' => $company])

    @if(!$company)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Δεν έχει οριστεί εταιρεία</strong> — Για να εγγραφείτε σε πακέτο, ο λογαριασμός σας πρέπει να είναι συνδεδεμένος με εταιρεία.
            Παρακαλούμε επικοινωνήστε με τον διαχειριστή.
        </div>
    @endif

    <div class="row">
        @foreach($plans as $plan)
            <div class="col-md-4">
                <div class="card {{ $currentPlan && $currentPlan->id === $plan->id ? 'card-primary' : '' }}">
                    <div class="card-header text-center">
                        <h3 class="card-title mb-0">{{ $plan->name }}</h3>
                        @if($currentPlan && $currentPlan->id === $plan->id)
                            <span class="badge badge-light ml-2">Τρέχον πακέτο</span>
                        @endif
                    </div>
                    <div class="card-body text-center">
                        <div class="display-4 mb-3">
                            {{ $plan->formatted_price }}
                            <small class="text-muted">/ μήνα</small>
                            @include('partials.vat-note')
                        </div>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2">
                                <strong>{{ number_el($plan->invoice_limit) }}</strong> παραστατικά / μήνα
                            </li>
                            {{-- Fixed, verified feature list (same as the public pricing). The DB "features"
                                 column holds legacy English marketing items (priority support, account manager,
                                 faster processing, custom integrations) that are not implemented/verified. --}}
                            @foreach(['Εξαγωγή δεδομένων με τεχνητή νοημοσύνη', 'Πρόσβαση στο REST API', 'Υποστήριξη μέσω email'] as $feature)
                                <li class="mb-2">
                                    <i class="fas fa-check text-success mr-1"></i>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>

                        @if(!$currentPlan)
                            <!-- No subscription - Subscribe -->
                            @if($company)
                                <form action="{{ route('billing.subscribe') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        Εγγραφή
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary btn-block" disabled title="Απαιτείται εταιρεία">
                                    Εγγραφή
                                </button>
                            @endif
                        @elseif($currentPlan->id === $plan->id)
                            <!-- Current plan -->
                            <button class="btn btn-secondary btn-block" disabled>
                                Τρέχον πακέτο
                            </button>
                        @elseif($plan->price > $currentPlan->price)
                            <!-- Upgrade -->
                            <form action="{{ route('billing.upgrade') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit" class="btn btn-success btn-block"
                                        onclick="return confirm('Αναβάθμιση σε {{ $plan->name }}; Θα χρεωθείτε αναλογικά για την υπόλοιπη περίοδο.')">
                                    <i class="fas fa-arrow-up mr-1"></i> Αναβάθμιση
                                </button>
                            </form>
                        @else
                            <!-- Downgrade -->
                            <form action="{{ route('billing.downgrade') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit" class="btn btn-warning btn-block"
                                        onclick="return confirm('Υποβάθμιση σε {{ $plan->name }}; Η αλλαγή και τα νέα όρια ισχύουν άμεσα. Η νέα τιμή χρεώνεται από την επόμενη περίοδο χρέωσης, χωρίς επιστροφή για την τρέχουσα.')">
                                    <i class="fas fa-arrow-down mr-1"></i> Υποβάθμιση
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($currentPlan)
        <div class="card">
            <div class="card-body">
                <h5>Πληροφορίες για αλλαγή πακέτου</h5>
                <ul class="mb-0">
                    <li>Οι <strong>αναβαθμίσεις</strong> ισχύουν άμεσα, με αναλογική χρέωση</li>
                    <li>Οι <strong>υποβαθμίσεις</strong> ισχύουν άμεσα (και για τα όρια του πακέτου)· η νέα τιμή χρεώνεται από την επόμενη περίοδο χρέωσης, χωρίς επιστροφή για την τρέχουσα</li>
                    <li>Μπορείτε να αλλάξετε πακέτο οποιαδήποτε στιγμή</li>
                </ul>
            </div>
        </div>
    @endif
@stop
