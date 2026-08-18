<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $categories = [
            ['name' => 'Gấu bông cỡ lớn', 'description' => 'Gấu bông kích thước lớn, phù hợp làm quà tặng đặc biệt', 'is_active' => true],
            ['name' => 'Gấu bông cỡ vừa', 'description' => 'Gấu bông kích thước trung bình, dễ thương cho mọi lứa tuổi', 'is_active' => true],
            ['name' => 'Gấu bông mini', 'description' => 'Gấu bông nhỏ xinh, kẹp túi hoặc trang trí bàn làm việc', 'is_active' => true],
            ['name' => 'Bộ sưu tập hoa gấu', 'description' => 'Bó hoa gấu bông handmade làm quà tặng', 'is_active' => true],
            ['name' => 'Gấu bông trang trí', 'description' => 'Gấu bông trang trí phòng ngủ, phòng khách', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
