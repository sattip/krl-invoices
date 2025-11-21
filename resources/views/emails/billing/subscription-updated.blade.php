@extends('emails.layouts.base')

@section('title', 'Subscription Updated')

@section('content')
    <div class="greeting">Subscription Updated</div>
    
    <p>Your subscription has been successfully updated.</p>
    
    <div class="credentials-box">
        <h4>New Subscription Details</h4>
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
                <th>Effective Date</th>
                <td>{{ $effectiveDate }}</td>
            </tr>
        </table>
    </div>
    
    <div class="text-center mb-6">
        <a href="{{ url('/billing') }}" class="button">View Billing Details</a>
    </div>
    
    <p class="text-muted text-small">If you didn't make this change or have any questions, please contact our support team immediately.</p>
@endsection
