<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('code', 100)->unique()->after('id')->nullable();
        });

        Schema::table('functions', function (Blueprint $table) {
            $table->string('code', 100)->unique()->after('id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('code');
        });
        Schema::table('functions', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
