<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarMarksTable extends Migration
{
    public function up()
    {
        Schema::create('car_marks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('country_id');
            $table->string('name');
            $table->string('mark_image_path');
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_marks');
    }
}
