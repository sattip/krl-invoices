@extends('emails.layouts.base')

@section('title', 'Καλώς ορίσατε στο InvoiceAI')

@section('content')
    <div class="greeting">Καλώς ορίσατε στο InvoiceAI!</div>
    
    <p>Σας ευχαριστούμε για την εγγραφή σας! Ο λογαριασμός σας είναι πλέον ενεργός και έτοιμος για χρήση.</p>
    
    <div class="credentials-box">
        <h4>Στοιχεία συνδρομής</h4>
        <table class="details-table">
            <tr>
                <th>Πακέτο</th>
                <td>{{ $plan->name }}</td>
            </tr>
            <tr>
                <th>Τιμή</th>
                <td>{{ $plan->formatted_price }}/μήνα</td>
            </tr>
            <tr>
                <th>Όριο τιμολογίων</th>
                <td>{{ $plan->invoice_limit == -1 ? 'Απεριόριστα' : number_el($plan->invoice_limit) }} τιμολόγια/μήνα</td>
            </tr>
            <tr>
                <th>Κατάσταση</th>
                <td><span style="color: #10b981; font-weight: 600;">Ενεργή</span></td>
            </tr>
        </table>
    </div>
    
    <div class="text-center mb-6">
        <a href="{{ url('/dashboard') }}" class="button">Μετάβαση στον πίνακα ελέγχου</a>
    </div>
    
    <div class="info-box success">
        <h4>Ξεκινήστε</h4>
        <p>Ανεβάστε το πρώτο σας τιμολόγιο για να δείτε την αυτόματη εξαγωγή δεδομένων με τεχνητή νοημοσύνη.</p>
    </div>
    
    <p class="text-muted text-small">Για οποιαδήποτε απορία, μη διστάσετε να επικοινωνήσετε μαζί μας στο <a href="mailto:support@obstechnologies.com">support@obstechnologies.com</a>.</p>
@endsection
