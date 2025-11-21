@extends('emails.layouts.base')

@section('title', 'Welcome to InvoiceAI')

@section('content')
    <div class="greeting">Welcome to InvoiceAI, {{ $user->name }}!</div>
    
    <p>Thank you for joining InvoiceAI! Your account has been successfully created and you're ready to start extracting data from your invoices.</p>
    
    <div class="info-box success">
        <h4>What's Next?</h4>
        <p>Upload your first invoice and watch our AI extract all the important data automatically!</p>
    </div>
    
    <div class="credentials-box">
        <h4>Your Account</h4>
        <table class="details-table">
            <tr>
                <th>Email</th>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <th>Company</th>
                <td>{{ $company->name }}</td>
            </tr>
            @if($plan)
            <tr>
                <th>Plan</th>
                <td>{{ $plan->name }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <div class="text-center mb-6">
        <a href="{{ url('/dashboard') }}" class="button">Go to Dashboard</a>
        <a href="{{ url('/invoices/create') }}" class="button button-success" style="margin-left: 8px;">Upload Invoice</a>
    </div>
    
    <p class="text-muted text-small">Need help getting started? Check out our documentation or contact our support team.</p>
@endsection
