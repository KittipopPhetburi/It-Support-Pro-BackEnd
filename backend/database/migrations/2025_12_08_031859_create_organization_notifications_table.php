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
        Schema::create('organization_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('request_type');
            $table->boolean('email_enabled')->default(false);
            $table->text('email_recipients')->nullable();
            $table->boolean('telegram_enabled')->default(false);
            $table->string('telegram_token')->nullable();
            $table->string('telegram_chat_id')->nullable();
            $table->boolean('line_enabled')->default(false);
            $table->string('line_token')->nullable();
            $table->timestamps();
            
            $table->unique(['organization_name', 'request_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_notifications');
    }
};
