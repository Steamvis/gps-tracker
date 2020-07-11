<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create(
            'settings',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->string('translate_en');
                $table->string('translate_ru');
                $table->string('type');
                $table->text('value_variants')->nullable();;
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}