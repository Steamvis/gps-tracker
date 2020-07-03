<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarsPointsForeignKeys extends Migration
{
    public function up()
    {
        Schema::table('cars_points', function (Blueprint $table) {
            $table
                ->foreign('route_id')
                ->references('id')
                ->on('cars_routes')
                ->onDelete('cascade');

            $table
                ->foreign('car_id')
                ->references('id')
                ->on('cars')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('cars_points', function (Blueprint $table) {
            //
        });
    }
}
