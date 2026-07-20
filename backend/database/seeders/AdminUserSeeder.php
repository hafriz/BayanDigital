<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            $this->command?->warn('ADMIN_EMAIL or ADMIN_PASSWORD is missing; initial administrator was not created.');

            return;
        }

        User::query()->firstOrCreate(
            ['email' => strtolower($email)],
            [
                'name' => config('admin.name', 'bayanDigital Administrator'),
                'password' => $password,
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ],
        );
    }
}
