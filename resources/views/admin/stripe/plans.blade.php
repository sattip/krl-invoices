@extends('adminlte::page')

@section('title', 'Manage Plans')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Manage Subscription Plans</h1>
        <a href="{{ route('admin.stripe.setup') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Settings
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

    <!-- Sync with Stripe -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Stripe Sync</h3>
        </div>
        <div class="card-body">
            <p>If you haven't created products in Stripe yet, you can automatically sync your plans:</p>
            <form action="{{ route('admin.stripe.sync-plans') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync mr-1"></i> Sync Plans to Stripe
                </button>
            </form>
            <small class="d-block mt-2 text-muted">
                This will create products and prices in Stripe for plans that don't have a Stripe Price ID.
            </small>
        </div>
    </div>

    <!-- Plans Table -->
    <form action="{{ route('admin.stripe.update-plans') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Subscription Plans</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Price (USD/mo)</th>
                            <th>Invoice Limit</th>
                            <th>Stripe Price ID</th>
                            <th>Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $plan)
                            <tr>
                                <td>
                                    <input type="hidden" name="plans[{{ $loop->index }}][id]" value="{{ $plan->id }}">
                                    <strong>{{ $plan->name }}</strong>
                                    <br><small class="text-muted">{{ $plan->slug }}</small>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm" style="width: 120px;">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">€</span>
                                        </div>
                                        <input type="number" step="0.01" min="0"
                                               class="form-control"
                                               name="plans[{{ $loop->index }}][price]"
                                               value="{{ $plan->price }}">
                                    </div>
                                </td>
                                <td>
                                    <input type="number" min="1"
                                           class="form-control form-control-sm"
                                           style="width: 100px;"
                                           name="plans[{{ $loop->index }}][invoice_limit]"
                                           value="{{ $plan->invoice_limit }}">
                                </td>
                                <td>
                                    <input type="text"
                                           class="form-control form-control-sm"
                                           name="plans[{{ $loop->index }}][stripe_price_id]"
                                           value="{{ $plan->stripe_price_id }}"
                                           placeholder="price_..."
                                           style="width: 250px;">
                                    @if($plan->stripe_price_id)
                                        <small class="text-success">
                                            <i class="fas fa-check"></i> Synced
                                        </small>
                                    @else
                                        <small class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Not synced
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="plan-active-{{ $plan->id }}"
                                               name="plans[{{ $loop->index }}][is_active]"
                                               value="1"
                                               {{ $plan->is_active ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="plan-active-{{ $plan->id }}"></label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </div>
    </form>

    <!-- Plan Features Info -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Plan Features</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($plans as $plan)
                    <div class="col-md-4">
                        <div class="info-box {{ $plan->is_active ? 'bg-light' : 'bg-secondary' }}">
                            <span class="info-box-icon {{ $plan->isEnterprise() ? 'bg-purple' : ($plan->isProfessional() ? 'bg-primary' : 'bg-info') }}">
                                <i class="fas fa-{{ $plan->isEnterprise() ? 'building' : ($plan->isProfessional() ? 'briefcase' : 'rocket') }}"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ $plan->name }}</span>
                                <span class="info-box-number">{{ $plan->formatted_price }}/mo</span>
                                <span class="progress-description">
                                    {{ number_format($plan->invoice_limit) }} invoices/month
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop
