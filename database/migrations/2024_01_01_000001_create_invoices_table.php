<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();

            // Issuer information
            $table->string('issuer_name');
            $table->string('issuer_vat')->nullable();
            $table->text('issuer_address')->nullable();

            // Customer information
            $table->string('customer_name')->nullable();
            $table->string('customer_vat')->nullable();
            $table->text('customer_address')->nullable();

            // Totals
            $table->string('currency', 10)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('vat_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // File storage
            $table->string('file_path');
            $table->string('original_filename')->nullable();

            // Raw response for debugging
            $table->text('raw_response')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
