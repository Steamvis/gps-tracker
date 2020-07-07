<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarsRouteSectionsForeignKeys extends Migration
{
    public function up()
    {
        Schema::table('cars_route_sections', function (Blueprint $table) {
            $table
                ->foreign('route_id')
                ->references('id')
                ->on('cars_routes')
                ->onDelete('cascade');

//            $table
//                ->foreign('id')
//                ->references('section_id')
//                ->on('cars_points')
//                ->onDelete('cascade');

//            $table
//                ->foreign('end_point_id')
//                ->references('id')
//                ->on('cars_points')
//                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('cars_route_sections', function (Blueprint $table) {
            $table->dropForeign('cars_route_sections_start_point_foreign');
            $table->dropForeign('cars_route_sections_end_point_foreign');
        });
    }
}
