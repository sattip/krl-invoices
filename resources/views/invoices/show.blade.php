@extends('adminlte::page')

@section('title', 'Invoice Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Invoice Details</h1>
        <div>
            <a href="{{ route('invoices.index') }}" class="btn btn-default">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>
            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash mr-2"></i> Delete
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
                    <h3 class="card-title">Invoice Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Invoice Number</strong>
                            <p class="text-muted">{{ $invoice->invoice_number ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Invoice Date</strong>
                            <p class="text-muted">{{ $invoice->invoice_date?->format('Y-m-d') ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Currency</strong>
                            <p class="text-muted">{{ $invoice->currency ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Original File</strong>
                            <p class="text-muted">{{ $invoice->original_filename ?? 'Unknown' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Issuer & Customer -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Issuer</h5>
                            <p class="mb-1"><strong>{{ $invoice->issuer_name }}</strong></p>
                            @if ($invoice->issuer_vat)
                                <p class="mb-1 text-muted small">VAT: {{ $invoice->issuer_vat }}</p>
                            @endif
                            @if ($invoice->issuer_address)
                                <p class="mb-0 text-muted small" style="white-space: pre-line;">{{ $invoice->issuer_address }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h5>Customer</h5>
                            @if ($invoice->customer_name)
                                <p class="mb-1"><strong>{{ $invoice->customer_name }}</strong></p>
                                @if ($invoice->customer_vat)
                                    <p class="mb-1 text-muted small">VAT: {{ $invoice->customer_vat }}</p>
                                @endif
                                @if ($invoice->customer_address)
                                    <p class="mb-0 text-muted small" style="white-space: pre-line;">{{ $invoice->customer_address }}</p>
                                @endif
                            @else
                                <p class="text-muted">No customer information available</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Line Items</h3>
                </div>
                <div class="card-body p-0">
                    @if ($invoice->lineItems->isEmpty())
                        <p class="text-muted text-center py-3">No line items found</p>
                    @else
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">VAT %</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->lineItems as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right">{{ $item->vat_rate !== null ? number_format($item->vat_rate, 0) . '%' : '-' }}</td>
                                        <td class="text-right"><strong>{{ number_format($item->line_total, 2) }}</strong></td>
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
                        <h3 class="card-title">Discounts</h3>
                    </div>
                    <div class="card-body">
                        @foreach ($invoice->discounts as $discount)
                            <div class="d-flex justify-content-between">
                                <span>{{ $discount->description }}</span>
                                <span class="text-danger">-{{ number_format($discount->amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Other Charges -->
            @if ($invoice->otherCharges->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Other Charges</h3>
                    </div>
                    <div class="card-body">
                        @foreach ($invoice->otherCharges as $charge)
                            <div class="d-flex justify-content-between">
                                <span>{{ $charge->description }}</span>
                                <span>{{ number_format($charge->amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Totals -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Totals</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    @if ($invoice->total_discounts > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Discounts</span>
                            <span class="text-danger">-{{ $invoice->currency }} {{ number_format($invoice->total_discounts, 2) }}</span>
                        </div>
                    @endif
                    @if ($invoice->total_other_charges > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span>Other Charges</span>
                            <span>{{ $invoice->currency }} {{ number_format($invoice->total_other_charges, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2">
                        <span>VAT Total</span>
                        <span>{{ $invoice->currency }} {{ number_format($invoice->vat_total, 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Grand Total</strong>
                        <strong class="text-lg">{{ $invoice->currency }} {{ number_format($invoice->grand_total, 2) }}</strong>
                    </div>

                    @if (!$invoice->totals_match)
                        <div class="alert alert-warning mt-3 mb-0">
                            <small><strong>Note:</strong> Calculated totals may not match due to rounding or additional fees not captured.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- File Preview -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Original Document</h3>
                </div>
                <div class="card-body">
                    @php
                        $extension = strtolower(pathinfo($invoice->file_path, PATHINFO_EXTENSION));
                    @endphp

                    @if ($extension === 'pdf')
                        <div style="height: 600px;">
                            <iframe src="{{ Storage::url($invoice->file_path) }}" class="w-100 h-100 border rounded" title="Invoice PDF"></iframe>
                        </div>
                        <a href="{{ Storage::url($invoice->file_path) }}" target="_blank" class="btn btn-link mt-2">
                            <i class="fas fa-external-link-alt mr-1"></i> Open PDF in new tab
                        </a>
                    @else
                        <img src="{{ Storage::url($invoice->file_path) }}" alt="Invoice" class="img-fluid rounded border">
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
