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
//                ->foreign('')
//                ->references('')
//                ->on('')
//                ->onDelete('');
//        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropUnique([
                    'companies_owner_id_unique'
                ]);
                $table->dropIndex([
                    'companies_owner_id_unique'
                ]);
            }
        });
    }
}
