@extends('emails.layouts.base')

@section('title', 'Η περίοδος δωρεάν πρόσβασης λήγει σύντομα')

@section('content')
    <div class="greeting">Η περίοδος δωρεάν πρόσβασης λήγει σύντομα</div>
    
    <p>Η δωρεάν πρόσβασή σας στο InvoiceAI ολοκληρώνεται σύντομα.</p>
    
    <div class="info-box danger">
        <h4>Λήγει σε {{ $daysRemaining }} {{ $daysRemaining == 1 ? 'ημέρα' : 'ημέρες' }}</h4>
        <p>Η περίοδος δωρεάν πρόσβασης λήγει στις <strong>{{ date_el($expirationDate) }}</strong>. Μετά την ημερομηνία αυτή δεν θα μπορείτε να επεξεργάζεστε νέα τιμολόγια, έως ότου εγγραφείτε σε κάποιο πακέτο.</p>
    </div>
    
    <div class="credentials-box">
        <h4>Το τρέχον πακέτο σας</h4>
        <table class="details-table">
            <tr>
                <th>Πακέτο</th>
                <td>{{ $plan->name }}</td>
            </tr>
            <tr>
                <th>Όριο τιμολογίων</th>
                <td>{{ $plan->invoice_limit == -1 ? 'Απεριόριστα' : number_el($plan->invoice_limit) }} τιμολόγια/μήνα</td>
            </tr>
            <tr>
                <th>Λήξη δωρεάν πρόσβασης</th>
                <td style="color: #ef4444; font-weight: 600;">{{ date_el($expirationDate) }}</td>
            </tr>
        </table>
    </div>
    
    <p>Για να συνεχίσετε να χρησιμοποιείτε το InvoiceAI χωρίς διακοπή, παρακαλούμε εγγραφείτε σε κάποιο πακέτο.</p>
    
    <div class="text-center mb-6">
        <a href="{{ url('/billing/plans') }}" class="button">Δείτε τα πακέτα και εγγραφείτε</a>
    </div>
    
    <p class="text-muted text-small">Για οποιαδήποτε απορία σχετικά με τα πακέτα μας, επικοινωνήστε μαζί μας στο <a href="mailto:support@obstechnologies.com">support@obstechnologies.com</a>.</p>
@endsection
