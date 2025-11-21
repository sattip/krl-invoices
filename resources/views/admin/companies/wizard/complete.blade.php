@extends('adminlte::page')

@section('title', 'Company Created Successfully')

@section('content_header')
    <h1>Company Created Successfully</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Success Alert -->
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            The company has been created and a welcome email has been sent to the owner.
        </div>

        <!-- Company Details Card -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building mr-2"></i>Company Details</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">Company Name</th>
                        <td>{{ $company->name }}</td>
                    </tr>
                    @if($company->email)
                    <tr>
                        <th>Company Email</th>
                        <td>{{ $company->email }}</td>
                    </tr>
                    @endif
                    @if($company->phone)
                    <tr>
                        <th>Phone</th>
                        <td>{{ $company->phone }}</td>
                    </tr>
                    @endif
                    @if($company->vat_number)
                    <tr>
                        <th>VAT Number</th>
                        <td>{{ $company->vat_number }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Owner Credentials Card -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user mr-2"></i>Owner Account Credentials</h3>
            </div>
            <div class="card-body">
                @if($passwordWasGenerated)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Important:</strong> Please save these credentials. The password will not be shown again.
                </div>
                @endif
                
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">Name</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><code>{{ $user->email }}</code></td>
                    </tr>
                    <tr>
                        <th>Password</th>
                        <td>
                            <code id="password-display">{{ $password }}</code>
                            <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="copyPassword()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td><span class="badge badge-primary">Owner</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Subscription Details Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-credit-card mr-2"></i>Subscription Details</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">Plan</th>
                        <td>
                            <strong>{{ $plan->name }}</strong>
                            <span class="text-muted">
                                ({{ number_format($plan->price, 2) }}/mo)
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Invoice Limit</th>
                        <td>{{ $plan->invoice_limit == -1 ? 'Unlimited' : $plan->invoice_limit }} invoices/month</td>
                    </tr>
                    <tr>
                        <th>Grace Period Until</th>
                        <td>
                            <span class="badge badge-warning">
                                {{ \Carbon\Carbon::parse($gracePeriodEnd)->format('F j, Y') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Payment Required</th>
                        <td><span class="badge badge-success">No (Grace Period)</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-body">
                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-primary">
                    <i class="fas fa-eye mr-1"></i> View Company
                </a>
                <a href="{{ route('admin.companies.wizard.step1') }}" class="btn btn-success">
                    <i class="fas fa-plus mr-1"></i> Create Another Company
                </a>
                <a href="{{ route('admin.companies.index') }}" class="btn btn-default">
                    <i class="fas fa-list mr-1"></i> Back to Companies
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    function copyPassword() {
        var password = document.getElementById('password-display').textContent;
        navigator.clipboard.writeText(password).then(function() {
            toastr.success('Password copied to clipboard!');
        }, function() {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = password;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            toastr.success('Password copied to clipboard!');
        });
    }
</script>
@stop
