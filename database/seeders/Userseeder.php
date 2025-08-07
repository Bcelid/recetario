<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'usuario')->first();

        User::insert([
            [
                'name' => 'Admin',
                'lastname' => 'Principal',
                'username' => 'admin',
                'phone' => '0999999999',
                'email' => 'admin@ejemplo.com',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Usuario',
                'lastname' => 'Normal',
                'username' => 'usuario',
                'phone' => '0888888888',
                'email' => 'usuario@ejemplo.com',
                'password' => Hash::make('usuario123'),
                'role_id' => $userRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
