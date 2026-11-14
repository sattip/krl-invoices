@extends('emails.layouts.base')

@section('title', 'Ακύρωση συνδρομής')

@section('content')
    <div class="greeting">Η συνδρομή σας ακυρώθηκε</div>
    
    <p>Η συνδρομή σας στο InvoiceAI ακυρώθηκε.</p>
    
    <div class="info-box warning">
        <h4>Πρόσβαση έως το τέλος της περιόδου</h4>
        <p>Θα έχετε πρόσβαση στον λογαριασμό σας έως τις <strong>{{ date_el($endDate) }}</strong>. Μετά την ημερομηνία αυτή δεν θα μπορείτε να επεξεργάζεστε νέα τιμολόγια.</p>
    </div>
    
    <div class="credentials-box">
        <h4>Συνδρομή που ακυρώθηκε</h4>
        <table class="details-table">
            <tr>
                <th>Πακέτο</th>
                <td>{{ $plan->name }}</td>
            </tr>
            <tr>
                <th>Λήξη πρόσβασης</th>
                <td>{{ date_el($endDate) }}</td>
            </tr>
        </table>
    </div>
    
    <p>Αν αλλάξετε γνώμη, μπορείτε να εγγραφείτε ξανά οποιαδήποτε στιγμή.</p>
    
    <div class="text-center mb-6">
        <a href="{{ url('/billing/plans') }}" class="button">Νέα εγγραφή</a>
    </div>
    
    <p class="text-muted text-small">Για τη διατήρηση και διαγραφή των δεδομένων σας ισχύουν όσα αναφέρονται στην <a href="{{ url('/privacy') }}">Πολιτική Απορρήτου</a>. Αν έχετε σχόλια για την υπηρεσία μας, θα χαρούμε να τα ακούσουμε στο <a href="mailto:support@obstechnologies.com">support@obstechnologies.com</a>.</p>
@endsection
