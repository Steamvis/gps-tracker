<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarsRoutesForeign extends Migration
{
    public function up()
    {
        Schema::table('cars_routes', function (Blueprint $table) {
            $table
                ->foreign('car_id')
                ->references('id')
                ->on('cars')
                ->onDelete('cascade');
            $table
                ->foreign('start_point_id')
                ->references('id')
                ->on('cars_points')
                ->onDelete('cascade');
            $table
                ->foreign('end_point_id')
                ->references('id')
                ->on('cars_points')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('cars_routes', function (Blueprint $table) {
            $table->dropForeign('cars_routes_start_point_foreign');
            $table->dropForeign('cars_routes_end_point_foreign');
        });
    }
}
