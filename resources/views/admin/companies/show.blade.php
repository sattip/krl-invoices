@extends('adminlte::page')

@section('title', $company->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $company->name }}</h1>
        <div>
            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-2"></i> Επεξεργασία
            </a>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left mr-2"></i> Επιστροφή
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
                    <h3 class="card-title">Στοιχεία εταιρείας</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Επωνυμία</dt>
                        <dd>{{ $company->name }}</dd>

                        <dt>Slug</dt>
                        <dd><code>{{ $company->slug }}</code></dd>

                        <dt>Email</dt>
                        <dd>{{ $company->email ?? '-' }}</dd>

                        <dt>Τηλέφωνο</dt>
                        <dd>{{ $company->phone ?? '-' }}</dd>

                        <dt>Διεύθυνση</dt>
                        <dd>{{ $company->address ?? '-' }}</dd>

                        <dt>ΑΦΜ</dt>
                        <dd>{{ $company->vat_number ?? '-' }}</dd>

                        <dt>Κατάσταση</dt>
                        <dd>
                            @if($company->is_active)
                                <span class="badge badge-success">Ενεργή</span>
                            @else
                                <span class="badge badge-danger">Ανενεργή</span>
                            @endif
                        </dd>

                        <dt>Δημιουργία</dt>
                        <dd>{{ date_el($company->created_at, true) }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Χρήστες ({{ $company->users->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Όνομα</th>
                                <th>Email</th>
                                <th>Ρόλος</th>
                                <th>Εγγραφή</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($company->users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge badge-{{ $user->role === 'owner' ? 'success' : ($user->role === 'admin' ? 'warning' : 'secondary') }}">
                                            {{ ['owner' => 'Ιδιοκτήτης', 'admin' => 'Διαχειριστής', 'member' => 'Μέλος'][$user->role] ?? $user->role }}
                                        </span>
                                    </td>
                                    <td>{{ date_el($user->created_at) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">Δεν βρέθηκαν χρήστες.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Πρόσφατα τιμολόγια</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Αρ. τιμολογίου</th>
                                <th>Εκδότης</th>
                                <th>Ποσό</th>
                                <th>Ημερομηνία</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($company->invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number ?? '—' }}</td>
                                    <td>{{ $invoice->issuer_name }}</td>
                                    <td>{{ money_el($invoice->grand_total, $invoice->currency) }}</td>
                                    <td>{{ date_el($invoice->created_at) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">Δεν βρέθηκαν τιμολόγια.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Assign User Form -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Αντιστοίχιση υπάρχοντος χρήστη</h3>
                </div>
                <form action="{{ route('admin.companies.assign-user', $company) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @php
                            $unassignedUsers = \App\Models\User::whereNull('company_id')->orWhere('company_id', '!=', $company->id)->get();
                        @endphp

                        @if($unassignedUsers->isEmpty())
                            <p class="text-muted mb-0">Δεν υπάρχουν διαθέσιμοι χρήστες για αντιστοίχιση.</p>
                        @else
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="user_id">Επιλογή χρήστη</label>
                                        <select class="form-control" name="user_id" id="user_id" required>
                                            <option value="">-- Επιλέξτε χρήστη --</option>
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
                                        <label for="role">Ρόλος</label>
                                        <select class="form-control" name="role" id="role" required>
                                            <option value="member">Μέλος</option>
                                            <option value="admin">Διαχειριστής</option>
                                            <option value="owner">Ιδιοκτήτης</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block">Αντιστοίχιση</button>
                                </div>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
