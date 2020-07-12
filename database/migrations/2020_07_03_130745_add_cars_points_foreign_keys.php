<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarsPointsForeignKeys extends Migration
{
    public function up()
    {
        Schema::table(
            'cars_points',
            function (Blueprint $table) {
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

                $table
                    ->foreign('section_id')
                    ->references('id')
                    ->on('cars_route_sections')
                    ->onDelete('cascade');
            }
        );
    }

    public function down()
    {
        Schema::table('cars_points', function (Blueprint $table) {
            $table->dropIndex([
                'cars_points_car_id_foreign',
                'cars_points_route_id_foreign',
                'cars_points_section_id_foreign',

            ]);
            $table->dropForeign([
                'cars_points_car_id_foreign',
                'cars_points_route_id_foreign',
                'cars_points_section_id_foreign',
            ]);
        });
    }
}
