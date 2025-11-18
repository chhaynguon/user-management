<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $userAdmin = User::where('email', 'admin@example.com')->first();
        $userUser = User::where('email', 'user@example.com')->first();

        $roleAdmin = Role::where('code', 'ADMIN')->first();
        $roleUser = Role::where('code', 'USER')->first();

        DB::table('user_role')->insert([
            ['user_id' => $userAdmin->id, 'role_id' => $roleAdmin->id],
            ['user_id' => $userUser->id, 'role_id' => $roleUser->id],
        ]);
    }
}
