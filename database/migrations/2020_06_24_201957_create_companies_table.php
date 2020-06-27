<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('owner_id')->unique();
            $table->unsignedInteger('country_id');
            $table->unsignedBigInteger('cars_counter')->default(0);
            $table->unsignedBigInteger('staff_counter')->default(0);
            $table->string('logotype_path')->default('public/img/company/default-company.png');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
