<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = Category::all()->keyBy('name');

        $products = [
            [
                'category' => 'Gấu bông cỡ lớn',
                'name' => 'Gấu bông Teddy khổng lồ 1m',
                'description' => 'Gấu bông Teddy cao 1m, lông siêu mềm, thích hợp làm quà tặng sinh nhật, kỷ niệm.',
                'price' => 550000,
                'sale_price' => 499000,
                'size' => '100cm',
                'color' => 'Nâu',
                'material' => 'Lông nhung cao cấp',
                'stock_quantity' => 20,
                'status' => 'ACTIVE',
            ],
            [
                'category' => 'Gấu bông cỡ lớn',
                'name' => 'Gấu bông Panda 80cm',
                'description' => 'Gấu bông gấu trúc dễ thương, thân thiện, chất liệu an toàn cho trẻ em.',
                'price' => 420000,
                'sale_price' => null,
                'size' => '80cm',
                'color' => 'Trắng - Đen',
                'material' => 'Cotton mềm',
                'stock_quantity' => 15,
                'status' => 'ACTIVE',
            ],
            [
                'category' => 'Gấu bông cỡ vừa',
                'name' => 'Gấu bông Brown Bear 40cm',
                'description' => 'Gấu bông nâu cỡ vừa, ôm êm ái, phù hợp làm quà tặng người yêu.',
                'price' => 250000,
                'sale_price' => 219000,
                'size' => '40cm',
                'color' => 'Nâu',
                'material' => 'Lông mềm',
                'stock_quantity' => 30,
                'status' => 'ACTIVE',
            ],
            [
                'category' => 'Gấu bông cỡ vừa',
                'name' => 'Gấu bông Hồng Rose 35cm',
                'description' => 'Gấu bông màu hồng pastel dễ thương, kèm nơ xinh xắn.',
                'price' => 230000,
                'sale_price' => null,
                'size' => '35cm',
                'color' => 'Hồng',
                'material' => 'Lông nhung',
                'stock_quantity' => 25,
                'status' => 'ACTIVE',
            ],
            [
                'category' => 'Gấu bông mini',
                'name' => 'Gấu bông mini kẹp túi',
                'description' => 'Gấu bông mini xinh xắn có móc kẹp túi xách, balo.',
                'price' => 89000,
                'sale_price' => 69000,
                'size' => '12cm',
                'color' => 'Nhiều màu',
                'material' => 'Lông ngắn',
                'stock_quantity' => 50,
                'status' => 'ACTIVE',
            ],
            [
                'category' => 'Gấu bông mini',
                'name' => 'Gấu bông móc khóa trái tim',
                'description' => 'Gấu bông mini ôm trái tim, làm móc khóa hoặc trang trí.',
                'price' => 99000,
                'sale_price' => null,
                'size' => '15cm',
                'color' => 'Đỏ',
                'material' => 'Lông mềm',
                'stock_quantity' => 40,
                'status' => 'ACTIVE',
            ],
            [
                'category' => 'Bộ sưu tập hoa gấu',
                'name' => 'Bó hoa gấu bông 9 bé',
                'description' => 'Bó hoa gấu bông handmade 9 bé gấu xinh xắn, quà tặng 8/3, valentine.',
                'price' => 650000,
                'sale_price' => 599000,
                'size' => '30cm',
                'color' => 'Nhiều màu',
                'material' => 'Lông nhung',
                'stock_quantity' => 10,
                'status' => 'ACTIVE',
            ],
            [
                'category' => 'Bộ sưu tập hoa gấu',
                'name' => 'Hộp hoa gấu bông 19 bé',
                'description' => 'Hộp quà hoa gấu bông sang trọng 19 bé, kèm thiệp chúc mừng.',
                'price' => 1200000,
                'sale_price' => null,
                'size' => '40cm',
                'color' => 'Nhiều màu',
                'material' => 'Lông nhung cao cấp',
                'stock_quantity' => 5,
                'status' => 'ACTIVE',
            ],
            [
                'category' => 'Gấu bông trang trí',
                'name' => 'Gấu bông ngồi trang trí phòng 50cm',
                'description' => 'Gấu bông ngồi tư thế dễ thương, trang trí giường ngủ, sofa.',
                'price' => 300000,
                'sale_price' => 269000,
                'size' => '50cm',
                'color' => 'Be',
                'material' => 'Cotton',
                'stock_quantity' => 18,
                'status' => 'ACTIVE',
            ],
            [
                'category' => 'Gấu bông trang trí',
                'name' => 'Gấu bông ôm gối dài 70cm',
                'description' => 'Gấu bông ôm gối dài, thoải mái khi ngủ và thư giãn.',
                'price' => 280000,
                'sale_price' => null,
                'size' => '70cm',
                'color' => 'Xám',
                'material' => 'Lông mềm',
                'stock_quantity' => 22,
                'status' => 'ACTIVE',
            ],
        ];

        foreach ($products as $product) {
            $category = $categories->get($product['category']);

            if (! $category) {
                continue;
            }

            $product['category_id'] = $category->id;
            unset($product['category']);

            $created = Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );

            ProductImage::updateOrCreate(
                ['product_id' => $created->id, 'image_url' => 'https://placehold.co/600x600?text='.urlencode($created->name)],
                ['is_primary' => true, 'sort_order' => 1]
            );
        }
    }
}
