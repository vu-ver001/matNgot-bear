<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Tạo dữ liệu danh mục chuẩn theo yêu cầu:
     * TEDDY CLASSIC, BUTTER BEAR, TEDDY MR. BEAN, TEDDY COUPLE, GỐI BÔNG TEDDY
     */
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'TEDDY CLASSIC',
                'description' => 'Bộ sưu tập gấu bông Teddy cổ điển truyền thống với chất lông xoắn hoa hồng mềm mại, màu sắc socola, nâu ấm và kem sữa ngọt ngào.',
                'is_active'   => true,
            ],
            [
                'name'        => 'BUTTER BEAR',
                'description' => 'Dòng gấu bơ Butter Bear siêu hot trend đình đám với má hồng phúng phính, đôi mắt long lanh và chất liệu bông gòn tinh khiết 100%.',
                'is_active'   => true,
            ],
            [
                'name'        => 'TEDDY MR. BEAN',
                'description' => 'Chú gấu bông nâu Mr. Bean huyền thoại tuổi thơ với thiết kế mắt cúc áo độc đáo, vải dệt len cao cấp đậm chất vintage nước Anh.',
                'is_active'   => true,
            ],
            [
                'name'        => 'TEDDY COUPLE',
                'description' => 'Các mẫu gấu bông đôi cô dâu chú rể, gấu cặp tình nhân áo đôi lãng mạn - món quà hoàn hảo cho ngày kỷ niệm và Valentine.',
                'is_active'   => true,
            ],
            [
                'name'        => 'GỐI BÔNG TEDDY',
                'description' => 'Gối ôm dài hình gấu bông, gối tựa lưng êm ái cho văn phòng và gối mền 2 trong 1 đa năng tiện lợi.',
                'is_active'   => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
