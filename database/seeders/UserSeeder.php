<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'full_name' => 'Nhân viên Minh Anh',
                'email' => 'staff@matngotbear.test',
                'phone' => '0902000002',
                'role' => User::ROLE_STAFF,
                'status' => User::STATUS_ACTIVE,
                'address' => '25 Lê Lợi, Quận 1, TP. Hồ Chí Minh',
            ],
            [
                'full_name' => 'Nguyễn Mật Ong',
                'email' => 'customer@matngotbear.test',
                'phone' => '0903000003',
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
                'address' => '88 Nguyễn Trãi, Quận 5, TP. Hồ Chí Minh',
            ],
            [
                'full_name' => 'Trần Gia Linh',
                'email' => 'linh@matngotbear.test',
                'phone' => '0904000004',
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
                'address' => '16 Võ Văn Tần, Quận 3, TP. Hồ Chí Minh',
            ],
            [
                'full_name' => 'Khách hàng bị khóa',
                'email' => 'blocked@matngotbear.test',
                'phone' => '0905000005',
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_BLOCKED,
                'address' => 'TP. Hồ Chí Minh',
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    ...$user,
                    'email_verified_at' => now(),
                    'password' => 'password',
                ],
            );
        }
    }
}
