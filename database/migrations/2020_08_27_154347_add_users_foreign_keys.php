<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsersForeignKeys extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table
                ->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique([
                'users_company_id_unique',
                'users_email_unique'
            ]);
            $table->dropForeign([
                'users_company_id_foreign'
            ]);
            $table->dropIndex([
                'users_company_id_unique',
                'users_email_unique'
            ]);
        });
    }
}
