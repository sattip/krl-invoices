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
        Schema::table('stripe_settings', function (Blueprint $table) {
            $table->string('stripe_user_id')->nullable()->after('stripe_webhook_secret');
            $table->text('stripe_access_token')->nullable()->after('stripe_user_id');
            $table->text('stripe_refresh_token')->nullable()->after('stripe_access_token');
            $table->string('livemode')->nullable()->after('stripe_refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stripe_settings', function (Blueprint $table) {
            $table->dropColumn(['stripe_user_id', 'stripe_access_token', 'stripe_refresh_token', 'livemode']);
        });
    }
};
