@extends('emails.layouts.base')

@section('title', 'Subscription Canceled')

@section('content')
    <div class="greeting">Subscription Canceled</div>
    
    <p>Your InvoiceAI subscription has been canceled.</p>
    
    <div class="info-box warning">
        <h4>Access Until End of Period</h4>
        <p>You'll continue to have access to your account until <strong>{{ $endDate }}</strong>. After this date, you won't be able to process new invoices.</p>
    </div>
    
    <div class="credentials-box">
        <h4>Canceled Subscription</h4>
        <table class="details-table">
            <tr>
                <th>Plan</th>
                <td>{{ $plan->name }}</td>
            </tr>
            <tr>
                <th>Access Ends</th>
                <td>{{ $endDate }}</td>
            </tr>
        </table>
    </div>
    
    <p>We're sorry to see you go! If you change your mind, you can resubscribe at any time.</p>
    
    <div class="text-center mb-6">
        <a href="{{ url('/billing/plans') }}" class="button">Resubscribe</a>
    </div>
    
    <p class="text-muted text-small">Your data will be retained for 30 days after cancellation. If you have any feedback about our service, we'd love to hear from you.</p>
@endsection
