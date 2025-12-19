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
        Schema::create('user_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->string('user_name')->nullable()->comment('ชื่อผู้ใช้');
            $table->string('menu_name')->nullable()->comment('ชื่อเมนู');
            $table->boolean('can_view')->default(false)->comment('สิทธิ์ดู');
            $table->boolean('can_create')->default(false)->comment('สิทธิ์เพิ่ม');
            $table->boolean('can_update')->default(false)->comment('สิทธิ์แก้ไข');
            $table->boolean('can_delete')->default(false)->comment('สิทธิ์ลบ');
            $table->string('created_by_username')->nullable()->comment('ผู้กำหนด');
            $table->timestamps();

            $table->unique(['user_id', 'menu_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_menu_permissions');
    }
};
