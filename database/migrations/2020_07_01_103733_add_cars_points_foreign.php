<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarsPointsForeign extends Migration
{
    public function up()
    {
        Schema::table('cars_points', function (Blueprint $table) {
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
            $table->dropForeign('cars_points_car_id_foreign');
        });
    }
}
