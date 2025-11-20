<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Invoice Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Back to List') }}
                </a>
                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Invoice Data -->
                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Invoice Information</h3>
                            <dl class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->invoice_number ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Invoice Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->invoice_date?->format('Y-m-d') ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Currency</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->currency ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Original File</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->original_filename ?? 'Unknown' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Issuer & Customer -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Issuer</h3>
                                    <dl class="space-y-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $invoice->issuer_name }}</dd>
                                        </div>
                                        @if ($invoice->issuer_vat)
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">VAT Number</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ $invoice->issuer_vat }}</dd>
                                            </div>
                                        @endif
                                        @if ($invoice->issuer_address)
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Address</dt>
                                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $invoice->issuer_address }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer</h3>
                                    @if ($invoice->customer_name)
                                        <dl class="space-y-2">
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ $invoice->customer_name }}</dd>
                                            </div>
                                            @if ($invoice->customer_vat)
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">VAT Number</dt>
                                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->customer_vat }}</dd>
                                                </div>
                                            @endif
                                            @if ($invoice->customer_address)
                                                <div>
                                                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                                                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $invoice->customer_address }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    @else
                                        <p class="text-sm text-gray-500">No customer information available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Line Items</h3>
                            @if ($invoice->lineItems->isEmpty())
                                <p class="text-sm text-gray-500">No line items found</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">VAT %</th>
                                                <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($invoice->lineItems as $item)
                                                <tr>
                                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->description }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-500 text-right">{{ number_format($item->quantity, 2) }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-500 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-500 text-right">{{ $item->vat_rate !== null ? number_format($item->vat_rate, 0) . '%' : '-' }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">{{ number_format($item->line_total, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Discounts -->
                    @if ($invoice->discounts->isNotEmpty())
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Discounts</h3>
                                <div class="space-y-2">
                                    @foreach ($invoice->discounts as $discount)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">{{ $discount->description }}</span>
                                            <span class="text-red-600 font-medium">-{{ number_format($discount->amount, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Other Charges -->
                    @if ($invoice->otherCharges->isNotEmpty())
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Other Charges</h3>
                                <div class="space-y-2">
                                    @foreach ($invoice->otherCharges as $charge)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">{{ $charge->description }}</span>
                                            <span class="text-gray-900 font-medium">{{ number_format($charge->amount, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Totals -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Totals</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <dt class="text-gray-500">Subtotal</dt>
                                    <dd class="text-gray-900">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</dd>
                                </div>
                                @if ($invoice->total_discounts > 0)
                                    <div class="flex justify-between text-sm">
                                        <dt class="text-gray-500">Total Discounts</dt>
                                        <dd class="text-red-600">-{{ $invoice->currency }} {{ number_format($invoice->total_discounts, 2) }}</dd>
                                    </div>
                                @endif
                                @if ($invoice->total_other_charges > 0)
                                    <div class="flex justify-between text-sm">
                                        <dt class="text-gray-500">Other Charges</dt>
                                        <dd class="text-gray-900">{{ $invoice->currency }} {{ number_format($invoice->total_other_charges, 2) }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between text-sm">
                                    <dt class="text-gray-500">VAT Total</dt>
                                    <dd class="text-gray-900">{{ $invoice->currency }} {{ number_format($invoice->vat_total, 2) }}</dd>
                                </div>
                                <div class="flex justify-between text-sm font-bold border-t pt-2 mt-2">
                                    <dt class="text-gray-900">Grand Total</dt>
                                    <dd class="text-gray-900">{{ $invoice->currency }} {{ number_format($invoice->grand_total, 2) }}</dd>
                                </div>
                            </dl>

                            @if (!$invoice->totals_match)
                                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                    <p class="text-sm text-yellow-800">
                                        <strong>Note:</strong> Calculated totals may not match due to rounding or additional fees not captured.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- File Preview -->
                <div class="lg:sticky lg:top-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Original Document</h3>
                            @php
                                $extension = strtolower(pathinfo($invoice->file_path, PATHINFO_EXTENSION));
                            @endphp

                            @if ($extension === 'pdf')
                                <div class="aspect-[3/4] w-full">
                                    <iframe src="{{ Storage::url($invoice->file_path) }}" class="w-full h-full border rounded-md" title="Invoice PDF"></iframe>
                                </div>
                                <a href="{{ Storage::url($invoice->file_path) }}" target="_blank" class="mt-4 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                                    Open PDF in new tab
                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            @else
                                <img src="{{ Storage::url($invoice->file_path) }}" alt="Invoice" class="w-full rounded-md border">
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
