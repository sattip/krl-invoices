@extends('adminlte::page')

@section('title', 'Τιμολόγια')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Τιμολόγια</h1>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-upload mr-2"></i> Μεταφόρτωση τιμολογίου
        </a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            @if ($invoices->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Δεν υπάρχουν ακόμη τιμολόγια</h5>
                    <p class="text-muted">Ανεβάστε το πρώτο σας τιμολόγιο για να ξεκινήσετε</p>
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                        <i class="fas fa-upload mr-2"></i> Μεταφόρτωση τιμολογίου
                    </a>
                </div>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Αρ. τιμολογίου</th>
                            <th>Εκδότης</th>
                            <th>Ημερομηνία</th>
                            <th>Ποσό</th>
                            <th>Μεταφόρτωση</th>
                            <th class="text-right">Ενέργειες</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td>
                                    <strong>{{ $invoice->invoice_number ?? '—' }}</strong>
                                </td>
                                <td>{{ $invoice->issuer_name }}</td>
                                <td>{{ date_el($invoice->invoice_date) }}</td>
                                <td>
                                    <span class="badge badge-success">
                                        {{ money_el($invoice->grand_total, $invoice->currency) }}
                                    </span>
                                </td>
                                <td>{{ $invoice->created_at->diffForHumans() }}</td>
                                <td class="text-right">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-info" title="Προβολή">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="d-inline" onsubmit="return confirm('Να διαγραφεί οριστικά αυτό το τιμολόγιο και το αρχείο του;');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Διαγραφή">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        @if ($invoices->hasPages())
            <div class="card-footer">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
@stop
