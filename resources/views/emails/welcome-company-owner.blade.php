@extends('emails.layouts.base')

@section('title', 'Καλώς ορίσατε στο InvoiceAI')

@section('content')
    <div class="greeting">Καλώς ορίσατε στο InvoiceAI, {{ $user->name }}!</div>
    
    <p>Ο εταιρικός λογαριασμός για την εταιρεία <strong>{{ $company->name }}</strong> δημιουργήθηκε και είναι έτοιμος για χρήση.</p>
    
    <div class="credentials-box">
        <h4>Τα στοιχεία σύνδεσής σας</h4>
        <table class="details-table">
            <tr>
                <th>Email</th>
                <td><code style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px;">{{ $user->email }}</code></td>
            </tr>
            <tr>
                <th>Κωδικός πρόσβασης</th>
                <td><code style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px;">{{ $password }}</code></td>
            </tr>
        </table>
    </div>
    
    <div class="info-box warning">
        <h4>Σημαντικό</h4>
        <p>Για λόγους ασφαλείας, παρακαλούμε αλλάξτε τον κωδικό πρόσβασής σας μετά την πρώτη σύνδεση.</p>
    </div>
    
    <div class="credentials-box">
        <h4>Στοιχεία πακέτου</h4>
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
                <th>Πρόσβαση έως</th>
                <td style="color: #f59e0b; font-weight: 600;">{{ date_el($gracePeriodEnd) }}</td>
            </tr>
        </table>
    </div>
    
    <p>Σας έχει παραχωρηθεί δωρεάν πρόσβαση έως την παραπάνω ημερομηνία. Κατά το διάστημα αυτό δεν απαιτείται πληρωμή.</p>
    
    <div class="text-center mb-6">
        <a href="{{ url('/login') }}" class="button">Σύνδεση στον λογαριασμό σας</a>
    </div>
    
    <p class="text-muted text-small">Για οποιαδήποτε απορία ή βοήθεια, μη διστάσετε να επικοινωνήσετε μαζί μας στο <a href="mailto:support@obstechnologies.com">support@obstechnologies.com</a>.</p>
@endsection
