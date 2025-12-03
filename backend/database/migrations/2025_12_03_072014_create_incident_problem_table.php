<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_problem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_id')->constrained()->onDelete('cascade');
            $table->foreignId('incident_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['problem_id', 'incident_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_problem');
    }
};
