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
        Schema::create('satisfaction_surveys', function (Blueprint $table) {
            $table->id();

            // เก็บเป็น string/bigint ตามที่ออกแบบฝั่ง backend
            $table->string('ticket_id');

            $table->unsignedTinyInteger('rating');
            $table->text('feedback')->nullable();

            $table->foreignId('respondent_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamp('submitted_at')->useCurrent();

            $table->timestamps();
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satisfaction_surveys');
        //
    }
};
