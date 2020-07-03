<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarsRoutesTable extends Migration
{
    public function up()
    {
        Schema::create('cars_routes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('car_id');
            $table->unsignedBigInteger('start_point_id');
            $table->unsignedBigInteger('end_point_id');
            $table->string('moving_time_ru');
            $table->string('moving_time_en');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cars_routes');
    }
}
