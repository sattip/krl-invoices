@extends('emails.layouts.base')

@section('title', 'Αποτυχία πληρωμής')

@section('content')
    <div class="greeting">Αποτυχία πληρωμής</div>
    
    <p>Δεν ήταν δυνατή η επεξεργασία της πληρωμής για τη συνδρομή σας στο InvoiceAI.</p>
    
    <div class="info-box danger">
        <h4>Απαιτείται ενέργεια</h4>
        <p>Παρακαλούμε ενημερώστε τον τρόπο πληρωμής σας, ώστε να αποφευχθεί διακοπή της υπηρεσίας. Η συνδρομή σας θα παραμείνει ενεργή για {{ config('subscription.grace_period_days', 3) }} ακόμη ημέρες.</p>
    </div>
    
    <div class="credentials-box">
        <h4>Στοιχεία πληρωμής</h4>
        <table class="details-table">
            <tr>
                <th>Οφειλόμενο ποσό</th>
                <td><strong>{{ $amount }}</strong></td>
            </tr>
            <tr>
                <th>Πακέτο</th>
                <td>{{ $planName }}</td>
            </tr>
            <tr>
                <th>Ημερομηνία προσπάθειας</th>
                <td>{{ date_el($date) }}</td>
            </tr>
        </table>
    </div>
    
    <div class="text-center mb-6">
        <a href="{{ url('/billing') }}" class="button button-danger">Ενημέρωση τρόπου πληρωμής</a>
    </div>
    
    <p class="text-muted text-small">Συνήθεις αιτίες αποτυχίας πληρωμής είναι η ληγμένη κάρτα, το ανεπαρκές υπόλοιπο ή τα μη ενημερωμένα στοιχεία χρέωσης.</p>
    
    <p class="text-muted text-small">Αν πιστεύετε ότι πρόκειται για λάθος ή χρειάζεστε βοήθεια, επικοινωνήστε μαζί μας στο <a href="mailto:support@obstechnologies.com">support@obstechnologies.com</a>.</p>
@endsection
