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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->date('end_date')->nullable(); // สำหรับวันหยุดที่มีหลายวัน
            $table->string('type')->default('public'); // public, company, personal, other
            $table->text('description')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // สำหรับวันลาส่วนบุคคล
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
            
            // Index สำหรับการค้นหา
            $table->index('date');
            $table->index('type');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
