@extends('adminlte::page')

@section('title', 'Στοιχεία τιμολογίου')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Στοιχεία τιμολογίου</h1>
        <div>
            <a href="{{ route('invoices.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left mr-2"></i> Επιστροφή στη λίστα
            </a>
            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Να διαγραφεί οριστικά αυτό το τιμολόγιο και το αρχείο του;');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash mr-2"></i> Διαγραφή
                </button>
            </form>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <!-- Invoice Data -->
        <div class="col-lg-7">
            <!-- Basic Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Πληροφορίες τιμολογίου</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Αριθμός τιμολογίου</strong>
                            <p class="text-muted">{{ $invoice->invoice_number ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Ημερομηνία έκδοσης</strong>
                            <p class="text-muted">{{ date_el($invoice->invoice_date) }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Νόμισμα</strong>
                            <p class="text-muted">{{ $invoice->currency ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Αρχικό αρχείο</strong>
                            <p class="text-muted">{{ $invoice->original_filename ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Issuer & Customer -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Εκδότης</h5>
                            <p class="mb-1"><strong>{{ $invoice->issuer_name }}</strong></p>
                            @if ($invoice->issuer_vat)
                                <p class="mb-1 text-muted small">ΑΦΜ: {{ $invoice->issuer_vat }}</p>
                            @endif
                            @if ($invoice->issuer_address)
                                <p class="mb-0 text-muted small" style="white-space: pre-line;">{{ $invoice->issuer_address }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5>Πελάτης</h5>
                            @if ($invoice->customer_name)
                                <p class="mb-1"><strong>{{ $invoice->customer_name }}</strong></p>
                                @if ($invoice->customer_vat)
                                    <p class="mb-1 text-muted small">ΑΦΜ: {{ $invoice->customer_vat }}</p>
                                @endif
                                @if ($invoice->customer_address)
                                    <p class="mb-0 text-muted small" style="white-space: pre-line;">{{ $invoice->customer_address }}</p>
                                @endif
                            @else
                                <p class="text-muted">Δεν βρέθηκαν στοιχεία πελάτη</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Γραμμές ειδών</h3>
                </div>
                <div class="card-body p-0">
                    @if ($invoice->lineItems->isEmpty())
                        <p class="text-muted text-center py-3">Δεν βρέθηκαν γραμμές ειδών</p>
                    @else
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Περιγραφή</th>
                                    <th class="text-right">Ποσότητα</th>
                                    <th class="text-right">Τιμή μονάδας</th>
                                    <th class="text-right">ΦΠΑ %</th>
                                    <th class="text-right">Σύνολο</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->lineItems as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-right">{{ number_el($item->quantity, 2) }}</td>
                                        <td class="text-right">{{ number_el($item->unit_price, 2) }}</td>
                                        <td class="text-right">{{ $item->vat_rate !== null ? number_el($item->vat_rate) . '%' : '-' }}</td>
                                        <td class="text-right"><strong>{{ number_el($item->line_total, 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <!-- Discounts -->
            @if ($invoice->discounts->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Εκπτώσεις</h3>
                    </div>
                    <div class="card-body">
                        @foreach ($invoice->discounts as $discount)
                            <div class="d-flex justify-content-between">
                                <span>{{ $discount->description }}</span>
                                <span class="text-danger">-{{ number_el($discount->amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Other Charges -->
            @if ($invoice->otherCharges->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Λοιπές χρεώσεις</h3>
                    </div>
                    <div class="card-body">
                        @foreach ($invoice->otherCharges as $charge)
                            <div class="d-flex justify-content-between">
                                <span>{{ $charge->description }}</span>
                                <span>{{ number_el($charge->amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Totals -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Σύνολα</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Καθαρή αξία</span>
                        <span>{{ money_el($invoice->subtotal, $invoice->currency) }}</span>
                    </div>
                    @if ($invoice->total_discounts > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Σύνολο εκπτώσεων</span>
                            <span class="text-danger">-{{ money_el($invoice->total_discounts, $invoice->currency) }}</span>
                        </div>
                    @endif
                    @if ($invoice->total_other_charges > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Λοιπές χρεώσεις</span>
                            <span>{{ money_el($invoice->total_other_charges, $invoice->currency) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2">
                        <span>Σύνολο ΦΠΑ</span>
                        <span>{{ money_el($invoice->vat_total, $invoice->currency) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Γενικό σύνολο</strong>
                        <strong class="text-lg">{{ money_el($invoice->grand_total, $invoice->currency) }}</strong>
                    </div>

                    @if (!$invoice->totals_match)
                        <div class="alert alert-warning mt-3 mb-0">
                            <small><strong>Σημείωση:</strong> Τα υπολογισμένα σύνολα μπορεί να διαφέρουν λόγω στρογγυλοποιήσεων ή χρεώσεων που δεν αναγνωρίστηκαν. Ελέγξτε τα στοιχεία με το πρωτότυπο.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- File Preview -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Πρωτότυπο έγγραφο</h3>
                </div>
                <div class="card-body">
                    @php
                        $extension = strtolower(pathinfo($invoice->file_path, PATHINFO_EXTENSION));
                    @endphp

                    @if ($extension === 'pdf')
                        <div style="height: 600px;">
                            <iframe src="{{ route('invoices.file', $invoice) }}" class="w-100 h-100 border rounded" title="PDF τιμολογίου"></iframe>
                        </div>
                        <a href="{{ route('invoices.file', $invoice) }}" target="_blank" class="btn btn-link mt-2">
                            <i class="fas fa-external-link-alt mr-1"></i> Άνοιγμα PDF σε νέα καρτέλα
                        </a>
                    @else
                        <img src="{{ route('invoices.file', $invoice) }}" alt="Τιμολόγιο" class="img-fluid rounded border">
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
