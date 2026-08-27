<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = [
            [
                'full_name' => 'Quản trị viên',
                'email' => 'admin@matngotbear.com',
                'phone' => '0912345678',
                'password' => 'password',
                'role' => 'ADMIN',
                'status' => 'ACTIVE',
                'address' => 'Hà Nội',
            ],
            [
                'full_name' => 'Nhân viên 1',
                'email' => 'staff1@matngotbear.com',
                'phone' => '0912345679',
                'password' => 'password',
                'role' => 'STAFF',
                'status' => 'ACTIVE',
                'address' => 'Hà Nội',
            ],
            [
                'full_name' => 'Nhân viên 2',
                'email' => 'staff2@matngotbear.com',
                'phone' => '0912345680',
                'password' => 'password',
                'role' => 'STAFF',
                'status' => 'ACTIVE',
                'address' => 'Hà Nội',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'full_name' => $user['full_name'],
                    'phone' => $user['phone'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                    'status' => $user['status'],
                    'address' => $user['address'],
                ]
            );
        }

        $customers = [
            ['Nguyễn Văn An', 'nguyenvana@example.com', '0981111111', 'Hà Nội'],
            ['Trần Thị Bình', 'tranthibinh@example.com', '0982222222', 'TP. Hồ Chí Minh'],
            ['Lê Văn Cường', 'levancuong@example.com', '0983333333', 'Đà Nẵng'],
            ['Phạm Thị Dung', 'phamthidung@example.com', '0984444444', 'Hải Phòng'],
            ['Hoàng Văn Em', 'hoangvanem@example.com', '0985555555', 'Cần Thơ'],
        ];

        foreach ($customers as [$name, $email, $phone, $address]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'full_name' => $name,
                    'phone' => $phone,
                    'password' => Hash::make('password'),
                    'role' => 'CUSTOMER',
                    'status' => 'ACTIVE',
                    'address' => $address,
                ]
            );
        }
    }
}
