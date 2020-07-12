<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarsForeignKeys extends Migration
{
    public function up()
    {
        Schema::table('cars', function (Blueprint $table) {
            $table
                ->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table
                ->foreign('mark_id')
                ->references('id')
                ->on('car_marks')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropUnique([
                'cars_api_code_unique'
            ]);
            $table->dropForeign([
                'cars_company_id_foreign',
                'cars_mark_id_foreign'
            ]);
            $table->dropIndex([
                'cars_api_code_unique',
                'cars_company_id_foreign',
                'cars_mark_id_foreign'
            ]);
        });
    }
}
