<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDiscount;
use App\Models\InvoiceLineItem;
use App\Models\InvoiceOtherCharge;
use App\Services\ClaudeInvoiceExtractor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $invoices = Invoice::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        return view('invoices.create');
    }

    /**
     * Store a newly uploaded invoice.
     */
    public function store(Request $request, ClaudeInvoiceExtractor $extractor)
    {
        $request->validate([
            'invoice_file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,pdf',
            ],
        ]);

        $file = $request->file('invoice_file');
        $originalFilename = $file->getClientOriginalName();

        // Store the file
        $path = $file->store('invoices', 'public');

        try {
            // Extract data using Claude
            $extractedData = $extractor->extract($path);

            // Save invoice and related data in a transaction
            $invoice = DB::transaction(function () use ($extractedData, $path, $originalFilename, $request) {
                // Create the invoice
                $invoice = Invoice::create([
                    'user_id' => $request->user()->id,
                    'invoice_number' => $extractedData['invoice_number'] ?? null,
                    'invoice_date' => $extractedData['invoice_date'] ?? null,
                    'issuer_name' => $extractedData['issuer']['name'],
                    'issuer_vat' => $extractedData['issuer']['vat_number'] ?? null,
                    'issuer_address' => $extractedData['issuer']['address'] ?? null,
                    'customer_name' => $extractedData['customer']['name'] ?? null,
                    'customer_vat' => $extractedData['customer']['vat_number'] ?? null,
                    'customer_address' => $extractedData['customer']['address'] ?? null,
                    'currency' => $extractedData['currency'] ?? null,
                    'subtotal' => $extractedData['totals']['subtotal'] ?? 0,
                    'vat_total' => $extractedData['totals']['vat_total'] ?? 0,
                    'grand_total' => $extractedData['totals']['grand_total'] ?? 0,
                    'file_path' => $path,
                    'original_filename' => $originalFilename,
                    'raw_response' => $extractedData['raw_response'] ?? null,
                ]);

                // Create line items
                if (!empty($extractedData['line_items'])) {
                    foreach ($extractedData['line_items'] as $item) {
                        InvoiceLineItem::create([
                            'invoice_id' => $invoice->id,
                            'description' => $item['description'],
                            'quantity' => $item['quantity'] ?? 1,
                            'unit_price' => $item['unit_price'] ?? 0,
                            'vat_rate' => $item['vat_rate'] ?? null,
                            'line_total' => $item['line_total'] ?? 0,
                        ]);
                    }
                }

                // Create discounts
                if (!empty($extractedData['discounts'])) {
                    foreach ($extractedData['discounts'] as $discount) {
                        InvoiceDiscount::create([
                            'invoice_id' => $invoice->id,
                            'description' => $discount['description'],
                            'amount' => $discount['amount'] ?? 0,
                        ]);
                    }
                }

                // Create other charges
                if (!empty($extractedData['other_charges'])) {
                    foreach ($extractedData['other_charges'] as $charge) {
                        InvoiceOtherCharge::create([
                            'invoice_id' => $invoice->id,
                            'description' => $charge['description'],
                            'amount' => $charge['amount'] ?? 0,
                        ]);
                    }
                }

                return $invoice;
            });

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Invoice parsed and saved successfully!');

        } catch (Exception $e) {
            // Delete the uploaded file if processing failed
            Storage::disk('public')->delete($path);

            Log::error('Invoice processing failed', [
                'error' => $e->getMessage(),
                'file' => $originalFilename,
            ]);

            return back()
                ->withInput()
                ->withErrors(['invoice_file' => 'Failed to process invoice: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Request $request, Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== $request->user()->id) {
            abort(403);
        }

        // Load relationships
        $invoice->load(['lineItems', 'discounts', 'otherCharges']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Request $request, Invoice $invoice)
    {
        // Ensure user owns this invoice
        if ($invoice->user_id !== $request->user()->id) {
            abort(403);
        }

        // Delete the file
        if ($invoice->file_path) {
            Storage::disk('public')->delete($invoice->file_path);
        }

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice deleted successfully!');
    }
}
