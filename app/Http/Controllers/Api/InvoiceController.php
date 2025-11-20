<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceDiscount;
use App\Models\InvoiceLineItem;
use App\Models\InvoiceOtherCharge;
use App\Services\ClaudeInvoiceExtractor;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * List all invoices for the authenticated user's company.
     */
    public function index(Request $request): JsonResponse
    {
        // Global scope automatically filters by company
        $invoices = Invoice::orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Get a specific invoice with all related data.
     */
    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        // Global scope ensures invoice belongs to current company
        $invoice->load(['lineItems', 'discounts', 'otherCharges']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
                'issuer' => [
                    'name' => $invoice->issuer_name,
                    'vat_number' => $invoice->issuer_vat,
                    'address' => $invoice->issuer_address,
                ],
                'customer' => [
                    'name' => $invoice->customer_name,
                    'vat_number' => $invoice->customer_vat,
                    'address' => $invoice->customer_address,
                ],
                'currency' => $invoice->currency,
                'line_items' => $invoice->lineItems->map(fn($item) => [
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'vat_rate' => $item->vat_rate ? (float) $item->vat_rate : null,
                    'line_total' => (float) $item->line_total,
                ]),
                'discounts' => $invoice->discounts->map(fn($d) => [
                    'description' => $d->description,
                    'amount' => (float) $d->amount,
                ]),
                'other_charges' => $invoice->otherCharges->map(fn($c) => [
                    'description' => $c->description,
                    'amount' => (float) $c->amount,
                ]),
                'totals' => [
                    'subtotal' => (float) $invoice->subtotal,
                    'vat_total' => (float) $invoice->vat_total,
                    'grand_total' => (float) $invoice->grand_total,
                ],
                'file_url' => Storage::url($invoice->file_path),
                'created_at' => $invoice->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Upload and parse a new invoice.
     */
    public function store(Request $request, ClaudeInvoiceExtractor $extractor): JsonResponse
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,pdf',
            ],
        ]);

        // Check subscription and invoice limits
        $company = current_company();
        if ($company && !$request->user()->is_super_admin) {
            if (!$company->hasActiveSubscription()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription. Please subscribe to upload invoices.',
                    'code' => 'no_subscription',
                ], 403);
            }

            if (!$company->canCreateInvoice()) {
                $plan = $company->currentPlan();
                return response()->json([
                    'success' => false,
                    'message' => "Monthly invoice limit reached ({$plan->invoice_limit} invoices). Please upgrade your plan.",
                    'code' => 'invoice_limit_reached',
                    'usage' => [
                        'used' => $company->invoicesUsedThisMonth(),
                        'limit' => $plan->invoice_limit,
                        'remaining' => 0,
                    ],
                ], 403);
            }
        }

        $file = $request->file('file');
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

            $invoice->load(['lineItems', 'discounts', 'otherCharges']);

            // Increment invoice usage for the company
            if ($company && !$request->user()->is_super_admin) {
                $company->incrementInvoiceUsage();
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice parsed successfully',
                'data' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'grand_total' => (float) $invoice->grand_total,
                ],
            ], 201);

        } catch (Exception $e) {
            Storage::disk('public')->delete($path);

            Log::error('API Invoice processing failed', [
                'error' => $e->getMessage(),
                'file' => $originalFilename,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process invoice: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete an invoice.
     */
    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        // Global scope ensures invoice belongs to current company
        if ($invoice->file_path) {
            Storage::disk('public')->delete($invoice->file_path);
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully',
        ]);
    }
}
