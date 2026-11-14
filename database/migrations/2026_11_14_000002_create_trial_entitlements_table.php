<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Local (non-Stripe) free trial entitlement: one row per company, created only at
 * self-service registration. Additive: existing tables, subscriptions and plans are
 * not touched and nothing is backfilled.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trial_entitlements', function (Blueprint $table) {
            $table->id();
            // One trial per company, ever.
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            // Total (not monthly) number of invoices the trial allows.
            $table->unsignedInteger('invoice_limit');
            // Successfully processed + in-flight (reserved) invoices; never decremented by deleting an invoice.
            $table->unsignedInteger('invoices_used')->default(0);
            $table->timestamps();

            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trial_entitlements');
    }
};
