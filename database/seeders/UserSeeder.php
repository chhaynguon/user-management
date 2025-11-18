<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin account
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),

        ]);
        // Normal user
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        DB::table('user_role')->insert([
            ['user_id' => $admin->id, 'role_code' => 'ADMIN'],
            ['user_id' => $user->id, 'role_code' => 'USER'],
        ]);
    }
}
