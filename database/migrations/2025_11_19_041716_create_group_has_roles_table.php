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
        Schema::create('group_has_roles', function (Blueprint $table) {
            $table->string('group_code', 50);
            $table->string('role_code', 50);
            $table->primary(['group_code', 'role_code']);
            $table->timestamps();

            $table->foreign('group_code')->references('code')->on('groups')->cascadeOnDelete();
            $table->foreign('role_code')->references('code')->on('roles')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_has_roles');
    }
};
