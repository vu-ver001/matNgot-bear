<?php

namespace Database\Seeders;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Tạo Tài Khoản Admin, Staff & Customer
        $admin = User::firstOrCreate(
            ['email' => 'admin@matngot.com'],
            [
                'full_name' => 'Admin Mật Ngọt',
                'password' => Hash::make('password'),
                'role' => 'ADMIN',
                'status' => 'ACTIVE',
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer@matngot.com'],
            [
                'full_name' => 'Nguyễn Văn Khách',
                'phone' => '0987654321',
                'address' => '123 Đường Nguyễn Trãi, Quận 5, TP. Hồ Chí Minh',
                'password' => Hash::make('password'),
                'role' => 'CUSTOMER',
                'status' => 'ACTIVE',
            ]
        );

        // 2. Tạo Danh Mục Sản Phẩm
        $catTeddy = Category::firstOrCreate(['name' => 'Gấu Bông Teddy'], ['description' => 'Các loại gấu bông Teddy mềm mịn cao cấp', 'is_active' => true]);
        $catAnime = Category::firstOrCreate(['name' => 'Gấu Bông Hoạt Hình'], ['description' => 'Capybara, Pikachu, Shin và nhân vật hoạt hình dễ thương', 'is_active' => true]);
        $catPillow = Category::firstOrCreate(['name' => 'Gối Ôm Bông Mịn'], ['description' => 'Gối ôm dài 1m - 1m5 êm ái cho giấc ngủ ngon', 'is_active' => true]);

        // 3. Tạo Sản Phẩm Mẫu
        $p1 = Product::firstOrCreate(
            ['name' => 'Gấu Bông Teddy Choco Khổng Lồ 1m2'],
            [
                'category_id' => $catTeddy->id,
                'description' => 'Gấu bông Teddy Choco nhập khẩu chất lượng cao, nhồi bông PP 3D tinh khiết 100%, vỏ nhung mịn không rụng lông.',
                'price' => 450000,
                'sale_price' => 380000,
                'size' => '1m2',
                'color' => 'Nâu Choco',
                'material' => 'Vải nhung tuyết cao cấp',
                'stock_quantity' => 25,
                'status' => 'ACTIVE',
                'sold_count' => 120,
            ]
        );

        $p2 = Product::firstOrCreate(
            ['name' => 'Gấu Bông Capybara Đội Vịt Vàng Siêu Hài'],
            [
                'category_id' => $catAnime->id,
                'description' => 'Chú gấu Capybara hot trend năm 2026 kèm balo mũ vịt vàng siêu cưng.',
                'price' => 280000,
                'sale_price' => 220000,
                'size' => '45cm',
                'color' => 'Nâu đất',
                'material' => 'Bông gòn cao cấp',
                'stock_quantity' => 15,
                'status' => 'ACTIVE',
                'sold_count' => 85,
            ]
        );

        $p3 = Product::firstOrCreate(
            ['name' => 'Gối Ôm Dài Mèo Thần Tài May Mắn 1m'],
            [
                'category_id' => $catPillow->id,
                'description' => 'Gối ôm dài hình Mèo Thần Tài mang lại may mắn, mềm êm ru ngủ cực thích.',
                'price' => 320000,
                'sale_price' => null,
                'size' => '1m',
                'color' => 'Trắng Hồng',
                'material' => 'Bông PP mềm',
                'stock_quantity' => 8,
                'status' => 'ACTIVE',
                'sold_count' => 40,
            ]
        );

        // 4. Tạo Ảnh Sản Phẩm
        ProductImage::firstOrCreate(
            ['product_id' => $p1->id],
            ['image_url' => 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?w=600&q=80', 'is_primary' => true, 'sort_order' => 1]
        );
        ProductImage::firstOrCreate(
            ['product_id' => $p2->id],
            ['image_url' => 'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?w=600&q=80', 'is_primary' => true, 'sort_order' => 1]
        );
        ProductImage::firstOrCreate(
            ['product_id' => $p3->id],
            ['image_url' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&q=80', 'is_primary' => true, 'sort_order' => 1]
        );

        // 5. Thêm Sản Phẩm Mẫu Vào Giỏ Hàng của Customer
        CartItem::firstOrCreate(
            ['user_id' => $customer->id, 'product_id' => $p1->id],
            ['quantity' => 1]
        );
        CartItem::firstOrCreate(
            ['user_id' => $customer->id, 'product_id' => $p2->id],
            ['quantity' => 2]
        );

        // 6. Tạo Các Mã Voucher Mẫu (Phân Loại ORDER & SHIPPING)
        Voucher::updateOrCreate(
            ['code' => 'BEAR10'],
            [
                'voucher_type' => 'ORDER',
                'discount_type' => 'PERCENTAGE',
                'discount_value' => 10,
                'min_order_value' => 200000,
                'max_discount_value' => 50000,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(25),
                'usage_limit' => 100,
                'used_count' => 12,
                'status' => 'ACTIVE',
            ]
        );

        Voucher::updateOrCreate(
            ['code' => 'CHAOBANMOI'],
            [
                'voucher_type' => 'ORDER',
                'discount_type' => 'FIXED',
                'discount_value' => 30000,
                'min_order_value' => 150000,
                'max_discount_value' => null,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(60),
                'usage_limit' => 50,
                'used_count' => 8,
                'status' => 'ACTIVE',
            ]
        );

        Voucher::updateOrCreate(
            ['code' => 'FREESHIP30K'],
            [
                'voucher_type' => 'SHIPPING',
                'discount_type' => 'FIXED',
                'discount_value' => 30000,
                'min_order_value' => 100000,
                'max_discount_value' => null,
                'start_date' => now()->subDays(3),
                'end_date' => now()->addDays(45),
                'usage_limit' => 200,
                'used_count' => 35,
                'status' => 'ACTIVE',
            ]
        );

        Voucher::updateOrCreate(
            ['code' => 'SHIP50PCT'],
            [
                'voucher_type' => 'SHIPPING',
                'discount_type' => 'PERCENTAGE',
                'discount_value' => 50,
                'min_order_value' => 200000,
                'max_discount_value' => 20000,
                'start_date' => now()->subDays(1),
                'end_date' => now()->addDays(30),
                'usage_limit' => 80,
                'used_count' => 14,
                'status' => 'ACTIVE',
            ]
        );

        Voucher::updateOrCreate(
            ['code' => 'FLASHSALE50'],
            [
                'voucher_type' => 'ORDER',
                'discount_type' => 'PERCENTAGE',
                'discount_value' => 50,
                'min_order_value' => 300000,
                'max_discount_value' => 100000,
                'start_date' => now()->subDays(10),
                'end_date' => now()->subDay(),
                'usage_limit' => 20,
                'used_count' => 20,
                'status' => 'ACTIVE',
            ]
        );
    }
}
