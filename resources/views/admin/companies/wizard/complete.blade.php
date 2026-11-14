@extends('adminlte::page')

@section('title', 'Η εταιρεία δημιουργήθηκε επιτυχώς')

@section('content_header')
    <h1>Η εταιρεία δημιουργήθηκε επιτυχώς</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Success Alert -->
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Επιτυχία!</h5>
            Η εταιρεία δημιουργήθηκε και το email καλωσορίσματος προς τον ιδιοκτήτη τέθηκε σε ουρά αποστολής.
        </div>

        <!-- Company Details Card -->
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building mr-2"></i>Στοιχεία εταιρείας</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">Επωνυμία εταιρείας</th>
                        <td>{{ $company->name }}</td>
                    </tr>
                    @if($company->email)
                    <tr>
                        <th>Email εταιρείας</th>
                        <td>{{ $company->email }}</td>
                    </tr>
                    @endif
                    @if($company->phone)
                    <tr>
                        <th>Τηλέφωνο</th>
                        <td>{{ $company->phone }}</td>
                    </tr>
                    @endif
                    @if($company->vat_number)
                    <tr>
                        <th>ΑΦΜ</th>
                        <td>{{ $company->vat_number }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Owner Credentials Card -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user mr-2"></i>Στοιχεία σύνδεσης ιδιοκτήτη</h3>
            </div>
            <div class="card-body">
                @if($passwordWasGenerated)
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Σημαντικό:</strong> Αποθηκεύστε αυτά τα στοιχεία σύνδεσης. Ο κωδικός πρόσβασης δεν θα εμφανιστεί ξανά.
                </div>
                @endif
                
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">Ονοματεπώνυμο</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><code>{{ $user->email }}</code></td>
                    </tr>
                    <tr>
                        <th>Κωδικός πρόσβασης</th>
                        <td>
                            <code id="password-display">{{ $password }}</code>
                            <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="copyPassword()">
                                <i class="fas fa-copy"></i> Αντιγραφή
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th>Ρόλος</th>
                        <td><span class="badge badge-primary">Ιδιοκτήτης</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Subscription Details Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-credit-card mr-2"></i>Στοιχεία συνδρομής</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 200px;">Πακέτο</th>
                        <td>
                            <strong>{{ $plan->name }}</strong>
                            <span class="text-muted">
                                ({{ money_el($plan->price) }}/μήνα)
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Όριο τιμολογίων</th>
                        <td>{{ $plan->invoice_limit == -1 ? 'Απεριόριστα' : number_el($plan->invoice_limit) }} τιμολόγια/μήνα</td>
                    </tr>
                    <tr>
                        <th>Δωρεάν πρόσβαση έως</th>
                        <td>
                            <span class="badge badge-warning">
                                {{ date_el($gracePeriodEnd) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Απαιτείται πληρωμή</th>
                        <td><span class="badge badge-success">Όχι (περίοδος δωρεάν πρόσβασης)</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-body">
                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-primary">
                    <i class="fas fa-eye mr-1"></i> Προβολή εταιρείας
                </a>
                <a href="{{ route('admin.companies.wizard.step1') }}" class="btn btn-success">
                    <i class="fas fa-plus mr-1"></i> Δημιουργία νέας εταιρείας
                </a>
                <a href="{{ route('admin.companies.index') }}" class="btn btn-default">
                    <i class="fas fa-list mr-1"></i> Επιστροφή στις εταιρείες
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    function copyPassword() {
        var password = document.getElementById('password-display').textContent;
        navigator.clipboard.writeText(password).then(function() {
            toastr.success('Ο κωδικός πρόσβασης αντιγράφηκε στο πρόχειρο.');
        }, function() {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = password;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            toastr.success('Ο κωδικός πρόσβασης αντιγράφηκε στο πρόχειρο.');
        });
    }
</script>
@stop
