@extends('emails.layouts.base')

@section('title', 'Payment Received')

@section('content')
    <div class="greeting">Payment Received</div>
    
    <p>Thank you! Your payment has been successfully processed.</p>
    
    <div class="credentials-box">
        <h4>Payment Details</h4>
        <table class="details-table">
            <tr>
                <th>Amount</th>
                <td><strong>{{ $amount }}</strong></td>
            </tr>
            <tr>
                <th>Plan</th>
                <td>{{ $planName }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ $date }}</td>
            </tr>
            <tr>
                <th>Invoice #</th>
                <td>{{ $invoiceNumber }}</td>
            </tr>
        </table>
    </div>
    
    <div class="text-center mb-6">
        <a href="{{ url('/billing') }}" class="button">View Billing History</a>
    </div>
    
    <p class="text-muted text-small">A receipt has been sent to your email address. You can also download it from your billing page.</p>
@endsection
