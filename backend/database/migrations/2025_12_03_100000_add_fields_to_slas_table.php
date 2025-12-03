<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('slas', function (Blueprint $table) {
            $table->text('description')->nullable()->after('resolution_time');
            $table->boolean('is_active')->default(true)->after('description');
        });
    }

    public function down()
    {
        Schema::table('slas', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_active']);
        });
    }
};
