@extends('adminlte::page')

@section('title', 'Create Company - Step 3')

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
                        <div class="step completed">
                            <button type="button" class="step-trigger" disabled>
                                <span class="bs-stepper-circle bg-success"><i class="fas fa-check"></i></span>
                                <span class="bs-stepper-label">Owner Account</span>
                            </button>
                        </div>
                        <div class="line bg-success"></div>
                        <div class="step active">
                            <button type="button" class="step-trigger" disabled>
                                <span class="bs-stepper-circle bg-primary">3</span>
                                <span class="bs-stepper-label">Plan & Grace Period</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3 Form -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Step 3: Plan & Grace Period</h3>
            </div>
            <form action="{{ route('admin.companies.wizard.store-step3') }}" method="POST">
                @csrf
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Assigning plan for: <strong>{{ $wizardData['company_name'] }}</strong> 
                        (Owner: {{ $wizardData['owner_email'] }})
                    </div>

                    <div class="form-group">
                        <label for="plan_id">Select Plan <span class="text-danger">*</span></label>
                        <select class="form-control @error('plan_id') is-invalid @enderror" 
                                id="plan_id" name="plan_id" required>
                            <option value="">-- Select a Plan --</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" 
                                        {{ old('plan_id', $wizardData['plan_id'] ?? '') == $plan->id ? 'selected' : '' }}
                                        data-price="{{ $plan->price }}"
                                        data-limit="{{ $plan->invoice_limit }}">
                                    {{ $plan->name }} - €{{ number_format($plan->price, 2) }}/mo 
                                    ({{ $plan->invoice_limit == -1 ? 'Unlimited' : $plan->invoice_limit }} invoices)
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="grace_period_end">Grace Period End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('grace_period_end') is-invalid @enderror" 
                               id="grace_period_end" name="grace_period_end" 
                               value="{{ old('grace_period_end', $wizardData['grace_period_end'] ?? date('Y-m-d', strtotime('+30 days'))) }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        @error('grace_period_end')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            The company will have full access until this date without payment.
                        </small>
                    </div>

                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-info-circle text-info mr-2"></i>Summary</h5>
                            <ul class="mb-0">
                                <li><strong>Company:</strong> {{ $wizardData['company_name'] }}</li>
                                <li><strong>Owner:</strong> {{ $wizardData['owner_name'] }} ({{ $wizardData['owner_email'] }})</li>
                                <li><strong>Plan:</strong> <span id="selected_plan">Not selected</span></li>
                                <li><strong>Access Until:</strong> <span id="grace_display">{{ date('F j, Y', strtotime('+30 days')) }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.companies.wizard.step2') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-success float-right">
                        <i class="fas fa-check mr-1"></i> Create Company
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
    document.addEventListener('DOMContentLoaded', function() {
        var planSelect = document.getElementById('plan_id');
        var graceDateInput = document.getElementById('grace_period_end');
        
        function updateSummary() {
            var selectedOption = planSelect.options[planSelect.selectedIndex];
            var selectedPlanSpan = document.getElementById('selected_plan');
            var graceDisplaySpan = document.getElementById('grace_display');
            
            if (selectedOption.value) {
                selectedPlanSpan.textContent = selectedOption.text;
            } else {
                selectedPlanSpan.textContent = 'Not selected';
            }
            
            if (graceDateInput.value) {
                var date = new Date(graceDateInput.value);
                graceDisplaySpan.textContent = date.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            }
        }
        
        planSelect.addEventListener('change', updateSummary);
        graceDateInput.addEventListener('change', updateSummary);
        
        // Initial update
        updateSummary();
    });
</script>
@stop
