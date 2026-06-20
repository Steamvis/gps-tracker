<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarsPointsTable extends Migration
{
    public function up()
    {
        Schema::create('cars_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('car_id');
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('section_id')->nullable();;
            $table->double('latitude');
            $table->double('longitude');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cars_points');
    }
}
