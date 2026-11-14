@extends('adminlte::page')

@section('title', 'Ρυθμίσεις WhatsApp')

@section('content_header')
    <h1><i class="fab fa-whatsapp text-success mr-2"></i>Ρυθμίσεις WhatsApp</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            {{-- API Configuration --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Σύνδεση με το WaSenderAPI</h3>
                </div>
                <form action="{{ route('company.whatsapp.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="api_key">Κλειδί API του WaSenderAPI</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('api_key') is-invalid @enderror"
                                       id="api_key" name="api_key" 
                                       placeholder="{{ $settings->api_key ? '••••••••••••••••' : 'Εισαγάγετε το κλειδί API' }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('api_key')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Το κλειδί API το βρίσκετε στον λογαριασμό σας στο <a href="https://wasenderapi.com" target="_blank" rel="noopener">WaSenderAPI.com</a>. Αν το αφήσετε κενό, διατηρείται το αποθηκευμένο.
                            </small>
                            @error('api_key')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="session_id">Session ID (προαιρετικό)</label>
                            <input type="text" class="form-control @error('session_id') is-invalid @enderror"
                                   id="session_id" name="session_id" 
                                   value="{{ old('session_id', $settings->session_id) }}"
                                   placeholder="Το session ID σας">
                            <small class="form-text text-muted">
                                Συμπληρώστε το μόνο αν έχετε περισσότερους αριθμούς WhatsApp στο WaSenderAPI.
                            </small>
                            @error('session_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone_number">Τηλέφωνο παραλήπτη ειδοποιήσεων</label>
                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                   id="phone_number" name="phone_number" 
                                   value="{{ old('phone_number', $settings->phone_number) }}"
                                   placeholder="π.χ. 306912345678">
                            <small class="form-text text-muted">
                                Ο αριθμός που θα λαμβάνει τις ειδοποιήσεις, με κωδικό χώρας και χωρίς «+».
                            </small>
                            @error('phone_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone_display_name">Όνομα εμφάνισης</label>
                            <input type="text" class="form-control @error('phone_display_name') is-invalid @enderror"
                                   id="phone_display_name" name="phone_display_name" 
                                   value="{{ old('phone_display_name', $settings->phone_display_name) }}"
                                   placeholder="π.χ. Γιώργος Παπαδόπουλος">
                            @error('phone_display_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr>

                        <h5 class="mb-3">Ποιες ειδοποιήσεις θα λαμβάνετε</h5>

                        @php
                            $notificationSettings = $settings->notification_settings ?? \App\Models\WhatsAppSettings::defaultNotificationSettings();
                        @endphp

                        <div class="form-group">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="notif_invoice_processed" 
                                       name="notification_settings[invoice_processed]" value="1"
                                       {{ ($notificationSettings['invoice_processed'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notif_invoice_processed">
                                    Επιτυχής επεξεργασία τιμολογίου
                                </label>
                            </div>

                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="notif_invoice_failed" 
                                       name="notification_settings[invoice_failed]" value="1"
                                       {{ ($notificationSettings['invoice_failed'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notif_invoice_failed">
                                    Αποτυχία επεξεργασίας τιμολογίου
                                </label>
                            </div>

                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="notif_payment_received" 
                                       name="notification_settings[payment_received]" value="1"
                                       {{ ($notificationSettings['payment_received'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notif_payment_received">
                                    Πληρωμή συνδρομής που ελήφθη
                                </label>
                            </div>

                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="notif_payment_failed" 
                                       name="notification_settings[payment_failed]" value="1"
                                       {{ ($notificationSettings['payment_failed'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notif_payment_failed">
                                    Αποτυχία πληρωμής συνδρομής
                                </label>
                            </div>

                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="notif_usage_warning" 
                                       name="notification_settings[usage_warning]" value="1"
                                       {{ ($notificationSettings['usage_warning'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notif_usage_warning">
                                    Προειδοποίηση χρήσης (στο 80% του μηνιαίου ορίου)
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_enabled" 
                                       name="is_enabled" value="1"
                                       {{ $settings->is_enabled ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_enabled">
                                    <strong>Ενεργοποίηση ειδοποιήσεων WhatsApp</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        @if(auth()->user()->isAdmin())
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i> Αποθήκευση ρυθμίσεων
                            </button>
                        @else
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle mr-1"></i>
                                Μόνο οι διαχειριστές της εταιρείας μπορούν να αλλάξουν τις ρυθμίσεις WhatsApp.
                            </p>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Test Message --}}
            @if($settings->api_key)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Δοκιμαστικό μήνυμα</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="test_phone">Αριθμός τηλεφώνου</label>
                        <input type="text" class="form-control" id="test_phone" 
                               value="{{ $settings->phone_number }}"
                               placeholder="π.χ. 306912345678">
                    </div>
                    <div class="form-group">
                        <label for="test_message">Μήνυμα</label>
                        <textarea class="form-control" id="test_message" rows="3">Γεια σας από το InvoiceAI! Αυτό είναι δοκιμαστικό μήνυμα.</textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-success" onclick="sendTestMessage()">
                        <i class="fab fa-whatsapp mr-2"></i> Αποστολή δοκιμαστικού μηνύματος
                    </button>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            {{-- Connection Status --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Κατάσταση σύνδεσης</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            @if($settings->is_connected)
                                <span class="badge badge-success p-2">
                                    <i class="fas fa-check-circle"></i> Συνδεδεμένο
                                </span>
                            @elseif($settings->api_key)
                                <span class="badge badge-warning p-2">
                                    <i class="fas fa-exclamation-circle"></i> Δεν έχει ελεγχθεί
                                </span>
                            @else
                                <span class="badge badge-secondary p-2">
                                    <i class="fas fa-times-circle"></i> Δεν έχει ρυθμιστεί
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($settings->last_connected_at)
                        <p class="text-muted small mb-2">
                            Τελευταία επιτυχής σύνδεση: {{ date_el($settings->last_connected_at, true) }}
                        </p>
                    @endif

                    @if($settings->api_key)
                        <button type="button" class="btn btn-outline-primary btn-block" onclick="testConnection()">
                            <i class="fas fa-plug mr-2"></i> Έλεγχος σύνδεσης
                        </button>
                    @endif
                </div>
            </div>

            {{-- Help --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Οδηγός ρύθμισης</h3>
                </div>
                <div class="card-body">
                    <ol class="pl-3 mb-0">
                        <li class="mb-2">Δημιουργήστε λογαριασμό στο <a href="https://wasenderapi.com" target="_blank" rel="noopener">WaSenderAPI.com</a></li>
                        <li class="mb-2">Συνδέστε τον αριθμό WhatsApp σας</li>
                        <li class="mb-2">Αντιγράψτε το κλειδί API</li>
                        <li class="mb-2">Επικολλήστε το εδώ και πατήστε «Αποθήκευση ρυθμίσεων»</li>
                        <li>Πατήστε «Έλεγχος σύνδεσης»</li>
                    </ol>
                </div>
            </div>

            {{-- Status Info --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Σύνοψη</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Ενεργές ειδοποιήσεις
                            <span class="badge badge-{{ $settings->is_enabled ? 'success' : 'secondary' }}">
                                {{ $settings->is_enabled ? 'Ναι' : 'Όχι' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Κλειδί API
                            <span class="badge badge-{{ $settings->api_key ? 'success' : 'secondary' }}">
                                {{ $settings->api_key ? 'Έχει οριστεί' : 'Δεν έχει οριστεί' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Τηλέφωνο
                            <span class="badge badge-{{ $settings->phone_number ? 'success' : 'secondary' }}">
                                {{ $settings->phone_number ? 'Έχει οριστεί' : 'Δεν έχει οριστεί' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
function togglePassword(fieldId) {
    var field = document.getElementById(fieldId);
    field.type = field.type === 'password' ? 'text' : 'password';
}

function testConnection() {
    Swal.fire({
        title: 'Έλεγχος σύνδεσης…',
        text: 'Περιμένετε όσο ελέγχουμε το κλειδί API.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('{{ route("company.whatsapp.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Η σύνδεση πέτυχε',
                text: data.message,
                timer: 3000
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Η σύνδεση απέτυχε',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Σφάλμα',
            text: 'Δεν ήταν δυνατός ο έλεγχος της σύνδεσης. Δοκιμάστε ξανά.'
        });
    });
}

function sendTestMessage() {
    var phone = document.getElementById('test_phone').value;
    var message = document.getElementById('test_message').value;

    if (!phone || !message) {
        Swal.fire({
            icon: 'warning',
            title: 'Λείπουν στοιχεία',
            text: 'Συμπληρώστε αριθμό τηλεφώνου και μήνυμα.'
        });
        return;
    }

    Swal.fire({
        title: 'Αποστολή μηνύματος…',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('{{ route("company.whatsapp.send-test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            phone: phone,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Το μήνυμα στάλθηκε',
                text: data.message
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Η αποστολή απέτυχε',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Σφάλμα',
            text: 'Δεν ήταν δυνατή η αποστολή. Δοκιμάστε ξανά.'
        });
    });
}
</script>
@stop
