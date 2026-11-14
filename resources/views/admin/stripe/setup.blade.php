@extends('adminlte::page')

@section('title', 'Ρυθμίσεις Stripe')

@section('content_header')
    <h1>Ρυθμίσεις Stripe</h1>
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

    <div class="row">
        <div class="col-md-8">
            <!-- Connection Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fab fa-stripe mr-2"></i>
                        Κατάσταση σύνδεσης
                    </h3>
                </div>
                <div class="card-body">
                    @if($settings->isConfigured() && $connectionStatus && $connectionStatus['success'])
                        <div class="d-flex align-items-center">
                            <span class="badge badge-success p-2 mr-3">
                                <i class="fas fa-check"></i> Συνδεδεμένο
                            </span>
                            <div>
                                <strong>{{ $connectionStatus['business_name'] ?? 'Λογαριασμός Stripe' }}</strong>
                                @if(isset($connectionStatus['account_id']))
                                    <br><small class="text-muted">{{ $connectionStatus['account_id'] }}</small>
                                @endif
                                @if($settings->livemode)
                                    <br><span class="badge badge-{{ $settings->livemode === 'live' ? 'success' : 'warning' }}">
                                        {{ $settings->livemode === 'live' ? 'Παραγωγική λειτουργία (live)' : 'Δοκιμαστική λειτουργία (test)' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="d-flex align-items-center">
                            <span class="badge badge-danger p-2 mr-3">
                                <i class="fas fa-times"></i> Μη συνδεδεμένο
                            </span>
                            <span class="text-muted">Συνδέστε τον λογαριασμό σας Stripe για να αρχίσετε να δέχεστε πληρωμές.</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Connect with OAuth -->
            @if(!$settings->isConfigured())
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt mr-2"></i>
                            Γρήγορη σύνδεση
                        </h3>
                    </div>
                    <div class="card-body text-center py-4">
                        <p class="mb-3">Συνδέστε τον λογαριασμό σας Stripe με ένα κλικ:</p>
                        <a href="{{ route('admin.stripe.connect') }}" class="btn btn-lg btn-primary">
                            <i class="fab fa-stripe mr-2"></i>
                            Σύνδεση με Stripe
                        </a>
                        <p class="text-muted mt-3 mb-0">
                            <small>Θα μεταφερθείτε στο Stripe για να εγκρίνετε τη σύνδεση.</small>
                        </p>
                    </div>
                </div>

                <div class="text-center my-3">
                    <span class="text-muted">— ή εισαγάγετε χειροκίνητα τα κλειδιά API —</span>
                </div>
            @endif

            <!-- Credentials Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Διαπιστευτήρια API</h3>
                </div>
                <form action="{{ route('admin.stripe.save-credentials') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="stripe_publishable_key">Δημοσιεύσιμο κλειδί (Publishable Key)</label>
                            <input type="text" class="form-control @error('stripe_publishable_key') is-invalid @enderror"
                                   id="stripe_publishable_key" name="stripe_publishable_key"
                                   value="{{ old('stripe_publishable_key', $settings->stripe_publishable_key) }}"
                                   placeholder="pk_live_...">
                            @error('stripe_publishable_key')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Ξεκινά με pk_live_ ή pk_test_</small>
                        </div>

                        <div class="form-group">
                            <label for="stripe_secret_key">Μυστικό κλειδί (Secret Key)</label>
                            <input type="password" class="form-control @error('stripe_secret_key') is-invalid @enderror"
                                   id="stripe_secret_key" name="stripe_secret_key"
                                   placeholder="sk_live_...">
                            @error('stripe_secret_key')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Ξεκινά με sk_live_ ή sk_test_</small>
                        </div>

                        <div class="form-group">
                            <label for="stripe_webhook_secret">Webhook Secret (προαιρετικό)</label>
                            <input type="password" class="form-control @error('stripe_webhook_secret') is-invalid @enderror"
                                   id="stripe_webhook_secret" name="stripe_webhook_secret"
                                   placeholder="whsec_...">
                            @error('stripe_webhook_secret')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Απαιτείται για τη διαχείριση των webhooks (ξεκινά με whsec_)</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Αποθήκευση διαπιστευτηρίων
                        </button>
                        @if($settings->isConfigured())
                            <form action="{{ route('admin.stripe.disconnect') }}" method="POST" class="d-inline ml-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Είστε βέβαιοι ότι θέλετε να αποσυνδέσετε το Stripe;')">
                                    <i class="fas fa-unlink mr-1"></i> Αποσύνδεση
                                </button>
                            </form>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Webhook URL -->
            @if($settings->isConfigured())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ρύθμιση webhook</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">Καταχωρίστε την παρακάτω διεύθυνση webhook στο Stripe Dashboard:</p>
                        <div class="input-group">
                            <input type="text" class="form-control" readonly
                                   value="{{ url('/webhooks/stripe') }}" id="webhook-url">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="copyToClipboard('webhook-url')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted mt-2">
                            Συμβάντα προς εγγραφή: customer.subscription.created, customer.subscription.updated,
                            customer.subscription.deleted, invoice.payment_succeeded, invoice.payment_failed
                        </small>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Help Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Οδηγός ρύθμισης</h3>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Επιλογή 1: Γρήγορη σύνδεση</h6>
                    <p class="text-muted small">Πατήστε «Σύνδεση με Stripe» για αυτόματη ρύθμιση του λογαριασμού σας.</p>
                    
                    <hr>
                    
                    <h6 class="font-weight-bold">Επιλογή 2: Χειροκίνητη ρύθμιση</h6>
                    <ol class="pl-3 small">
                        <li class="mb-2">Μεταβείτε στο <a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard</a></li>
                        <li class="mb-2">Αντιγράψτε τα κλειδιά API</li>
                        <li class="mb-2">Επικολλήστε τα κλειδιά και αποθηκεύστε</li>
                        <li class="mb-2">Ρυθμίστε το webhook endpoint</li>
                        <li class="mb-2">Ρυθμίστε τα πακέτα σας</li>
                    </ol>
                </div>
            </div>

            <!-- Quick Actions -->
            @if($settings->isConfigured())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Γρήγορες ενέργειες</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.stripe.plans') }}" class="btn btn-block btn-outline-primary">
                            <i class="fas fa-tags mr-1"></i> Διαχείριση πακέτων
                        </a>
                        <form action="{{ route('admin.stripe.test') }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-block btn-outline-info">
                                <i class="fas fa-vial mr-1"></i> Έλεγχος σύνδεσης
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
<script>
function copyToClipboard(elementId) {
    var element = document.getElementById(elementId);
    element.select();
    document.execCommand('copy');
    alert('Αντιγράφηκε στο πρόχειρο.');
}
</script>
@stop
