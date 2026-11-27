<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Petugas Demo',
                'username' => 'petugas@simship.com',
                'email' => 'petugas@simship.com',
                'role' => User::ROLE_PETUGAS,
            ],
            [
                'name' => 'Admin Demo',
                'username' => 'admin@simship.com',
                'email' => 'admin@simship.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Direksi Demo',
                'username' => 'direksi@simship.com',
                'email' => 'direksi@simship.com',
                'role' => User::ROLE_DIREKSI,
            ],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'username' => $account['username'],
                    'role' => $account['role'],
                    'password' => 'password',
                    'is_active' => true,
                ]
            );
        }
    }
}