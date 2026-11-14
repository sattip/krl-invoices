<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * One-off, idempotent move of legacy invoice uploads from the web-served "public"
 * disk (storage/app/public/invoices → https://…/storage/invoices/…) to the private
 * "local" disk. Dry-run by default; copies, verifies size + SHA-256, then deletes the
 * public copy. Run only after a storage backup (see docs/INVOICEAI_RELEASE.md).
 */
class PrivatizeInvoiceFiles extends Command
{
    protected $signature = 'invoices:privatize-files {--apply : Perform the move (default is a dry run)}';

    protected $description = 'Move invoice files from the public disk to the private disk';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $public = Storage::disk(Invoice::LEGACY_FILE_DISK);
        $private = Storage::disk(Invoice::FILE_DISK);
        $stats = ['public_only' => 0, 'moved' => 0, 'already_private' => 0, 'missing' => 0, 'failed' => 0];

        Invoice::withoutGlobalScopes()->whereNotNull('file_path')->orderBy('id')
            ->chunkById(200, function ($invoices) use ($apply, $public, $private, &$stats) {
                foreach ($invoices as $invoice) {
                    $path = $invoice->file_path;

                    if ($private->exists($path)) {
                        $stats['already_private']++;
                        if ($apply && $public->exists($path)
                            && hash_file('sha256', $public->path($path)) === hash_file('sha256', $private->path($path))) {
                            $public->delete($path);
                        }
                        continue;
                    }

                    if (! $public->exists($path)) {
                        $stats['missing']++;
                        continue;
                    }

                    $stats['public_only']++;
                    if (! $apply) {
                        continue;
                    }

                    $source = $public->path($path);
                    $hash = hash_file('sha256', $source);
                    $private->put($path, fopen($source, 'rb'));

                    if ($private->exists($path) && hash_file('sha256', $private->path($path)) === $hash) {
                        $public->delete($path);
                        $stats['moved']++;
                    } else {
                        $stats['failed']++;
                        $this->error("Verification failed for invoice #{$invoice->id}; public copy kept.");
                    }
                }
            });

        $this->table(array_keys($stats), [array_values($stats)]);
        $this->info($apply ? 'Done.' : 'Dry run only. Re-run with --apply to move files.');

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
