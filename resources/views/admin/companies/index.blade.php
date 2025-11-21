@extends('adminlte::page')

@section('title', 'Manage Companies')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Manage Companies</h1>
        <div class="btn-group">
            <a href="{{ route('admin.companies.wizard.step1') }}" class="btn btn-success">
                <i class="fas fa-magic mr-1"></i> Create with Wizard
            </a>
            <a href="{{ route('admin.companies.create') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus mr-1"></i> Quick Create
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

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Users</th>
                        <th>Invoices</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td>
                                <strong>{{ $company->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $company->slug }}</small>
                            </td>
                            <td>{{ $company->email ?? '-' }}</td>
                            <td>
                                <span class="badge badge-primary">{{ $company->users_count }}</span>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $company->invoices_count }}</span>
                            </td>
                            <td>
                                @if($company->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $company->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">No companies found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($companies->hasPages())
            <div class="card-footer">
                {{ $companies->links() }}
            </div>
        @endif
    </div>
@stop
