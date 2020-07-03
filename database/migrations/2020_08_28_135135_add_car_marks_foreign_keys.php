<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarMarksForeignKeys extends Migration
{
    public function up()
    {
        Schema::table('car_marks', function (Blueprint $table) {
            $table
                ->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('car_marks', function (Blueprint $table) {
            //
        });
    }
}
