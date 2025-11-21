@extends('emails.layouts.base')

@section('title', 'Grace Period Expiring Soon')

@section('content')
    <div class="greeting">Grace Period Expiring Soon</div>
    
    <p>Your complimentary access to InvoiceAI is ending soon.</p>
    
    <div class="info-box danger">
        <h4>Expires in {{ $daysRemaining }} {{ $daysRemaining == 1 ? 'day' : 'days' }}</h4>
        <p>Your grace period ends on <strong>{{ $expirationDate }}</strong>. After this date, you won't be able to process new invoices until you subscribe to a plan.</p>
    </div>
    
    <div class="credentials-box">
        <h4>Your Current Plan</h4>
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
                <th>Grace Period Ends</th>
                <td style="color: #ef4444; font-weight: 600;">{{ $expirationDate }}</td>
            </tr>
        </table>
    </div>
    
    <p>To continue using InvoiceAI without interruption, please subscribe to a plan.</p>
    
    <div class="text-center mb-6">
        <a href="{{ url('/billing/plans') }}" class="button">View Plans & Subscribe</a>
    </div>
    
    <p class="text-muted text-small">If you have any questions about our plans or need assistance, please contact our support team.</p>
@endsection
