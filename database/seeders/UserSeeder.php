<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Super Admin',
                'email'    => 'superadmin@warkop.com',
                'password' => Hash::make('password'),
                'phone'    => '081234567890',
                'pin'      => '000000',
                'is_active'=> true,
                'role'     => 'super_admin',
            ],
            [
                'name'     => 'Admin Warkop',
                'email'    => 'admin@warkop.com',
                'password' => Hash::make('password'),
                'phone'    => '081234567891',
                'pin'      => '111111',
                'is_active'=> true,
                'role'     => 'admin',
            ],
            [
                'name'     => 'Kasir Satu',
                'email'    => 'kasir@warkop.com',
                'password' => Hash::make('password'),
                'phone'    => '081234567892',
                'pin'      => '123456',
                'is_active'=> true,
                'role'     => 'kasir',
            ],
            [
                'name'     => 'Barista Kopi',
                'email'    => 'barista@warkop.com',
                'password' => Hash::make('password'),
                'phone'    => '081234567893',
                'pin'      => '654321',
                'is_active'=> true,
                'role'     => 'barista',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::updateOrCreate(['email' => $data['email']], $data);
            
            // Assign roles for both web and sanctum guards to ensure compatibility
            // We set the guard_name on the model instance before assigning each role
            $user->guard_name = 'web';
            $user->assignRole($role);
            
            $user->guard_name = 'sanctum';
            $user->assignRole($role);
        }

        $this->command->info('Users seeded successfully!');
    }
}
