<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserGroupSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_group')->insert([
            ['user_id' => 1, 'group_code' => 'HQ'],
            ['user_id' => 2, 'group_code' => 'SALES'],
        ]);
    }
}
