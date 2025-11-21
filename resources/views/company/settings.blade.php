@extends('adminlte::page')

@section('title', 'Company Settings')

@section('content_header')
    <h1>Company Settings</h1>
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
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Company Information</h3>
                </div>
                <form action="{{ route('company.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $company->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Company Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $company->email) }}">
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', $company->phone) }}">
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="3">{{ old('address', $company->address) }}</textarea>
                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="vat_number">VAT Number</label>
                            <input type="text" class="form-control @error('vat_number') is-invalid @enderror"
                                   id="vat_number" name="vat_number" value="{{ old('vat_number', $company->vat_number) }}">
                            @error('vat_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        @if(auth()->user()->isAdmin())
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        @else
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle mr-1"></i>
                                Only company administrators can update settings.
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Company Stats</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Users
                            <span class="badge badge-primary badge-pill">{{ $company->users->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Invoices
                            <span class="badge badge-info badge-pill">{{ $company->invoices->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Created
                            <span class="text-muted">{{ $company->created_at->format('M d, Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Your Role</h3>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <span class="badge badge-{{ auth()->user()->role === 'owner' ? 'success' : (auth()->user()->role === 'admin' ? 'warning' : 'secondary') }}">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
@stop
