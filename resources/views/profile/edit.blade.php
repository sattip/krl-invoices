@extends('adminlte::page')

@section('title', 'Προφίλ')

@section('content_header')
    <h1>Ρυθμίσεις προφίλ</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <!-- Update Profile Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Στοιχεία προφίλ</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Ενημερώστε το ονοματεπώνυμο και τη διεύθυνση email του λογαριασμού σας.</p>

                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                        @csrf
                    </form>

                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="form-group">
                            <label for="name">Ονοματεπώνυμο</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror

                            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                <div class="mt-2">
                                    <p class="text-muted small">
                                        Η διεύθυνση email σας δεν έχει επαληθευτεί.
                                        <button form="send-verification" class="btn btn-link p-0">
                                            Πατήστε εδώ για να σας σταλεί ξανά το email επαλήθευσης.
                                        </button>
                                    </p>

                                    @if (session('status') === 'verification-link-sent')
                                        <p class="text-success small">
                                            Στάλθηκε νέος σύνδεσμος επαλήθευσης στη διεύθυνση email σας.
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">Αποθήκευση</button>

                        @if (session('status') === 'profile-updated')
                            <span class="text-success ml-3">Αποθηκεύτηκε.</span>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Update Password -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Αλλαγή κωδικού πρόσβασης</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Χρησιμοποιήστε έναν μεγάλο και τυχαίο κωδικό πρόσβασης για να παραμένει ασφαλής ο λογαριασμός σας.</p>

                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="form-group">
                            <label for="current_password">Τρέχων κωδικός πρόσβασης</label>
                            <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" id="current_password" name="current_password">
                            @error('current_password', 'updatePassword')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Νέος κωδικός πρόσβασης</label>
                            <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" id="password" name="password">
                            @error('password', 'updatePassword')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Επιβεβαίωση νέου κωδικού</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>

                        <button type="submit" class="btn btn-primary">Αποθήκευση</button>

                        @if (session('status') === 'password-updated')
                            <span class="text-success ml-3">Αποθηκεύτηκε.</span>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Delete Account -->
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">Διαγραφή λογαριασμού</h3>
                </div>
                <div class="card-body">
                    <p>Με τη διαγραφή του λογαριασμού σας:</p>
                    <ul class="text-muted">
                        <li>διαγράφεται οριστικά ο λογαριασμός χρήστη σας και αποσυνδέεστε από την εφαρμογή·</li>
                        <li>διαγράφονται οριστικά από τη βάση δεδομένων τα παραστατικά που έχετε ανεβάσει εσείς, μαζί με τα δεδομένα που εξήχθησαν από αυτά·</li>
                        <li>τα αρχεία (PDF/εικόνες) αυτών των παραστατικών <strong>δεν</strong> διαγράφονται αυτόματα από τον χώρο αποθήκευσης·</li>
                        <li>η εταιρεία, οι υπόλοιποι χρήστες της και τα παραστατικά που ανέβασαν άλλοι χρήστες <strong>δεν</strong> διαγράφονται·</li>
                        <li>η συνδρομή της εταιρείας <strong>δεν</strong> ακυρώνεται αυτόματα — αν χρειάζεται, ακυρώστε την πρώτα από τη σελίδα «Χρέωση & Χρήση».</li>
                    </ul>
                    <p class="text-muted">Για πλήρη διαγραφή της εταιρείας και όλων των δεδομένων της, συμπεριλαμβανομένων των αποθηκευμένων αρχείων, επικοινωνήστε μαζί μας στο <a href="mailto:support@obstechnologies.com">support@obstechnologies.com</a>. Πριν από τη διαγραφή, κατεβάστε όσα δεδομένα θέλετε να διατηρήσετε.</p>

                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirm-user-deletion">
                        Διαγραφή λογαριασμού
                    </button>
                </div>
            </div>
        </div>
    </div>

    <p class="text-muted small mt-2">
        <a href="{{ url('/privacy') }}">Πολιτική Απορρήτου</a>
        &middot;
        <a href="{{ url('/terms') }}">Όροι Χρήσης</a>
    </p>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="confirm-user-deletion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title">Είστε βέβαιοι ότι θέλετε να διαγράψετε τον λογαριασμό σας;</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Η ενέργεια είναι οριστική και δεν αναιρείται. Θα διαγραφούν ο λογαριασμός σας και τα παραστατικά που έχετε ανεβάσει εσείς· η εταιρεία και η συνδρομή της παραμένουν. Πληκτρολογήστε τον κωδικό πρόσβασής σας για επιβεβαίωση.</p>

                        <div class="form-group">
                            <label for="delete_password">Κωδικός πρόσβασης</label>
                            <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" id="delete_password" name="password" placeholder="Κωδικός πρόσβασης">
                            @error('password', 'userDeletion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Άκυρο</button>
                        <button type="submit" class="btn btn-danger">Οριστική διαγραφή λογαριασμού</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
@if ($errors->userDeletion->isNotEmpty())
<script>
    $(document).ready(function() {
        $('#confirm-user-deletion').modal('show');
    });
</script>
@endif
@stop
