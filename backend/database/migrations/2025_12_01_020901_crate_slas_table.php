<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('slas', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('priority'); // จะเปลี่ยนเป็น enum ทีหลังก็ได้
            $table->unsignedInteger('response_time');   // ชั่วโมง
            $table->unsignedInteger('resolution_time'); // ชั่วโมง

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('slas');
    }
};
