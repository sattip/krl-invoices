@extends('adminlte::page')

@section('title', 'Create Company - Step 2')

@section('content_header')
    <h1>Create Company</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Progress Steps -->
        <div class="card">
            <div class="card-body">
                <div class="bs-stepper">
                    <div class="bs-stepper-header" role="tablist">
                        <div class="step completed">
                            <button type="button" class="step-trigger" disabled>
                                <span class="bs-stepper-circle bg-success"><i class="fas fa-check"></i></span>
                                <span class="bs-stepper-label">Company Info</span>
                            </button>
                        </div>
                        <div class="line bg-success"></div>
                        <div class="step active">
                            <button type="button" class="step-trigger" disabled>
                                <span class="bs-stepper-circle bg-primary">2</span>
                                <span class="bs-stepper-label">Owner Account</span>
                            </button>
                        </div>
                        <div class="line"></div>
                        <div class="step">
                            <button type="button" class="step-trigger" disabled>
                                <span class="bs-stepper-circle">3</span>
                                <span class="bs-stepper-label">Plan & Grace Period</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2 Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Step 2: Owner Account</h3>
            </div>
            <form action="{{ route('admin.companies.wizard.store-step2') }}" method="POST">
                @csrf
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Creating owner account for: <strong>{{ $wizardData['company_name'] }}</strong>
                    </div>

                    <div class="form-group">
                        <label for="owner_name">Owner Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('owner_name') is-invalid @enderror" 
                               id="owner_name" name="owner_name" 
                               value="{{ old('owner_name', $wizardData['owner_name'] ?? '') }}" required>
                        @error('owner_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="owner_email">Owner Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('owner_email') is-invalid @enderror" 
                               id="owner_email" name="owner_email" 
                               value="{{ old('owner_email', $wizardData['owner_email'] ?? '') }}" required>
                        @error('owner_email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">This email will be used for login and notifications.</small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="auto_generate_password" 
                                   name="auto_generate_password" value="1" 
                                   {{ old('auto_generate_password', true) ? 'checked' : '' }}
                                   onchange="togglePasswordField()">
                            <label class="custom-control-label" for="auto_generate_password">
                                Auto-generate secure password
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="password_field" style="display: none;">
                        <label for="owner_password">Password</label>
                        <input type="password" class="form-control @error('owner_password') is-invalid @enderror" 
                               id="owner_password" name="owner_password" minlength="8">
                        @error('owner_password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">Minimum 8 characters.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.companies.wizard.step1') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary float-right">
                        Next <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .bs-stepper-header {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .step {
        display: flex;
        align-items: center;
    }
    .step-trigger {
        display: flex;
        align-items: center;
        background: none;
        border: none;
        padding: 0;
    }
    .bs-stepper-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 10px;
        color: white;
    }
    .step.active .bs-stepper-circle {
        background: #007bff;
    }
    .step.completed .bs-stepper-circle {
        background: #28a745;
    }
    .line {
        flex: 1;
        height: 2px;
        background: #dee2e6;
        margin: 0 15px;
        max-width: 100px;
    }
    .line.bg-success {
        background: #28a745;
    }
</style>
@stop

@section('js')
<script>
    function togglePasswordField() {
        var checkbox = document.getElementById('auto_generate_password');
        var passwordField = document.getElementById('password_field');
        passwordField.style.display = checkbox.checked ? 'none' : 'block';
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        togglePasswordField();
    });
</script>
@stop
