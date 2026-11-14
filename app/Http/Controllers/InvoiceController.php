<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDiscount;
use App\Models\InvoiceLineItem;
use App\Models\InvoiceOtherCharge;
use App\Notifications\WhatsApp\InvoiceProcessed;
use App\Notifications\WhatsApp\InvoiceFailed;
use App\Notifications\WhatsApp\UsageWarning;
use App\Services\ClaudeInvoiceExtractor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::orderBy('created_at', 'desc')->paginate(15);
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('invoices.create');
    }

    public function store(Request $request, ClaudeInvoiceExtractor $extractor)
    {
        $request->validate([
            'invoice_files' => 'required|array|min:1',
            'invoice_files.*' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $company = current_company();

        // Users without a company cannot upload: their rows would belong to no tenant
        // and they would bypass the subscription/limit checks below.
        if (!$company && !$request->user()->is_super_admin) {
            return redirect()->route('dashboard')
                ->with('error', 'Ο λογαριασμός σας δεν είναι συνδεδεμένος με εταιρεία. Επικοινωνήστε με την υποστήριξη στο ' . config('legal.company.support_email') . '.');
        }
        // Local free trial (only when there is no active paid subscription): total limit and
        // end date are enforced per file with an atomic reservation below.
        $trial = ($company && !$request->user()->is_super_admin) ? $company->governingTrial() : null;

        if ($trial) {
            $denial = $trial->denial(count($request->file('invoice_files')));

            if ($denial) {
                return redirect()->route('billing.plans')->with('error', $denial['message']);
            }
        } elseif ($company && !$request->user()->is_super_admin) {
            if (!$company->hasActiveSubscription()) {
                return redirect()->route('billing.plans')
                    ->with('error', 'Επιλέξτε πακέτο συνδρομής για να ανεβάσετε τιμολόγια.');
            }

            $files = $request->file('invoice_files');
            $remaining = $company->invoicesRemainingThisMonth();

            if (count($files) > $remaining) {
                $plan = $company->currentPlan();
                return back()->with('error',
                    "Αυτόν τον μήνα μπορείτε να ανεβάσετε ακόμη {$remaining} τιμολόγιο/α. " .
                    "Το πακέτο {$plan->name} περιλαμβάνει {$plan->invoice_limit} τιμολόγια τον μήνα. " .
                    "Αναβαθμίστε το πακέτο σας για περισσότερα."
                );
            }
        }

        $files = $request->file('invoice_files');
        $processedCount = 0;
        $errors = [];
        $lastInvoice = null;

        foreach ($files as $file) {
            $originalFilename = $file->getClientOriginalName();

            // Trial: take the slot before any storage/extraction; fails closed when expired/full.
            if ($trial && !$trial->reserveSlot()) {
                $errors[] = $originalFilename . ': ' . ($trial->denial()['message'] ?? 'Η δωρεάν δοκιμή δεν επιτρέπει άλλα τιμολόγια.');
                continue;
            }

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

                $lastInvoice = $invoice;
                $processedCount++;

                // Send WhatsApp notification for processed invoice
                try {
                    $request->user()->notify(new InvoiceProcessed($invoice));
                } catch (Exception $e) {
                    Log::warning('WhatsApp notification failed for processed invoice', ['exception' => get_class($e), 'invoice_id' => $invoice->id]);
                }

                // Increment invoice usage for the company (paid plans; a trial slot is already taken)
                if ($company && !$request->user()->is_super_admin && !$trial) {
                    $company->incrementInvoiceUsage();
                    $this->checkUsageWarning($request->user(), $company);
                }

            } catch (Exception $e) {
                Storage::disk(Invoice::FILE_DISK)->delete($path);
                $trial?->releaseSlot();
                $safe = $e instanceof \App\Exceptions\InvoiceExtractionException;
                $errors[] = $originalFilename . ': ' . ($safe ? $e->getMessage() : 'Σφάλμα κατά την επεξεργασία. Δοκιμάστε ξανά.');

                // No exception message for unexpected errors: DB errors can carry invoice values.
                Log::error('Invoice processing failed', [
                    'exception' => get_class($e),
                    'error' => $safe ? $e->getMessage() : null,
                    'at' => basename($e->getFile()) . ':' . $e->getLine(),
                    'file' => $originalFilename,
                ]);

                // WhatsApp notification (live WaSenderAPI feature): only the safe user-facing text,
                // never the raw exception message (it can contain invoice values).
                try {
                    $request->user()->notify(new InvoiceFailed(
                        $originalFilename,
                        $safe ? $e->getMessage() : 'Σφάλμα κατά την επεξεργασία. Δοκιμάστε ξανά.'
                    ));
                } catch (Exception $notifyError) {
                    Log::warning('WhatsApp notification failed for processing error', ['exception' => get_class($notifyError)]);
                }
            }
        }

        if ($processedCount === 0) {
            return back()
                ->withInput()
                ->withErrors(['invoice_files' => 'Δεν ήταν δυνατή η επεξεργασία κανενός αρχείου. ' . implode('; ', $errors)]);
        }

        $message = $processedCount === 1
            ? 'Το τιμολόγιο επεξεργάστηκε και αποθηκεύτηκε. Ελέγξτε τα στοιχεία πριν τα χρησιμοποιήσετε.'
            : "Επεξεργάστηκαν και αποθηκεύτηκαν {$processedCount} τιμολόγια. Ελέγξτε τα στοιχεία πριν τα χρησιμοποιήσετε.";

        if (!empty($errors)) {
            $message .= ' Ορισμένα αρχεία απέτυχαν: ' . implode('; ', $errors);
        }

        if ($processedCount === 1 && $lastInvoice) {
            return redirect()->route('invoices.show', $lastInvoice)->with('success', $message);
        }

        return redirect()->route('invoices.index')->with('success', $message);
    }

    protected function checkUsageWarning($user, $company): void
    {
        $plan = $company->currentPlan();
        if (!$plan || $plan->invoice_limit <= 0) {
            return;
        }

        $used = $company->invoicesUsedThisMonth();
        $limit = $plan->invoice_limit;
        $percentage = round(($used / $limit) * 100);

        if ($percentage === 80) {
            try {
                $user->notify(new UsageWarning($used, $limit, $percentage));
            } catch (Exception $e) {
                Log::warning('WhatsApp usage warning notification failed', ['error' => $e->getMessage(), 'company_id' => $company->id]);
            }
        }
    }

    public function show(Request $request, Invoice $invoice)
    {
        $invoice->load(['lineItems', 'discounts', 'otherCharges']);
        return view('invoices.show', compact('invoice'));
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        // Global scope ensures invoice belongs to current company
        // Delete the file
        $invoice->deleteStoredFile();

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Το τιμολόγιο και το αρχείο του διαγράφηκαν.');
    }

    /**
     * Stream the original file to an authenticated user of the owning company.
     * (Route-model binding applies the company global scope, so other companies get 404.)
     */
    public function file(Request $request, Invoice $invoice)
    {
        $disk = $invoice->fileDisk();

        abort_if($disk === null, 404);

        $name = $invoice->original_filename ?: basename($invoice->file_path);

        return Storage::disk($disk)->response($invoice->file_path, $name, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }
}
