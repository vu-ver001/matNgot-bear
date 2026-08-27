<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Gấu bông',
                'description' => 'Những mẫu gấu bông mềm mại và đáng yêu dành cho mọi lứa tuổi.',
                'is_active' => true,
            ],
            [
                'name' => 'Thú bông',
                'description' => 'Thỏ, capybara và nhiều người bạn thú bông dễ thương khác.',
                'is_active' => true,
            ],
            [
                'name' => 'Bộ sưu tập giới hạn',
                'description' => 'Các thiết kế theo mùa với số lượng giới hạn.',
                'is_active' => true,
            ],
            [
                'name' => 'Phụ kiện',
                'description' => 'Trang phục và phụ kiện nhỏ dành cho gấu bông.',
                'is_active' => false,
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                $category,
            );
        }
    }
}
