@extends('emails.layouts.base')

@section('title', 'Welcome to InvoiceAI')

@section('content')
    <div class="greeting">Welcome to InvoiceAI, {{ $user->name }}!</div>
    
    <p>Your company account for <strong>{{ $company->name }}</strong> has been created and is ready to use.</p>
    
    <div class="credentials-box">
        <h4>Your Login Credentials</h4>
        <table class="details-table">
            <tr>
                <th>Email</th>
                <td><code style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px;">{{ $user->email }}</code></td>
            </tr>
            <tr>
                <th>Password</th>
                <td><code style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px;">{{ $password }}</code></td>
            </tr>
        </table>
    </div>
    
    <div class="info-box warning">
        <h4>Important</h4>
        <p>For security reasons, please change your password after your first login.</p>
    </div>
    
    <div class="credentials-box">
        <h4>Your Plan Details</h4>
        <table class="details-table">
            <tr>
                <th>Plan</th>
                <td>{{ $plan->name }}</td>
            </tr>
            <tr>
                <th>Invoice Limit</th>
                <td>{{ $plan->invoice_limit == -1 ? 'Unlimited' : number_format($plan->invoice_limit) }} invoices/month</td>
            </tr>
            <tr>
                <th>Access Until</th>
                <td style="color: #f59e0b; font-weight: 600;">{{ \Carbon\Carbon::parse($gracePeriodEnd)->format('F j, Y') }}</td>
            </tr>
        </table>
    </div>
    
    <p>You have been granted complimentary access until the date above. No payment is required during this period.</p>
    
    <div class="text-center mb-6">
        <a href="{{ url('/login') }}" class="button">Login to Your Account</a>
    </div>
    
    <p class="text-muted text-small">If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
@endsection
