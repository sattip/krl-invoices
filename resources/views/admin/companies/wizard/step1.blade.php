@extends('adminlte::page')

@section('title', 'Create Company - Step 1')

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
                        <div class="step active">
                            <button type="button" class="step-trigger" disabled>
                                <span class="bs-stepper-circle bg-primary">1</span>
                                <span class="bs-stepper-label">Company Info</span>
                            </button>
                        </div>
                        <div class="line"></div>
                        <div class="step">
                            <button type="button" class="step-trigger" disabled>
                                <span class="bs-stepper-circle">2</span>
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

        <!-- Step 1 Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Step 1: Company Information</h3>
            </div>
            <form action="{{ route('admin.companies.wizard.store-step1') }}" method="POST">
                @csrf
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="form-group">
                        <label for="company_name">Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                               id="company_name" name="company_name" 
                               value="{{ old('company_name', $wizardData['company_name'] ?? '') }}" required>
                        @error('company_name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_email">Company Email</label>
                        <input type="email" class="form-control @error('company_email') is-invalid @enderror" 
                               id="company_email" name="company_email" 
                               value="{{ old('company_email', $wizardData['company_email'] ?? '') }}">
                        @error('company_email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_phone">Phone</label>
                                <input type="text" class="form-control @error('company_phone') is-invalid @enderror" 
                                       id="company_phone" name="company_phone" 
                                       value="{{ old('company_phone', $wizardData['company_phone'] ?? '') }}">
                                @error('company_phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_vat">VAT Number</label>
                                <input type="text" class="form-control @error('company_vat') is-invalid @enderror" 
                                       id="company_vat" name="company_vat" 
                                       value="{{ old('company_vat', $wizardData['company_vat'] ?? '') }}">
                                @error('company_vat')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="company_address">Address</label>
                        <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                  id="company_address" name="company_address" rows="3">{{ old('company_address', $wizardData['company_address'] ?? '') }}</textarea>
                        @error('company_address')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-default">Cancel</a>
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
    }
    .step.active .bs-stepper-circle {
        background: #007bff;
        color: white;
    }
    .line {
        flex: 1;
        height: 2px;
        background: #dee2e6;
        margin: 0 15px;
        max-width: 100px;
    }
</style>
@stop
