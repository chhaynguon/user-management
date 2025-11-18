<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_fnction_permission', function (Blueprint $table) {
            $table->string('role_code', 100);
            $table->string('fnction_code', 100);
            $table->string('permission_code', 100);

            // Foreign keys
            $table->foreign('role_code')
                ->references('code')->on('roles')
                ->cascadeOnDelete();

            $table->foreign('fnction_code')
                ->references('code')->on('fnctions')
                ->cascadeOnDelete();

            $table->foreign('permission_code')
                ->references('code')->on('permissions')
                ->cascadeOnDelete();

            // Composite PK
            $table->primary(['role_code', 'fnction_code', 'permission_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_fnction_permission');
    }
};
