@extends('emails.layouts.base')

@section('title', 'Ενημέρωση συνδρομής')

@section('content')
    <div class="greeting">Η συνδρομή σας ενημερώθηκε</div>
    
    <p>Η συνδρομή σας ενημερώθηκε επιτυχώς.</p>
    
    <div class="credentials-box">
        <h4>Νέα στοιχεία συνδρομής</h4>
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
                <th>Ημερομηνία ισχύος</th>
                <td>{{ date_el($effectiveDate) }}</td>
            </tr>
        </table>
    </div>
    
    <div class="text-center mb-6">
        <a href="{{ url('/billing') }}" class="button">Στοιχεία χρεώσεων</a>
    </div>
    
    <p class="text-muted text-small">Αν δεν κάνατε εσείς αυτή την αλλαγή ή έχετε οποιαδήποτε απορία, επικοινωνήστε άμεσα μαζί μας στο <a href="mailto:support@obstechnologies.com">support@obstechnologies.com</a>.</p>
@endsection
