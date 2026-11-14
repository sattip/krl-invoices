@extends('emails.layouts.base')

@section('title', 'Η πληρωμή σας ελήφθη')

@section('content')
    <div class="greeting">Η πληρωμή σας ελήφθη</div>
    
    <p>Σας ευχαριστούμε! Η πληρωμή σας ολοκληρώθηκε επιτυχώς.</p>
    
    <div class="credentials-box">
        <h4>Στοιχεία πληρωμής</h4>
        <table class="details-table">
            <tr>
                <th>Ποσό</th>
                <td><strong>{{ $amount }}</strong></td>
            </tr>
            <tr>
                <th>Πακέτο</th>
                <td>{{ $planName }}</td>
            </tr>
            <tr>
                <th>Ημερομηνία</th>
                <td>{{ date_el($date) }}</td>
            </tr>
            <tr>
                <th>Αρ. παραστατικού</th>
                <td>{{ $invoiceNumber }}</td>
            </tr>
        </table>
    </div>
    
    <div class="text-center mb-6">
        <a href="{{ url('/billing') }}" class="button">Ιστορικό χρεώσεων</a>
    </div>
    
    <p class="text-muted text-small">Μπορείτε να δείτε τις χρεώσεις σας στη σελίδα χρεώσεων του λογαριασμού σας.</p>
@endsection
