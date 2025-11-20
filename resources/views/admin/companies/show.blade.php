@extends('adminlte::page')

@section('title', $company->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $company->name }}</h1>
        <div>
            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Company Details</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Name</dt>
                        <dd>{{ $company->name }}</dd>

                        <dt>Slug</dt>
                        <dd><code>{{ $company->slug }}</code></dd>

                        <dt>Email</dt>
                        <dd>{{ $company->email ?? '-' }}</dd>

                        <dt>Phone</dt>
                        <dd>{{ $company->phone ?? '-' }}</dd>

                        <dt>Address</dt>
                        <dd>{{ $company->address ?? '-' }}</dd>

                        <dt>VAT Number</dt>
                        <dd>{{ $company->vat_number ?? '-' }}</dd>

                        <dt>Status</dt>
                        <dd>
                            @if($company->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </dd>

                        <dt>Created</dt>
                        <dd>{{ $company->created_at->format('M d, Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Users ({{ $company->users->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($company->users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge badge-{{ $user->role === 'owner' ? 'success' : ($user->role === 'admin' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Invoices</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Issuer</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($company->invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number ?? 'N/A' }}</td>
                                    <td>{{ $invoice->issuer_name }}</td>
                                    <td>{{ $invoice->currency }} {{ number_format($invoice->grand_total, 2) }}</td>
                                    <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">No invoices found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Assign User Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Assign Existing User</h3>
                </div>
                <form action="{{ route('admin.companies.assign-user', $company) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @php
                            $unassignedUsers = \App\Models\User::whereNull('company_id')->orWhere('company_id', '!=', $company->id)->get();
                        @endphp

                        @if($unassignedUsers->isEmpty())
                            <p class="text-muted mb-0">No unassigned users available.</p>
                        @else
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="user_id">Select User</label>
                                        <select class="form-control" name="user_id" id="user_id" required>
                                            <option value="">-- Select User --</option>
                                            @foreach($unassignedUsers as $user)
                                                <option value="{{ $user->id }}">
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="role">Role</label>
                                        <select class="form-control" name="role" id="role" required>
                                            <option value="member">Member</option>
                                            <option value="admin">Admin</option>
                                            <option value="owner">Owner</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block">Assign</button>
                                </div>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
