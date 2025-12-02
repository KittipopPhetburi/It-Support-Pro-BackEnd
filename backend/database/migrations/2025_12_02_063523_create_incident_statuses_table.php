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
    Schema::create('incident_statuses', function (Blueprint $table) {
        $table->id();
        $table->string('name');              // เปิดใหม่ / กำลังดำเนินการ / ปิดงาน
        $table->text('description')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('incident_statuses');
}

};
