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
        Schema::create('user_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->string('fnction_code', 50);
            $table->string('permission_code', 50);
            $table->string('fnc_perm_code', 50)->nullable();
            $table->primary(['user_id', 'fnc_perm_code']);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('fnction_code')->references('code')->on('fnctions')->cascadeOnDelete();
            $table->foreign('permission_code')->references('code')->on('permissions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_has_permissions');
    }
};
