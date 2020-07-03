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
            $table->string('name');
            $table->string('start_time');
            $table->string('end_time')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cars_routes');
    }
}
