<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarsTable extends Migration
{
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('mark_id')->default('1');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('color', 7)->nullable();
            $table->string('name');
            $table->string('api_code',40)->unique();
            $table->char('year', 4)->nullable();
            $table->string('vin_number')->nullable();
            $table->string('gov_number')->nullable();
            $table->string('description')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cars');
    }
}
