@extends('emails.layouts.base')

@section('title', 'Επιβεβαίωση email')

@section('content')
    <div class="greeting">Επιβεβαίωση διεύθυνσης email</div>
    
    <p>Σας ευχαριστούμε για την εγγραφή σας! Πατήστε το παρακάτω κουμπί για να επιβεβαιώσετε τη διεύθυνση email σας και να ενεργοποιήσετε τον λογαριασμό σας.</p>
    
    <div class="text-center mb-6">
        <a href="{{ $url }}" class="button button-success">Επιβεβαίωση email</a>
    </div>
    
    <div class="info-box">
        <h4>Γιατί χρειάζεται επιβεβαίωση;</h4>
        <p>Η επιβεβαίωση του email σας συμβάλλει στην ασφάλεια του λογαριασμού σας και σας επιτρέπει να λαμβάνετε σημαντικές ειδοποιήσεις σχετικά με τα τιμολόγιά σας.</p>
    </div>
    
    <p class="text-muted text-small">Αν δεν δημιουργήσατε λογαριασμό, δεν απαιτείται καμία ενέργεια από μέρους σας.</p>
    
    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
    
    <p class="text-muted text-small">Αν δεν μπορείτε να πατήσετε το κουμπί, αντιγράψτε και επικολλήστε την παρακάτω διεύθυνση στον browser σας:</p>
    <p class="text-small" style="word-break: break-all; color: #2563eb;">{{ $url }}</p>
@endsection
