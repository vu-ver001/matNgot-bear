<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@matngotbear.test'],
            [
                'full_name' => 'Quản trị Mật Ngọt Bear',
                'email_verified_at' => now(),
                'phone' => '0901000001',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'address' => '12 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh',
            ],
        );
    }
}
