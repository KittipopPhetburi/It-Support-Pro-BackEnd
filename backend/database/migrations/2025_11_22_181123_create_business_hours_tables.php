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
        Schema::create('business_hours_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('timezone')->nullable();
            $table->timestamps();
        });

        Schema::create('business_hours_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_hours_profile_id')->constrained('business_hours_profiles')->onDelete('cascade');
            $table->integer('day_of_week'); // 1=Mon ... 7=Sun
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_hours_profile_id')->constrained('business_hours_profiles')->onDelete('cascade');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('business_hours_periods');
        Schema::dropIfExists('business_hours_profiles');
    }
};
