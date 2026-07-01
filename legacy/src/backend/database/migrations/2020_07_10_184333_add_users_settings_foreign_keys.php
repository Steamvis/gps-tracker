<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsersSettingsForeignKeys extends Migration
{
    public function up()
    {
        Schema::table('users_settings',
            function (Blueprint $table) {
                $table
                    ->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table
                    ->foreign('setting_id')
                    ->references('id')
                    ->on('settings')
                    ->onDelete('cascade');
            });
    }

    public function down()
    {
        Schema::table('users_settings', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropIndex([
                    'users_settings_setting_id_foreign',
                    'users_settings_user_id_foreign'
                ]);
                $table->dropForeign([
                    'users_settings_setting_id_foreign',
                    'users_settings_user_id_foreign'
                ]);
            }
        });
    }
}
