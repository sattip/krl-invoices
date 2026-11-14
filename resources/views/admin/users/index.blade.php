@extends('adminlte::page')

@section('title', 'Διαχείριση χρηστών')

@section('content_header')
    <h1>Διαχείριση χρηστών</h1>
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

    <!-- Filters -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Φίλτρα</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Αναζήτηση</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Όνομα ή email" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Εταιρεία</label>
                        <select name="company_id" class="form-control">
                            <option value="">Όλες οι εταιρείες</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Ρόλος</label>
                        <select name="role" class="form-control">
                            <option value="">Όλοι οι ρόλοι</option>
                            <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Ιδιοκτήτης</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Διαχειριστής</option>
                            <option value="member" {{ request('role') === 'member' ? 'selected' : '' }}>Μέλος</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Super Admin</label>
                        <select name="is_super_admin" class="form-control">
                            <option value="">Όλοι</option>
                            <option value="1" {{ request('is_super_admin') === '1' ? 'selected' : '' }}>Ναι</option>
                            <option value="0" {{ request('is_super_admin') === '0' ? 'selected' : '' }}>Όχι</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-1"></i> Φιλτράρισμα
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-default">Καθαρισμός</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Όνομα</th>
                        <th>Email</th>
                        <th>Εταιρεία</th>
                        <th>Ρόλος</th>
                        <th>Super Admin</th>
                        <th>Δημιουργία</th>
                        <th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->company)
                                    <a href="{{ route('admin.companies.show', $user->company) }}">
                                        {{ $user->company->name }}
                                    </a>
                                @else
                                    <span class="text-muted">Χωρίς εταιρεία</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $user->role === 'owner' ? 'success' : ($user->role === 'admin' ? 'warning' : 'secondary') }}">
                                    {{ ['owner' => 'Ιδιοκτήτης', 'admin' => 'Διαχειριστής', 'member' => 'Μέλος'][$user->role] ?? $user->role }}
                                </span>
                            </td>
                            <td>
                                @if($user->is_super_admin)
                                    <span class="badge badge-danger">Ναι</span>
                                @else
                                    <span class="text-muted">Όχι</span>
                                @endif
                            </td>
                            <td>{{ date_el($user->created_at) }}</td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning" title="Επεξεργασία">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->id !== auth()->id() && !$user->is_super_admin)
                                    <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info" title="Σύνδεση ως χρήστης">
                                            <i class="fas fa-user-secret"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">Δεν βρέθηκαν χρήστες.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="card-footer">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
@stop
