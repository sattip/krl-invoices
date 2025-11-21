@extends('adminlte::page')

@section('title', 'All Invoices')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>All Invoices</h1>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-upload mr-2"></i> Upload Invoice
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
                    <h5 class="text-muted">No invoices yet</h5>
                    <p class="text-muted">Upload your first invoice to get started</p>
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                        <i class="fas fa-upload mr-2"></i> Upload Invoice
                    </a>
                </div>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Issuer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Uploaded</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td>
                                    <strong>{{ $invoice->invoice_number ?? 'N/A' }}</strong>
                                </td>
                                <td>{{ $invoice->issuer_name }}</td>
                                <td>{{ $invoice->invoice_date?->format('M d, Y') ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-success">
                                        {{ $invoice->currency ?? 'USD' }} {{ number_format($invoice->grand_total, 2) }}
                                    </span>
                                </td>
                                <td>{{ $invoice->created_at->diffForHumans() }}</td>
                                <td class="text-right">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
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
