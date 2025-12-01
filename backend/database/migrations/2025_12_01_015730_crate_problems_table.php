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
        Schema::create('problems', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('status', ['Open', 'Investigating', 'Known Error', 'Resolved', 'Closed'])
                  ->default('Open');

            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])
                  ->default('Medium');

            $table->foreignId('assigned_to_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('root_cause')->nullable();
            $table->text('workaround')->nullable();
            $table->text('solution')->nullable();

            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
        });

        Schema::create('problem_incident', function (Blueprint $table) {
            $table->id();

            $table->foreignId('problem_id')
                ->constrained('problems')
                ->cascadeOnDelete();

            $table->foreignId('incident_id')
                ->constrained('incidents')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['problem_id', 'incident_id']);
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problem_incident');
        Schema::dropIfExists('problems');
        //
    }
};
