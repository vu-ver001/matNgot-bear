<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds for Admin, Staff and Customer.
     */
    public function run(): void
    {
        // 1. Admin
        User::updateOrCreate(
            ['email' => 'admin@matngotbear.com'],
            [
                'full_name' => 'Khánh Vân (Admin)',
                'password'  => Hash::make('password123'),
                'role'      => 'ADMIN',
                'status'    => 'ACTIVE',
                'phone'     => '0987654321',
                'address'   => 'Hà Nội',
            ]
        );

        // 2. Staff
        User::updateOrCreate(
            ['email' => 'staff@matngotbear.com'],
            [
                'full_name' => 'Nhân Viên Vận Hành',
                'password'  => Hash::make('password123'),
                'role'      => 'STAFF',
                'status'    => 'ACTIVE',
                'phone'     => '0988123456',
                'address'   => 'Hà Nội',
            ]
        );

        // 3. Customer
        User::updateOrCreate(
            ['email' => 'customer@matngotbear.com'],
            [
                'full_name' => 'Khách Hàng Thân Thiết',
                'password'  => Hash::make('password123'),
                'role'      => 'CUSTOMER',
                'status'    => 'ACTIVE',
                'phone'     => '0912345678',
                'address'   => 'TP. Hồ Chí Minh',
            ]
        );
    }
}
