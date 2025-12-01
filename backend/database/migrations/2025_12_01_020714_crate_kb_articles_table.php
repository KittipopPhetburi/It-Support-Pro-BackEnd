<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kb_articles', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->longText('content');
            $table->string('category')->nullable();

            $table->json('tags')->nullable();

            $table->foreignId('author_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('helpful')->default(0);
            $table->unsignedBigInteger('not_helpful')->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kb_articles');
    }
};
