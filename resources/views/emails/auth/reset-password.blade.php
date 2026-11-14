@extends('emails.layouts.base')

@section('title', 'Επαναφορά κωδικού πρόσβασης')

@section('content')
    <div class="greeting">Επαναφορά κωδικού πρόσβασης</div>
    
    <p>Λαμβάνετε αυτό το email επειδή λάβαμε αίτημα επαναφοράς του κωδικού πρόσβασης για τον λογαριασμό σας.</p>
    
    <div class="text-center mb-6">
        <a href="{{ $url }}" class="button">Επαναφορά κωδικού</a>
    </div>
    
    <div class="info-box warning">
        <h4>Ο σύνδεσμος λήγει σύντομα</h4>
        <p>Ο σύνδεσμος επαναφοράς κωδικού θα λήξει σε {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} λεπτά.</p>
    </div>
    
    <p class="text-muted text-small">Αν δεν ζητήσατε επαναφορά κωδικού, δεν απαιτείται καμία ενέργεια από μέρους σας.</p>
    
    <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
    
    <p class="text-muted text-small">Αν δεν μπορείτε να πατήσετε το κουμπί, αντιγράψτε και επικολλήστε την παρακάτω διεύθυνση στον browser σας:</p>
    <p class="text-small" style="word-break: break-all; color: #2563eb;">{{ $url }}</p>
@endsection
