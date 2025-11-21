<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // API Configuration
            $table->text('api_key')->nullable();
            $table->text('webhook_secret')->nullable();
            $table->string('session_id')->nullable();
            
            // Phone Number
            $table->string('phone_number')->nullable();
            $table->string('phone_display_name')->nullable();
            
            // Status
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_connected')->default(false);
            $table->string('connection_status')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            
            // Settings
            $table->json('notification_settings')->nullable();
            $table->json('message_templates')->nullable();
            $table->json('account_details')->nullable();
            
            $table->timestamps();
            
            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_settings');
    }
};
