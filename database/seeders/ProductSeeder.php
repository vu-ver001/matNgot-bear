<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'category' => 'Gấu bông',
                'name' => 'Gấu Teddy Mật Ong 45cm',
                'description' => 'Gấu Teddy màu nâu mật ong, lông ngắn mềm và dễ vệ sinh.',
                'price' => 349000,
                'sale_price' => 299000,
                'size' => '45cm',
                'color' => 'Nâu mật ong',
                'material' => 'Lông nhung, bông PP',
                'stock_quantity' => 25,
                'status' => Product::STATUS_ACTIVE,
                'sold_count' => 128,
                'images' => ['/images/auth/bear-hero.png'],
            ],
            [
                'category' => 'Gấu bông',
                'name' => 'Gấu Dâu Hồng 60cm',
                'description' => 'Gấu bông hồng pastel ôm trái dâu, phù hợp làm quà tặng.',
                'price' => 429000,
                'sale_price' => null,
                'size' => '60cm',
                'color' => 'Hồng pastel',
                'material' => 'Lông mịn, bông PP',
                'stock_quantity' => 0,
                'status' => Product::STATUS_ACTIVE,
                'sold_count' => 86,
                'images' => ['/images/auth/auth-panel-background.png'],
            ],
            [
                'category' => 'Gấu bông',
                'name' => 'Gấu Nâu Cổ Điển 80cm',
                'description' => 'Mẫu gấu nâu dáng ngồi cổ điển với chiếc nơ caro.',
                'price' => 659000,
                'sale_price' => 599000,
                'size' => '80cm',
                'color' => 'Nâu cacao',
                'material' => 'Lông nhung cao cấp, bông PP',
                'stock_quantity' => 12,
                'status' => Product::STATUS_ACTIVE,
                'sold_count' => 54,
                'images' => ['/images/auth/bear-hero.png'],
            ],
            [
                'category' => 'Thú bông',
                'name' => 'Thỏ Bông Kem 40cm',
                'description' => 'Thỏ bông tai dài màu kem, nhẹ và êm ái.',
                'price' => 249000,
                'sale_price' => null,
                'size' => '40cm',
                'color' => 'Kem',
                'material' => 'Lông mịn, bông PP',
                'stock_quantity' => 31,
                'status' => Product::STATUS_ACTIVE,
                'sold_count' => 73,
                'images' => ['/images/auth/auth-panel-background.png'],
            ],
            [
                'category' => 'Thú bông',
                'name' => 'Capybara Đội Quýt 35cm',
                'description' => 'Capybara dáng nằm với chiếc mũ quả quýt có thể tháo rời.',
                'price' => 279000,
                'sale_price' => 259000,
                'size' => '35cm',
                'color' => 'Nâu sáng',
                'material' => 'Lông co giãn, bông PP',
                'stock_quantity' => 18,
                'status' => Product::STATUS_ACTIVE,
                'sold_count' => 101,
                'images' => ['/images/auth/bear-hero.png'],
            ],
            [
                'category' => 'Bộ sưu tập giới hạn',
                'name' => 'Gấu Noel Phiên Bản Giới Hạn',
                'description' => 'Gấu bông mặc trang phục Noel thuộc bộ sưu tập mùa lễ hội.',
                'price' => 399000,
                'sale_price' => null,
                'size' => '50cm',
                'color' => 'Đỏ - trắng',
                'material' => 'Lông nhung, bông PP',
                'stock_quantity' => 5,
                'status' => Product::STATUS_INACTIVE,
                'sold_count' => 42,
                'images' => [],
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::query()
                ->where('name', $productData['category'])
                ->firstOrFail();
            $images = $productData['images'];

            unset($productData['category'], $productData['images']);

            $product = Product::query()->updateOrCreate(
                ['name' => $productData['name']],
                ['category_id' => $category->id, ...$productData],
            );

            foreach ($images as $sortOrder => $imageUrl) {
                ProductImage::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'sort_order' => $sortOrder,
                    ],
                    [
                        'image_url' => $imageUrl,
                        'is_primary' => $sortOrder === 0,
                    ],
                );
            }
        }
    }
}
