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
        });
    }

    public function down()
    {
        Schema::table('cars_route_sections', function (Blueprint $table) {
            $table->dropIndex([
                'cars_route_sections_route_id_foreign',
            ]);
            $table->dropForeign([
                'cars_route_sections_route_id_foreign',
            ]);
        });
    }
}
