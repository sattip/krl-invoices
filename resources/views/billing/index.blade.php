@extends('adminlte::page')

@section('title', 'Billing & Usage')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Billing & Usage</h1>
        @if($subscription)
            <a href="{{ route('billing.portal') }}" class="btn btn-outline-primary">
                <i class="fas fa-external-link-alt mr-1"></i> Stripe Portal
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
                    <h3 class="card-title">Current Plan</h3>
                </div>
                <div class="card-body">
                    @if($plan)
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge badge-{{ $plan->isEnterprise() ? 'purple' : ($plan->isProfessional() ? 'primary' : 'info') }} p-2 mr-3">
                                {{ $plan->name }}
                            </span>
                            <div>
                                <strong>{{ $plan->formatted_price }}/month</strong>
                            </div>
                        </div>

                        @if($subscription)
                            <p class="mb-2">
                                <strong>Status:</strong>
                                @if($subscription->isActive())
                                    <span class="badge badge-success">Active</span>
                                @elseif($subscription->isCanceled())
                                    <span class="badge badge-warning">Canceling</span>
                                @elseif($subscription->isPastDue())
                                    <span class="badge badge-danger">Past Due</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($subscription->status) }}</span>
                                @endif
                            </p>

                            @if($subscription->isCanceled() && !$subscription->hasEnded())
                                <p class="text-warning mb-2">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Your subscription will end on {{ $subscription->current_period_end->format('M d, Y') }}
                                </p>
                                <form action="{{ route('billing.resume') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-undo mr-1"></i> Resume Subscription
                                    </button>
                                </form>
                            @else
                                <p class="mb-2">
                                    <strong>Next billing:</strong> {{ $subscription->current_period_end->format('M d, Y') }}
                                </p>
                            @endif
                        @endif

                        <hr>

                        <a href="{{ route('billing.plans') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-up mr-1"></i> Change Plan
                        </a>

                        @if($subscription && $subscription->isActive() && !$subscription->isCanceled())
                            <form action="{{ route('billing.cancel') }}" method="POST" class="d-inline ml-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to cancel your subscription?')">
                                    Cancel Subscription
                                </button>
                            </form>
                        @endif
                    @else
                        <p class="text-muted mb-3">You don't have an active subscription.</p>
                        <a href="{{ route('billing.plans') }}" class="btn btn-primary">
                            <i class="fas fa-rocket mr-1"></i> Subscribe Now
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Usage This Month -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Usage This Month</h3>
                </div>
                <div class="card-body">
                    @if($plan)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Invoices Used</span>
                            <strong>{{ $usage['used'] }} / {{ $usage['limit'] }}</strong>
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
                                {{ number_format($usage['percentage'], 0) }}%
                            </div>
                        </div>

                        <p class="mb-0">
                            <strong>{{ $usage['remaining'] }}</strong> invoices remaining this month
                        </p>

                        @if($usage['percentage'] >= 80)
                            <hr>
                            <div class="alert alert-{{ $usage['percentage'] >= 90 ? 'danger' : 'warning' }} mb-0">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                @if($usage['percentage'] >= 100)
                                    You've reached your monthly limit.
                                    <a href="{{ route('billing.plans') }}">Upgrade now</a> to continue processing invoices.
                                @else
                                    You're approaching your monthly limit.
                                    Consider <a href="{{ route('billing.plans') }}">upgrading</a> to avoid interruptions.
                                @endif
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">Subscribe to a plan to start tracking usage.</p>
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
                    <h3 class="card-title">Billing Actions</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('billing.plans') }}" class="btn btn-block btn-outline-primary">
                                <i class="fas fa-tags mr-1"></i> View Plans
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('billing.invoices') }}" class="btn btn-block btn-outline-info">
                                <i class="fas fa-file-invoice mr-1"></i> Payment History
                            </a>
                        </div>
                        <div class="col-md-4">
                            @if($subscription)
                                <a href="{{ route('billing.portal') }}" class="btn btn-block btn-outline-secondary">
                                    <i class="fas fa-credit-card mr-1"></i> Update Payment Method
                                </a>
                            @else
                                <button class="btn btn-block btn-outline-secondary" disabled>
                                    <i class="fas fa-credit-card mr-1"></i> Update Payment Method
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
