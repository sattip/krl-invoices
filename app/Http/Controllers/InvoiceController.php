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
        // Global scope automatically filters by company
        $invoices = Invoice::orderBy('created_at', 'desc')
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
     * Store newly uploaded invoices.
     */
    public function store(Request $request, ClaudeInvoiceExtractor $extractor)
    {
        $request->validate([
            'invoice_files' => 'required|array|min:1',
            'invoice_files.*' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,pdf',
            ],
        ]);

        // Check subscription and invoice limits
        $company = current_company();
        if ($company && !$request->user()->is_super_admin) {
            if (!$company->hasActiveSubscription()) {
                return redirect()->route('billing.plans')
                    ->with('error', 'Please subscribe to a plan to upload invoices.');
            }

            $files = $request->file('invoice_files');
            $remaining = $company->invoicesRemainingThisMonth();

            if (count($files) > $remaining) {
                $plan = $company->currentPlan();
                return back()->with('error',
                    "You can only upload {$remaining} more invoice(s) this month. " .
                    "Your {$plan->name} plan allows {$plan->invoice_limit} invoices per month. " .
                    "Please upgrade your plan for more capacity."
                );
            }
        }

        $files = $request->file('invoice_files');
        $processedCount = 0;
        $errors = [];
        $lastInvoice = null;

        foreach ($files as $file) {
            $originalFilename = $file->getClientOriginalName();
            $path = $file->store('invoices', 'public');

            try {
                $extractedData = $extractor->extract($path);

                $invoice = DB::transaction(function () use ($extractedData, $path, $originalFilename, $request) {
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

                    if (!empty($extractedData['discounts'])) {
                        foreach ($extractedData['discounts'] as $discount) {
                            InvoiceDiscount::create([
                                'invoice_id' => $invoice->id,
                                'description' => $discount['description'],
                                'amount' => $discount['amount'] ?? 0,
                            ]);
                        }
                    }

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

                $lastInvoice = $invoice;
                $processedCount++;

                // Increment invoice usage for the company
                if ($company && !$request->user()->is_super_admin) {
                    $company->incrementInvoiceUsage();
                }

            } catch (Exception $e) {
                Storage::disk('public')->delete($path);
                $errors[] = "{$originalFilename}: {$e->getMessage()}";

                Log::error('Invoice processing failed', [
                    'error' => $e->getMessage(),
                    'file' => $originalFilename,
                ]);
            }
        }

        if ($processedCount === 0) {
            return back()
                ->withInput()
                ->withErrors(['invoice_files' => 'Failed to process any invoices. ' . implode('; ', $errors)]);
        }

        $message = $processedCount === 1
            ? 'Invoice parsed and saved successfully!'
            : "{$processedCount} invoices parsed and saved successfully!";

        if (!empty($errors)) {
            $message .= ' Some files failed: ' . implode('; ', $errors);
        }

        if ($processedCount === 1 && $lastInvoice) {
            return redirect()
                ->route('invoices.show', $lastInvoice)
                ->with('success', $message);
        }

        return redirect()
            ->route('invoices.index')
            ->with('success', $message);
    }

    /**
     * Display the specified invoice.
     */
    public function show(Request $request, Invoice $invoice)
    {
        // Global scope ensures invoice belongs to current company
        // Load relationships
        $invoice->load(['lineItems', 'discounts', 'otherCharges']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Request $request, Invoice $invoice)
    {
        // Global scope ensures invoice belongs to current company
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
