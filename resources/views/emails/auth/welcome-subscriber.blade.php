@extends('emails.layouts.base')

@section('title', 'Καλώς ορίσατε στο InvoiceAI')

@section('content')
    <div class="greeting">Καλώς ορίσατε στο InvoiceAI, {{ $user->name }}!</div>
    
    <p>Σας ευχαριστούμε που επιλέξατε το InvoiceAI. Ο λογαριασμός σας δημιουργήθηκε επιτυχώς και μπορείτε πλέον να ξεκινήσετε την εξαγωγή δεδομένων από τα τιμολόγιά σας.</p>
    
    <div class="info-box success">
        <h4>Επόμενο βήμα</h4>
        <p>Ανεβάστε το πρώτο σας τιμολόγιο και το InvoiceAI θα εξαγάγει αυτόματα τα βασικά του στοιχεία. Σας προτείνουμε να ελέγχετε πάντα τα εξαγόμενα δεδομένα πριν από τη χρήση τους.</p>
    </div>
    
    <div class="credentials-box">
        <h4>Ο λογαριασμός σας</h4>
        <table class="details-table">
            <tr>
                <th>Email</th>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <th>Εταιρεία</th>
                <td>{{ $company->name }}</td>
            </tr>
            @if($plan)
            <tr>
                <th>Πακέτο</th>
                <td>{{ $plan->name }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <div class="text-center mb-6">
        <a href="{{ url('/dashboard') }}" class="button">Μετάβαση στον πίνακα ελέγχου</a>
        <a href="{{ url('/invoices/create') }}" class="button button-success" style="margin-left: 8px;">Ανέβασμα τιμολογίου</a>
    </div>
    
    <p class="text-muted text-small">Χρειάζεστε βοήθεια για να ξεκινήσετε; Επικοινωνήστε μαζί μας στο <a href="mailto:support@obstechnologies.com">support@obstechnologies.com</a>.</p>
@endsection
