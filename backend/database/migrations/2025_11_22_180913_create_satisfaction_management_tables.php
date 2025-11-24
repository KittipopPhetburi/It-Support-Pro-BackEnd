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
        Schema::create('satisfaction_questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('satisfaction_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satisfaction_questionnaire_id')->constrained('satisfaction_questionnaires')->onDelete('cascade');
            $table->text('question_text');
            $table->string('question_type');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('satisfaction_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('satisfaction_questionnaire_id')->constrained('satisfaction_questionnaires');
            $table->integer('overall_score')->nullable();
            $table->text('comment')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('satisfaction_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satisfaction_response_id')->constrained('satisfaction_responses')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('satisfaction_questions')->onDelete('cascade');
            $table->integer('rating_value')->nullable();
            $table->text('text_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satisfaction_answers');
        Schema::dropIfExists('satisfaction_responses');
        Schema::dropIfExists('satisfaction_questions');
        Schema::dropIfExists('satisfaction_questionnaires');
    }
};
