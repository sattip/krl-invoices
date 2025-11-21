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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Make Stripe fields nullable for manual subscriptions
            $table->string('stripe_subscription_id')->nullable()->change();
            $table->string('stripe_customer_id')->nullable()->change();
            
            // Add fields for manual/grace period subscriptions
            $table->boolean('is_manual')->default(false)->after('status');
            $table->timestamp('grace_period_end')->nullable()->after('is_manual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['is_manual', 'grace_period_end']);
            
            // Note: Reverting nullable changes would require ensuring no null values exist
            $table->string('stripe_subscription_id')->nullable(false)->change();
            $table->string('stripe_customer_id')->nullable(false)->change();
        });
    }
};
