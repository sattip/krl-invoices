@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    <h1>Edit User: {{ $user->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company_id">Company</label>
                                    <select class="form-control @error('company_id') is-invalid @enderror"
                                            id="company_id" name="company_id">
                                        <option value="">No Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}"
                                                {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Role <span class="text-danger">*</span></label>
                                    <select class="form-control @error('role') is-invalid @enderror"
                                            id="role" name="role" required>
                                        <option value="member" {{ old('role', $user->role) === 'member' ? 'selected' : '' }}>Member</option>
                                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="owner" {{ old('role', $user->role) === 'owner' ? 'selected' : '' }}>Owner</option>
                                    </select>
                                    @error('role')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" placeholder="Leave blank to keep current">
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Only fill if you want to change the password.</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_super_admin" value="0">
                                <input type="checkbox" class="custom-control-input" id="is_super_admin"
                                       name="is_super_admin" value="1"
                                       {{ old('is_super_admin', $user->is_super_admin) ? 'checked' : '' }}
                                       {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                <label class="custom-control-label" for="is_super_admin">Super Admin</label>
                            </div>
                            @if($user->id === auth()->id())
                                <small class="text-muted">You cannot remove your own super admin status.</small>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Info</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>ID</dt>
                        <dd>{{ $user->id }}</dd>

                        <dt>Created</dt>
                        <dd>{{ $user->created_at->format('M d, Y H:i') }}</dd>

                        <dt>Last Updated</dt>
                        <dd>{{ $user->updated_at->format('M d, Y H:i') }}</dd>

                        @if($user->company)
                            <dt>Company Invoices</dt>
                            <dd>{{ $user->company->invoices->count() }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if($user->id !== auth()->id() && !$user->is_super_admin)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Actions</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.users.impersonate', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-info btn-block">
                                <i class="fas fa-user-secret mr-2"></i> Impersonate User
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
