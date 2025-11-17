<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('group_role')->insert([
            ['group_code' => 'HQ', 'role_code' => 'ADMIN'],
            ['group_code' => 'SALES', 'role_code' => 'MANAGER'],
        ]);
    }
}
