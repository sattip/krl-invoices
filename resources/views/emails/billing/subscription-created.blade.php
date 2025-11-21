@extends('emails.layouts.base')

@section('title', 'Welcome to InvoiceAI')

@section('content')
    <div class="greeting">Welcome to InvoiceAI!</div>
    
    <p>Thank you for subscribing! Your account is now active and ready to use.</p>
    
    <div class="credentials-box">
        <h4>Your Subscription Details</h4>
        <table class="details-table">
            <tr>
                <th>Plan</th>
                <td>{{ $plan->name }}</td>
            </tr>
            <tr>
                <th>Price</th>
                <td>{{ $plan->formatted_price }}/month</td>
            </tr>
            <tr>
                <th>Invoice Limit</th>
                <td>{{ $plan->invoice_limit == -1 ? 'Unlimited' : number_format($plan->invoice_limit) }} invoices/month</td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span style="color: #10b981; font-weight: 600;">Active</span></td>
            </tr>
        </table>
    </div>
    
    <div class="text-center mb-6">
        <a href="{{ url('/dashboard') }}" class="button">Go to Dashboard</a>
    </div>
    
    <div class="info-box success">
        <h4>Get Started</h4>
        <p>Upload your first invoice to see AI-powered data extraction in action!</p>
    </div>
    
    <p class="text-muted text-small">If you have any questions, please don't hesitate to contact our support team.</p>
@endsection
