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
                'voucher_type' => 'ORDER',
                'apply_scope' => 'ALL',
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
                'voucher_type' => 'ORDER',
                'apply_scope' => 'ALL',
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
                'voucher_type' => 'ORDER',
                'apply_scope' => 'ALL',
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonth(),
                'usage_limit' => 30,
                'used_count' => 0,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'FREESHIP30K',
                'discount_type' => 'FIXED',
                'discount_value' => 30000,
                'min_order_value' => 0,
                'max_discount_value' => null,
                'voucher_type' => 'SHIPPING',
                'apply_scope' => 'ALL',
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonth(),
                'usage_limit' => 200,
                'used_count' => 0,
                'status' => 'ACTIVE',
            ],
            [
                'code' => 'SHIP50PCT',
                'discount_type' => 'PERCENTAGE',
                'discount_value' => 50,
                'min_order_value' => 150000,
                'max_discount_value' => 25000,
                'voucher_type' => 'SHIPPING',
                'apply_scope' => 'ALL',
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonth(),
                'usage_limit' => 150,
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
