<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRoleHasPermissionsPrimaryKey extends Migration
{
    public function up()
    {
        Schema::table('role_has_permissions', function (Blueprint $table) {
            // Drop existing primary key
            $table->dropPrimary();

            // Create composite primary key: role_code + fnc_perm_code
            $table->primary(['role_code', 'fnc_perm_code']);
        });
    }

    public function down()
    {
        Schema::table('role_has_permissions', function (Blueprint $table) {
            // Drop composite primary key
            $table->dropPrimary();

            // Restore old primary key on fnc_perm_code
            $table->primary('fnc_perm_code');
        });
    }
}
