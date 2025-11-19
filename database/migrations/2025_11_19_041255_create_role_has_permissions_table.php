<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->string('role_code', 50);
            $table->string('fnction_code', 50);
            $table->string('permission_code', 50);
            $table->string('fnc_perm_code', 50)->primary();
            $table->timestamps();

            $table->foreign('role_code')->references('code')->on('roles')->cascadeOnDelete();
            $table->foreign('fnction_code')->references('code')->on('fnctions')->cascadeOnDelete();
            $table->foreign('permission_code')->references('code')->on('permissions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
    }
};
