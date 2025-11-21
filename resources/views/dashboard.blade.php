@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    @php
        // Global scope automatically filters by company
        $totalInvoices = \App\Models\Invoice::count();
        $thisMonth = \App\Models\Invoice::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $totalAmount = \App\Models\Invoice::sum('grand_total');
        $recentInvoices = \App\Models\Invoice::orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        $company = current_company();

        // Subscription data
        $plan = $company ? $company->currentPlan() : null;
        $usage = $company ? [
            'used' => $company->invoicesUsedThisMonth(),
            'limit' => $company->invoiceLimit(),
            'remaining' => $company->invoicesRemainingThisMonth(),
            'percentage' => $company->usagePercentage(),
        ] : null;
    @endphp

    <!-- Welcome Box -->
    <div class="card card-primary card-outline">
        <div class="card-body">
            <h4>Welcome to InvoiceAI</h4>
            <p class="text-muted">
                @if($company)
                    Managing invoices for <strong>{{ $company->name }}</strong>
                @else
                    Extract structured data from invoices using AI-powered analysis
                @endif
            </p>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-upload mr-2"></i> Upload Invoices
            </a>
        </div>
    </div>

    <!-- Stats Boxes -->
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalInvoices }}</h3>
                    <p>Total Invoices</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <a href="{{ route('invoices.index') }}" class="small-box-footer">
                    View all <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $thisMonth }}</h3>
                    <p>This Month</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar"></i>
                </div>
                <a href="{{ route('invoices.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>${{ number_format($totalAmount, 0) }}</h3>
                    <p>Total Processed</p>
                </div>
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <a href="{{ route('invoices.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Usage Widget -->
    @if($company && $plan)
        <div class="row">
            <div class="col-12">
                <div class="card {{ $usage['percentage'] >= 90 ? 'card-danger' : ($usage['percentage'] >= 80 ? 'card-warning' : 'card-success') }} card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>
                            Monthly Usage - {{ $plan->name }} Plan
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('billing.index') }}" class="btn btn-tool">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Invoices Used</span>
                                    <strong>{{ $usage['used'] }} / {{ $usage['limit'] }}</strong>
                                </div>
                                <div class="progress" style="height: 25px;">
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
                            </div>
                            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                @if($usage['remaining'] > 0)
                                    <span class="text-muted">{{ $usage['remaining'] }} invoices remaining</span>
                                @else
                                    <span class="text-danger font-weight-bold">Limit reached</span>
                                @endif
                                <br>
                                <a href="{{ route('billing.plans') }}" class="btn btn-sm btn-outline-primary mt-2">
                                    @if($usage['percentage'] >= 80)
                                        <i class="fas fa-arrow-up mr-1"></i> Upgrade Plan
                                    @else
                                        View Plans
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($company && !$plan)
        <div class="row">
            <div class="col-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>No Active Subscription</strong> -
                    <a href="{{ route('billing.plans') }}">Subscribe to a plan</a> to start processing invoices.
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Recent Invoices -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Invoices</h3>
                </div>
                <div class="card-body p-0">
                    @if($recentInvoices->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No invoices yet.</p>
                            <a href="{{ route('invoices.create') }}" class="btn btn-link">Upload your first invoice</a>
                        </div>
                    @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Issuer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentInvoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_number ?? 'N/A' }}</td>
                                        <td>{{ $invoice->issuer_name }}</td>
                                        <td>{{ $invoice->currency }} {{ number_format($invoice->grand_total, 2) }}</td>
                                        <td>{{ $invoice->created_at->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                @if($recentInvoices->isNotEmpty())
                    <div class="card-footer text-center">
                        <a href="{{ route('invoices.index') }}">View All Invoices</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-upload mr-2"></i> Upload Invoice
                    </a>
                    <a href="{{ route('invoices.index') }}" class="btn btn-default btn-block mb-2">
                        <i class="fas fa-list mr-2"></i> View All Invoices
                    </a>
                    <a href="{{ route('api.tokens.index') }}" class="btn btn-default btn-block mb-2">
                        <i class="fas fa-key mr-2"></i> Manage API Tokens
                    </a>
                    <a href="{{ route('profile.edit') }}" class="btn btn-default btn-block">
                        <i class="fas fa-user mr-2"></i> Profile Settings
                    </a>
                </div>
            </div>

            <!-- API Info Card -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">API Access</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Integrate InvoiceAI with your applications using our REST API.</p>
                    <a href="{{ route('api.documentation') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-book mr-1"></i> View Documentation
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop
