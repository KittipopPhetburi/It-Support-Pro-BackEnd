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
    Schema::create('incident_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');              // ชื่อประเภท เช่น ทั่วไป, ระบบเครือข่าย
        $table->text('description')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('incident_categories');
}

};
