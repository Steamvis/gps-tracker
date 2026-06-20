<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarsRoutesForeignKeys extends Migration
{
    public function up()
    {
        Schema::table('cars_routes', function (Blueprint $table) {
            $table
                ->foreign('car_id')
                ->references('id')
                ->on('cars')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('cars_routes', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign([
                    'cars_routes_route_id_foreign'
                ]);
                $table->dropIndex([
                    'cars_routes_route_id_foreign'
                ]);
            }
        });
    }
}
