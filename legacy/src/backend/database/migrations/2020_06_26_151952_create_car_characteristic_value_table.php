<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarCharacteristicValueTable extends Migration
{
    public function up()
    {
        Schema::create('car_characteristic_value', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('car_id');
            $table->unsignedBigInteger('characteristic_id');
            $table->string('value');
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_characteristic_value');
    }
}
