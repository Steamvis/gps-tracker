<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPointsSectionForeignKeys extends Migration
{
    public function up()
    {
        Schema::table('points_section', function (Blueprint $table) {
            $table
                ->foreign('point_id')
                ->references('id')
                ->on('cars_points')
                ->onDelete('cascade');

            $table
                ->foreign('section_id')
                ->references('id')
                ->on('cars_route_sections')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('points_section', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropIndex([
                    'points_section_point_id_foreign',
                    'points_section_section_id_foreign',
                ]);
                $table->dropForeign([
                    'points_section_point_id_foreign',
                    'points_section_section_id_foreign',
                ]);
            }
        });
    }
}
