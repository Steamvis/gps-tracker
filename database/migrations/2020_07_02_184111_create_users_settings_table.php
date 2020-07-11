<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('users_settings',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedInteger('setting_id');
                $table->text('value');
            });
    }

    public function down()
    {
        Schema::dropIfExists('users_settings');
    }
}