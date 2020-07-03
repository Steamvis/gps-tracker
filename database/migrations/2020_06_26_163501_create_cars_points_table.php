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
            $table->double('latitude');
            $table->double('longitude');
            $table->string('start_route_time')->nullable();
            $table->string('end_route_time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cars_points');
    }
}
