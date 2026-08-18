<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $vouchers = [
            [
                'code' => 'WELCOME10',
                'discount_type' => 'PERCENTAGE',
                'discount_value' => 10,
                'min_order_value' => 200000,
                'max_discount_value' => 100000,
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonth(),
                'usage_limit' => 100,
                'used_count' => 0,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'GIAM50K',
                'discount_type' => 'FIXED',
                'discount_value' => 50000,
                'min_order_value' => 300000,
                'max_discount_value' => null,
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonth(),
                'usage_limit' => 50,
                'used_count' => 0,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'SALE20',
                'discount_type' => 'PERCENTAGE',
                'discount_value' => 20,
                'min_order_value' => 500000,
                'max_discount_value' => 200000,
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonth(),
                'usage_limit' => 30,
                'used_count' => 0,
                'status' => 'ACTIVE',
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::updateOrCreate(
                ['code' => $voucher['code']],
                $voucher
            );
        }
    }
}
