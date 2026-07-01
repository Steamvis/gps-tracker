<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name_ru', 150);
            $table->string('name_en', 150);
            $table->char('code', 2);
            $table->string('flag_path');
        });
    }

    public function down()
    {
        Schema::dropIfExists('countries');
    }
}
