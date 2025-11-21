@extends('emails.layouts.base')

@section('title', 'Payment Failed')

@section('content')
    <div class="greeting">Payment Failed</div>
    
    <p>We were unable to process your payment for your InvoiceAI subscription.</p>
    
    <div class="info-box danger">
        <h4>Action Required</h4>
        <p>Please update your payment method to avoid service interruption. Your subscription will remain active for {{ config('subscription.grace_period_days', 3) }} more days.</p>
    </div>
    
    <div class="credentials-box">
        <h4>Payment Details</h4>
        <table class="details-table">
            <tr>
                <th>Amount Due</th>
                <td><strong>{{ $amount }}</strong></td>
            </tr>
            <tr>
                <th>Plan</th>
                <td>{{ $planName }}</td>
            </tr>
            <tr>
                <th>Attempt Date</th>
                <td>{{ $date }}</td>
            </tr>
        </table>
    </div>
    
    <div class="text-center mb-6">
        <a href="{{ url('/billing') }}" class="button button-danger">Update Payment Method</a>
    </div>
    
    <p class="text-muted text-small">Common reasons for payment failure include expired cards, insufficient funds, or outdated billing information.</p>
    
    <p class="text-muted text-small">If you believe this is an error or need assistance, please contact our support team.</p>
@endsection
