@extends('adminlte::page')

@section('title', 'Ιστορικό πληρωμών')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Ιστορικό πληρωμών</h1>
        <a href="{{ route('billing.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Επιστροφή στη χρέωση
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Ημερομηνία</th>
                        <th>Περιγραφή</th>
                        <th>Ποσό</th>
                        <th>Κατάσταση</th>
                        <th>Τιμολόγιο</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stripeInvoices as $invoice)
                        <tr>
                            <td>{{ date_el(\Carbon\Carbon::createFromTimestamp($invoice->created)) }}</td>
                            <td>
                                @if($invoice->lines->data)
                                    {{ $invoice->lines->data[0]->description ?? 'Συνδρομή' }}
                                @else
                                    Συνδρομή
                                @endif
                            </td>
                            <td>{{ money_el($invoice->amount_paid / 100, $invoice->currency ?? 'EUR') }}</td>
                            <td>
                                @if($invoice->status === 'paid')
                                    <span class="badge badge-success">Εξοφλημένο</span>
                                @elseif($invoice->status === 'open')
                                    <span class="badge badge-warning">Ανοιχτό</span>
                                @elseif($invoice->status === 'draft')
                                    <span class="badge badge-secondary">Πρόχειρο</span>
                                @elseif($invoice->status === 'void')
                                    <span class="badge badge-dark">Ακυρωμένο</span>
                                @else
                                    <span class="badge badge-secondary">{{ $invoice->status === 'uncollectible' ? 'Μη εισπράξιμο' : ucfirst((string) $invoice->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($invoice->invoice_pdf)
                                    <a href="{{ $invoice->invoice_pdf }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="Λήψη PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <p class="text-muted mb-0">Δεν βρέθηκαν πληρωμές.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
