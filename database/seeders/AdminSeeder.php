<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRoles;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user with the specified credentials
        User::create([
            'name' => 'Admin User',
            'email' => 'admin_def@mail',
            'password' => Hash::make('1234QWER'),
            'role' => UserRoles::ADMIN,
            'email_verified_at' => now(), // Admin is pre-verified
        ]);
    }
}
