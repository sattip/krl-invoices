@extends('adminlte::page')

@section('title', 'Payment History')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Payment History</h1>
        <a href="{{ route('billing.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back to Billing
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stripeInvoices as $invoice)
                        <tr>
                            <td>{{ \Carbon\Carbon::createFromTimestamp($invoice->created)->format('M d, Y') }}</td>
                            <td>
                                @if($invoice->lines->data)
                                    {{ $invoice->lines->data[0]->description ?? 'Subscription' }}
                                @else
                                    Subscription
                                @endif
                            </td>
                            <td>${{ number_format($invoice->amount_paid / 100, 2) }}</td>
                            <td>
                                @if($invoice->status === 'paid')
                                    <span class="badge badge-success">Paid</span>
                                @elseif($invoice->status === 'open')
                                    <span class="badge badge-warning">Open</span>
                                @elseif($invoice->status === 'draft')
                                    <span class="badge badge-secondary">Draft</span>
                                @elseif($invoice->status === 'void')
                                    <span class="badge badge-dark">Void</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($invoice->status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($invoice->invoice_pdf)
                                    <a href="{{ $invoice->invoice_pdf }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <p class="text-muted mb-0">No payment history found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
