<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePointsSectionTable extends Migration
{
    public function up()
    {
        Schema::create('points_section', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('point_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('points_section');
    }
}
