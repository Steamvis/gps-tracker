<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarCharacteristicsTable extends Migration
{
    public function up()
    {
        Schema::create('car_characteristics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name_ru');
            $table->string('name_en');
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_characteristics');
    }
}
