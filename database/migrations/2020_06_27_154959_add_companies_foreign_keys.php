<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompaniesForeignKeys extends Migration
{
    public function up()
    {
//        Schema::table('companies', function (Blueprint $table) {
//            $table
//                ->foreign('id')
//                ->references('company_id')
//                ->on('cars')
//                ->onDelete('cascade');
//        });
    }

    public function down()
    {
//        Schema::table('companies', function (Blueprint $table) {
//            $table->dropForeign('companies_id_foreign');
//        });
    }
}
