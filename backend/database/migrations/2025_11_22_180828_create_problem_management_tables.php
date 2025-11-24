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
        Schema::create('problem_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('workaround')->nullable();
            $table->text('permanent_fix')->nullable();
            $table->foreignId('status_id')->constrained('problem_statuses');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('problem_incidents', function (Blueprint $table) {
            $table->foreignId('problem_id')->constrained('problems')->onDelete('cascade');
            $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
            $table->unique(['problem_id', 'incident_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problem_incidents');
        Schema::dropIfExists('problems');
        Schema::dropIfExists('problem_statuses');
    }
};
