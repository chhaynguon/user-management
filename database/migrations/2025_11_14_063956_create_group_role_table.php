<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_role', function (Blueprint $table) {
            $table->string('group_code', 100);
            $table->string('role_code', 100);

            $table->foreign('group_code')
                ->references('code')->on('groups')
                ->cascadeOnDelete();

            $table->foreign('role_code')
                ->references('code')->on('roles')
                ->cascadeOnDelete();

            $table->primary(['group_code', 'role_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_role');
    }
};
