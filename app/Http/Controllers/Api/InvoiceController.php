<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\TrialEntitlement;
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
                'file_url' => url('/api/invoices/'.$invoice->id.'/file'), // authenticated (Bearer token) download
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

        if (!$company && !$request->user()->is_super_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Ο λογαριασμός δεν είναι συνδεδεμένος με εταιρεία.',
                'code' => 'no_company',
            ], 403);
        }
        $trial = ($company && !$request->user()->is_super_admin) ? $company->governingTrial() : null;

        if ($trial) {
            // Local free trial: atomic reservation before storage/extraction (fails closed).
            if (!$trial->reserveSlot()) {
                $denial = $trial->denial() ?? ['code' => TrialEntitlement::CODE_LIMIT, 'message' => 'Η δωρεάν δοκιμή δεν επιτρέπει άλλα παραστατικά.'];

                return response()->json([
                    'success' => false,
                    'message' => $denial['message'],
                    'code' => $denial['code'],
                    'usage' => $trial->usage(),
                ], 403);
            }
        } elseif ($company && !$request->user()->is_super_admin) {
            if (!$company->hasActiveSubscription()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Δεν υπάρχει ενεργή συνδρομή. Εγγραφείτε σε πακέτο για να ανεβάσετε παραστατικά.',
                    'code' => 'no_subscription',
                ], 403);
            }

            if (!$company->canCreateInvoice()) {
                $plan = $company->currentPlan();
                return response()->json([
                    'success' => false,
                    'message' => "Συμπληρώθηκε το μηνιαίο όριο παραστατικών ({$plan->invoice_limit}). Αναβαθμίστε το πακέτο σας για να συνεχίσετε.",
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
        $path = $file->store('invoices', Invoice::FILE_DISK);

        try {
            $extractedData = $extractor->extract($path, Invoice::FILE_DISK);

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

            // Increment invoice usage for the company (paid plans; a trial slot is already taken)
            if ($company && !$request->user()->is_super_admin && !$trial) {
                $company->incrementInvoiceUsage();
            }

            return response()->json([
                'success' => true,
                'message' => 'Το παραστατικό αναλύθηκε επιτυχώς',
                'data' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'grand_total' => (float) $invoice->grand_total,
                ],
            ], 201);

        } catch (Exception $e) {
            Storage::disk(Invoice::FILE_DISK)->delete($path);
            $trial?->releaseSlot();

            $safe = $e instanceof \App\Exceptions\InvoiceExtractionException;

            Log::error('API Invoice processing failed', [
                'exception' => get_class($e),
                'error' => $safe ? $e->getMessage() : null,
                'at' => basename($e->getFile()) . ':' . $e->getLine(),
                'file' => $originalFilename,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Η επεξεργασία του παραστατικού απέτυχε: ' . ($safe ? $e->getMessage() : 'σφάλμα επεξεργασίας. Δοκιμάστε ξανά.'),
            ], 422);
        }
    }

    /**
     * Delete an invoice.
     */
    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        // Global scope ensures invoice belongs to current company
        $invoice->deleteStoredFile();

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Το παραστατικό διαγράφηκε',
        ]);
    }

    /**
     * Download the original file (company-scoped through route-model binding).
     */
    public function file(Request $request, Invoice $invoice)
    {
        $disk = $invoice->fileDisk();

        if ($disk === null) {
            return response()->json([
                'success' => false,
                'message' => 'Το αρχείο του παραστατικού δεν βρέθηκε.',
            ], 404);
        }

        return Storage::disk($disk)->response(
            $invoice->file_path,
            $invoice->original_filename ?: basename($invoice->file_path),
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'],
            'inline'
        );
    }
}
