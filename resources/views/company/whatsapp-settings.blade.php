@extends('adminlte::page')

@section('title', 'WhatsApp Settings')

@section('content_header')
    <h1><i class="fab fa-whatsapp text-success mr-2"></i>WhatsApp Settings</h1>
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
            <!-- API Configuration -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API Configuration</h3>
                </div>
                <form action="{{ route('company.whatsapp.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="api_key">WaSenderAPI Key</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('api_key') is-invalid @enderror"
                                       id="api_key" name="api_key" 
                                       placeholder="{{ $settings->api_key ? '••••••••••••••••' : 'Enter your API key' }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('api_key')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Get your API key from <a href="https://wasenderapi.com" target="_blank">WaSenderAPI.com</a>
                            </small>
                            @error('api_key')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="session_id">Session ID (Optional)</label>
                            <input type="text" class="form-control @error('session_id') is-invalid @enderror"
                                   id="session_id" name="session_id" 
                                   value="{{ old('session_id', $settings->session_id) }}"
                                   placeholder="Your session ID">
                            <small class="form-text text-muted">
                                If you have multiple WhatsApp numbers, specify the session ID
                            </small>
                            @error('session_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone_number">Default Recipient Phone</label>
                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                   id="phone_number" name="phone_number" 
                                   value="{{ old('phone_number', $settings->phone_number) }}"
                                   placeholder="e.g., 306912345678">
                            <small class="form-text text-muted">
                                The phone number to receive notifications (with country code, no +)
                            </small>
                            @error('phone_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone_display_name">Display Name</label>
                            <input type="text" class="form-control @error('phone_display_name') is-invalid @enderror"
                                   id="phone_display_name" name="phone_display_name" 
                                   value="{{ old('phone_display_name', $settings->phone_display_name) }}"
                                   placeholder="e.g., John Doe">
                            @error('phone_display_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <hr>

                        <h5 class="mb-3">Notification Settings</h5>

                        @php
                            $notificationSettings = $settings->notification_settings ?? \App\Models\WhatsAppSettings::defaultNotificationSettings();
                        @endphp

                        <div class="form-group">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="notif_invoice_processed" 
                                       name="notification_settings[invoice_processed]" value="1"
                                       {{ ($notificationSettings['invoice_processed'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notif_invoice_processed">
                                    Invoice Processed Successfully
                                </label>
                            </div>

                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="notif_invoice_failed" 
                                       name="notification_settings[invoice_failed]" value="1"
                                       {{ ($notificationSettings['invoice_failed'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notif_invoice_failed">
                                    Invoice Processing Failed
                                </label>
                            </div>

                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="notif_payment_received" 
                                       name="notification_settings[payment_received]" value="1"
                                       {{ ($notificationSettings['payment_received'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notif_payment_received">
                                    Payment Received
                                </label>
                            </div>

                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="notif_payment_failed" 
                                       name="notification_settings[payment_failed]" value="1"
                                       {{ ($notificationSettings['payment_failed'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notif_payment_failed">
                                    Payment Failed
                                </label>
                            </div>

                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="notif_usage_warning" 
                                       name="notification_settings[usage_warning]" value="1"
                                       {{ ($notificationSettings['usage_warning'] ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="notif_usage_warning">
                                    Usage Limit Warning (80% reached)
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
                                    <strong>Enable WhatsApp Notifications</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        @if(auth()->user()->isAdmin())
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i> Save Settings
                            </button>
                        @else
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle mr-1"></i>
                                Only company administrators can update WhatsApp settings.
                            </p>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Test Message -->
            @if($settings->api_key)
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Send Test Message</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="test_phone">Phone Number</label>
                        <input type="text" class="form-control" id="test_phone" 
                               value="{{ $settings->phone_number }}"
                               placeholder="e.g., 306912345678">
                    </div>
                    <div class="form-group">
                        <label for="test_message">Message</label>
                        <textarea class="form-control" id="test_message" rows="3">Hello from InvoiceAI! This is a test message.</textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-success" onclick="sendTestMessage()">
                        <i class="fab fa-whatsapp mr-2"></i> Send Test Message
                    </button>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Connection Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Connection Status</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            @if($settings->is_connected)
                                <span class="badge badge-success p-2">
                                    <i class="fas fa-check-circle"></i> Connected
                                </span>
                            @elseif($settings->api_key)
                                <span class="badge badge-warning p-2">
                                    <i class="fas fa-exclamation-circle"></i> Not Tested
                                </span>
                            @else
                                <span class="badge badge-secondary p-2">
                                    <i class="fas fa-times-circle"></i> Not Configured
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($settings->last_connected_at)
                        <p class="text-muted small mb-2">
                            Last connected: {{ $settings->last_connected_at->diffForHumans() }}
                        </p>
                    @endif

                    @if($settings->api_key)
                        <button type="button" class="btn btn-outline-primary btn-block" onclick="testConnection()">
                            <i class="fas fa-plug mr-2"></i> Test Connection
                        </button>
                    @endif
                </div>
            </div>

            <!-- Help -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Setup Guide</h3>
                </div>
                <div class="card-body">
                    <ol class="pl-3 mb-0">
                        <li class="mb-2">Sign up at <a href="https://wasenderapi.com" target="_blank">WaSenderAPI.com</a></li>
                        <li class="mb-2">Connect your WhatsApp number</li>
                        <li class="mb-2">Copy your API key</li>
                        <li class="mb-2">Paste it here and save</li>
                        <li>Test the connection</li>
                    </ol>
                </div>
            </div>

            <!-- Status Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Enabled
                            <span class="badge badge-{{ $settings->is_enabled ? 'success' : 'secondary' }}">
                                {{ $settings->is_enabled ? 'Yes' : 'No' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            API Key
                            <span class="badge badge-{{ $settings->api_key ? 'success' : 'secondary' }}">
                                {{ $settings->api_key ? 'Configured' : 'Not Set' }}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            Phone
                            <span class="badge badge-{{ $settings->phone_number ? 'success' : 'secondary' }}">
                                {{ $settings->phone_number ? 'Configured' : 'Not Set' }}
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
        title: 'Testing Connection...',
        text: 'Please wait while we verify your API key',
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
                title: 'Connected!',
                text: data.message,
                timer: 3000
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Connection Failed',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to test connection. Please try again.'
        });
    });
}

function sendTestMessage() {
    var phone = document.getElementById('test_phone').value;
    var message = document.getElementById('test_message').value;

    if (!phone || !message) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please enter both phone number and message.'
        });
        return;
    }

    Swal.fire({
        title: 'Sending Message...',
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
                title: 'Message Sent!',
                text: data.message
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Failed to Send',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to send message. Please try again.'
        });
    });
}
</script>
@stop
