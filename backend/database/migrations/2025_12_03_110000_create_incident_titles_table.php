<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('incident_titles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category'); // Hardware, Software, Network, Account, Email, Security
            $table->string('priority')->default('Medium'); // Critical, High, Medium, Low
            $table->unsignedInteger('response_time')->default(8); // hours
            $table->unsignedInteger('resolution_time')->default(12); // hours
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('incident_titles');
    }
};
