<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sub_contractors', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('company');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->json('specialization')->nullable(); // string[]

            $table->enum('status', ['Active', 'Inactive'])
                  ->default('Active');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_contractors');
    }
};
