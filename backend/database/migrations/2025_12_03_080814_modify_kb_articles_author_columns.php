<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            // Remove foreign key constraints first
            $table->dropForeign(['author_id']);
            $table->dropForeign(['created_by_id']);
            
            // Drop old columns
            $table->dropColumn(['author_id', 'created_by_id']);
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            // Add new string columns
            $table->string('author')->nullable()->after('tags');
            $table->string('created_by')->nullable()->after('author');
        });
    }

    public function down()
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropColumn(['author', 'created_by']);
        });

        Schema::table('kb_articles', function (Blueprint $table) {
            $table->foreignId('author_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }
};
