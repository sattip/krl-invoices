@extends('adminlte::page')

@section('title', 'Stripe Settings')

@section('content_header')
    <h1>Stripe Settings</h1>
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
                        Connection Status
                    </h3>
                </div>
                <div class="card-body">
                    @if($settings->isConfigured() && $connectionStatus && $connectionStatus['success'])
                        <div class="d-flex align-items-center">
                            <span class="badge badge-success p-2 mr-3">
                                <i class="fas fa-check"></i> Connected
                            </span>
                            <div>
                                <strong>{{ $connectionStatus['business_name'] ?? 'Stripe Account' }}</strong>
                                @if(isset($connectionStatus['account_id']))
                                    <br><small class="text-muted">{{ $connectionStatus['account_id'] }}</small>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="d-flex align-items-center">
                            <span class="badge badge-danger p-2 mr-3">
                                <i class="fas fa-times"></i> Not Connected
                            </span>
                            <span class="text-muted">Enter your Stripe API credentials below to connect.</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Credentials Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API Credentials</h3>
                </div>
                <form action="{{ route('admin.stripe.save-credentials') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="stripe_publishable_key">Publishable Key</label>
                            <input type="text" class="form-control @error('stripe_publishable_key') is-invalid @enderror"
                                   id="stripe_publishable_key" name="stripe_publishable_key"
                                   value="{{ old('stripe_publishable_key', $settings->stripe_publishable_key) }}"
                                   placeholder="pk_live_...">
                            @error('stripe_publishable_key')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Starts with pk_live_ or pk_test_</small>
                        </div>

                        <div class="form-group">
                            <label for="stripe_secret_key">Secret Key</label>
                            <input type="password" class="form-control @error('stripe_secret_key') is-invalid @enderror"
                                   id="stripe_secret_key" name="stripe_secret_key"
                                   placeholder="sk_live_...">
                            @error('stripe_secret_key')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Starts with sk_live_ or sk_test_</small>
                        </div>

                        <div class="form-group">
                            <label for="stripe_webhook_secret">Webhook Secret (Optional)</label>
                            <input type="password" class="form-control @error('stripe_webhook_secret') is-invalid @enderror"
                                   id="stripe_webhook_secret" name="stripe_webhook_secret"
                                   placeholder="whsec_...">
                            @error('stripe_webhook_secret')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Required for handling webhooks (starts with whsec_)</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Save Credentials
                        </button>
                        @if($settings->isConfigured())
                            <form action="{{ route('admin.stripe.disconnect') }}" method="POST" class="d-inline ml-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to disconnect Stripe?')">
                                    <i class="fas fa-unlink mr-1"></i> Disconnect
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
                        <h3 class="card-title">Webhook Configuration</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">Configure this webhook URL in your Stripe Dashboard:</p>
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
                            Events to subscribe to: customer.subscription.created, customer.subscription.updated,
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
                    <h3 class="card-title">Setup Guide</h3>
                </div>
                <div class="card-body">
                    <ol class="pl-3">
                        <li class="mb-2">Go to <a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard</a></li>
                        <li class="mb-2">Copy your API keys (use Test keys for testing)</li>
                        <li class="mb-2">Paste the keys in the form and save</li>
                        <li class="mb-2">Set up the webhook endpoint</li>
                        <li class="mb-2">Configure your plans with Stripe Price IDs</li>
                    </ol>
                </div>
            </div>

            <!-- Quick Actions -->
            @if($settings->isConfigured())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.stripe.plans') }}" class="btn btn-block btn-outline-primary">
                            <i class="fas fa-tags mr-1"></i> Manage Plans
                        </a>
                        <form action="{{ route('admin.stripe.test') }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-block btn-outline-info">
                                <i class="fas fa-vial mr-1"></i> Test Connection
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
    alert('Copied to clipboard!');
}
</script>
@stop
