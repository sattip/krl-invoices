@extends('adminlte::page')

@section('title', 'Subscription Plans')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Choose Your Plan</h1>
        <a href="{{ route('billing.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Billing
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

    @if(!$company)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>No Company Assigned</strong> - You must be assigned to a company before you can subscribe to a plan.
            Please contact an administrator.
        </div>
    @endif

    <div class="row">
        @foreach($plans as $plan)
            <div class="col-md-4">
                <div class="card {{ $currentPlan && $currentPlan->id === $plan->id ? 'card-primary' : '' }}">
                    <div class="card-header text-center">
                        <h3 class="card-title mb-0">{{ $plan->name }}</h3>
                        @if($currentPlan && $currentPlan->id === $plan->id)
                            <span class="badge badge-light ml-2">Current Plan</span>
                        @endif
                    </div>
                    <div class="card-body text-center">
                        <div class="display-4 mb-3">
                            {{ $plan->formatted_price }}
                            <small class="text-muted">/mo</small>
                        </div>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2">
                                <strong>{{ number_format($plan->invoice_limit) }}</strong> invoices/month
                            </li>
                            @if($plan->features)
                                @foreach($plan->features as $feature)
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success mr-1"></i>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            @else
                                <li class="mb-2">
                                    <i class="fas fa-check text-success mr-1"></i>
                                    AI-powered extraction
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success mr-1"></i>
                                    API access
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success mr-1"></i>
                                    Email support
                                </li>
                                @if($plan->isProfessional() || $plan->isEnterprise())
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success mr-1"></i>
                                        Priority support
                                    </li>
                                @endif
                                @if($plan->isEnterprise())
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success mr-1"></i>
                                        Dedicated account manager
                                    </li>
                                @endif
                            @endif
                        </ul>

                        @if(!$currentPlan)
                            <!-- No subscription - Subscribe -->
                            @if($company)
                                <form action="{{ route('billing.subscribe') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        Subscribe
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary btn-block" disabled title="Company required">
                                    Subscribe
                                </button>
                            @endif
                        @elseif($currentPlan->id === $plan->id)
                            <!-- Current plan -->
                            <button class="btn btn-secondary btn-block" disabled>
                                Current Plan
                            </button>
                        @elseif($plan->price > $currentPlan->price)
                            <!-- Upgrade -->
                            <form action="{{ route('billing.upgrade') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit" class="btn btn-success btn-block"
                                        onclick="return confirm('Upgrade to {{ $plan->name }}? You will be charged a prorated amount.')">
                                    <i class="fas fa-arrow-up mr-1"></i> Upgrade
                                </button>
                            </form>
                        @else
                            <!-- Downgrade -->
                            <form action="{{ route('billing.downgrade') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit" class="btn btn-warning btn-block"
                                        onclick="return confirm('Downgrade to {{ $plan->name }}? This will take effect at the end of your billing period.')">
                                    <i class="fas fa-arrow-down mr-1"></i> Downgrade
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
                <h5>Plan Change Information</h5>
                <ul class="mb-0">
                    <li><strong>Upgrades</strong> take effect immediately with prorated charges</li>
                    <li><strong>Downgrades</strong> take effect at the end of your current billing period</li>
                    <li>You can change plans at any time</li>
                </ul>
            </div>
        </div>
    @endif
@stop
