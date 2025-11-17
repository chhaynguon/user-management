<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use Carbon\Carbon;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        Group::insert([
            ['code' => 'HQ', 'name' => 'Headquarters', 'description' => 'HQ group', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'SALES', 'name' => 'Sales', 'description' => 'Sales group', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
