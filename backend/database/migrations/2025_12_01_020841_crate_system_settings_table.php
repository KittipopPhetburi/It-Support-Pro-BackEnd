<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();

            $table->string('category')->nullable();
            $table->string('key');
            $table->text('value');
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['category', 'key']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
};
