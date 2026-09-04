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

    /**
     * Tạo sản phẩm mẫu gấu bông thực tế, ảnh đẹp sang trọng cho 5 danh mục.
     */
    public function run(): void
    {
        $catClassic = Category::where('name', 'TEDDY CLASSIC')->first()->id;
        $catButter  = Category::where('name', 'BUTTER BEAR')->first()->id;
        $catMrBean  = Category::where('name', 'TEDDY MR. BEAN')->first()->id;
        $catCouple  = Category::where('name', 'TEDDY COUPLE')->first()->id;
        $catPillow  = Category::where('name', 'GỐI BÔNG TEDDY')->first()->id;

        $products = [
            // TEDDY CLASSIC (4 sản phẩm)
            [
                'category_id'    => $catClassic,
                'name'           => 'Gấu Bông Teddy Socola 1m7 - 2m',
                'description'    => 'Gấu bông Teddy Socola khổng lồ 1m7 - 2m là món quà sinh nhật bất ngờ nhất. Lông xoắn 3D cao cấp mềm mại, nhồi bông PP tinh khiết đàn hồi cực tốt, thân hình to bự ôm trọn người.',
                'price'          => 1250000,
                'sale_price'     => 980000,
                'size'           => '1m8',
                'color'          => 'Nâu Socola',
                'material'       => '100% Bông PP 3D xoắn, vải nhung tuyết mịn',
                'stock_quantity' => 25,
                'status'         => 'ACTIVE',
                'sold_count'     => 340,
                'images'         => [
                    '/images/products/teddy_socola_giant.jpg',
                    '/images/products/teddy_reference_main.png',
                ],
            ],
            [
                'category_id'    => $catClassic,
                'name'           => 'Gấu Bông Teddy Logo Baby 1m7 - 2m',
                'description'    => 'Mẫu gấu Teddy màu vàng kem bơ áo len kẻ sọc Logo Baby dáng ngồi siêu cưng. Kích thước to bằng người thật, chất lông xù mềm mại không rụng lông, an toàn cho trẻ nhỏ.',
                'price'          => 1150000,
                'sale_price'     => 890000,
                'size'           => '1m7',
                'color'          => 'Vàng Kem',
                'material'       => 'Bông gòn bi tinh khiết, áo len dệt kim',
                'stock_quantity' => 18,
                'status'         => 'ACTIVE',
                'sold_count'     => 215,
                'images'         => [
                    '/images/products/teddy_logo_baby.jpg',
                    '/images/products/teddy_boy_classic.jpg',
                ],
            ],
            [
                'category_id'    => $catClassic,
                'name'           => 'Gấu Bông Teddy Boy Đeo Nơ Cổ Điển',
                'description'    => 'Chú gấu Teddy Boy màu nâu quế thanh lịch với nơ caro to bản sang trọng. Lớp lông xoắn hoa hồng mềm mướt tay, thích hợp làm quà tỏ tình hoặc trang trí sofa phòng khách.',
                'price'          => 650000,
                'sale_price'     => 499000,
                'size'           => '1m2',
                'color'          => 'Nâu Quế',
                'material'       => 'Bông PP cao cấp, nơ ruy băng lụa',
                'stock_quantity' => 30,
                'status'         => 'ACTIVE',
                'sold_count'     => 189,
                'images'         => [
                    '/images/products/teddy_boy_classic.jpg',
                    '/images/products/teddy_reference_main.png',
                ],
            ],
            [
                'category_id'    => $catClassic,
                'name'           => 'Gấu Bông Teddy Áo Len Gấu 1m6 Hồng Pastel',
                'description'    => 'Em gấu Teddy hồng phấn ngọt ngào diện áo len thêu hình gấu cưng. Màu sắc pastel nhẹ nhàng đốn tim mọi cô nàng, ôm ngủ cực êm ái và ấm áp.',
                'price'          => 950000,
                'sale_price'     => 760000,
                'size'           => '1m6',
                'color'          => 'Hồng Pastel',
                'material'       => 'Vải lông nhung tuyết, áo len dệt sợi tự nhiên',
                'stock_quantity' => 15,
                'status'         => 'ACTIVE',
                'sold_count'     => 410,
                'images'         => [
                    '/images/products/teddy_pink_pastel.jpg',
                    '/images/products/teddy_logo_baby.jpg',
                ],
            ],

            // BUTTER BEAR (3 sản phẩm)
            [
                'category_id'    => $catButter,
                'name'           => 'Gấu Bơ Butter Bear Má Hồng Đội Mũ Bơ 45cm',
                'description'    => 'Siêu phẩm Butter Bear đang làm mưa làm gió khắp châu Á! Biểu cảm má hồng cute phô mai que, đội chiếc mũ trái bơ ngộ nghĩnh, chất lông mịn như nhung.',
                'price'          => 350000,
                'sale_price'     => 279000,
                'size'           => '45cm',
                'color'          => 'Vàng Bơ',
                'material'       => 'Bông gòn 3D cao cấp, vải mịn không xơ',
                'stock_quantity' => 45,
                'status'         => 'ACTIVE',
                'sold_count'     => 580,
                'images'         => [
                    '/images/products/butter_bear_hat.jpg',
                    '/images/products/butter_bear_dress.jpg',
                ],
            ],
            [
                'category_id'    => $catButter,
                'name'           => 'Butter Bear Váy Hồng Bồng Bềnh 60cm',
                'description'    => 'Bé gấu Butter Bear công chúa diện chiếc váy hồng xòe điệu đà, tai thêu nơ hoa xinh xắn. Là món quà tặng bé gái hoặc người yêu cực kỳ đáng yêu.',
                'price'          => 480000,
                'sale_price'     => 389000,
                'size'           => '60cm',
                'color'          => 'Vàng Bơ & Hồng',
                'material'       => 'Bông PP đàn hồi 4 chiều, vải voan lụa cao cấp',
                'stock_quantity' => 28,
                'status'         => 'ACTIVE',
                'sold_count'     => 310,
                'images'         => [
                    '/images/products/butter_bear_dress.jpg',
                    '/images/products/butter_bear_toast.jpg',
                ],
            ],
            [
                'category_id'    => $catButter,
                'name'           => 'Gấu Butter Bear Cầm Bánh Mì Nướng 35cm',
                'description'    => 'Em gấu bơ ôm lát bánh mì nướng giòn rụm với nụ cười ngọt ngào. Kích thước 35cm nhỏ gọn mang theo đi du lịch hoặc để bàn làm việc giảm stress.',
                'price'          => 280000,
                'sale_price'     => 219000,
                'size'           => '35cm',
                'color'          => 'Vàng Kem',
                'material'       => '100% Bông sạch kháng khuẩn',
                'stock_quantity' => 60,
                'status'         => 'ACTIVE',
                'sold_count'     => 450,
                'images'         => [
                    '/images/products/butter_bear_toast.jpg',
                    '/images/products/butter_bear_hat.jpg',
                ],
            ],

            // TEDDY MR. BEAN (3 sản phẩm)
            [
                'category_id'    => $catMrBean,
                'name'           => 'Gấu Bông Mr. Bean Cổ Điển Mắt Cúc 40cm',
                'description'    => 'Chú gấu bông người bạn thân nhất của Mr. Bean! Thiết kế chuẩn phim với mắt cúc áo thủ công, thân hình thon dài độc đáo và vải len đan mộc mạc hoài niệm.',
                'price'          => 290000,
                'sale_price'     => 229000,
                'size'           => '40cm',
                'color'          => 'Nâu Đất',
                'material'       => 'Vải len dệt sợi thô phong cách Vintage',
                'stock_quantity' => 38,
                'status'         => 'ACTIVE',
                'sold_count'     => 290,
                'images'         => [
                    '/images/products/mr_bean_vintage.jpg',
                    '/images/products/mr_bean_giant.jpg',
                ],
            ],
            [
                'category_id'    => $catMrBean,
                'name'           => 'Gấu Bông Mr. Bean Phiên Bản To 80cm',
                'description'    => 'Phiên bản Mr. Bean nhồi bông size lớn 80cm ôm siêu đã. Chất len dệt mềm không xù lông, món quà sưu tầm vô giá cho các fan trung thành của series phim hài Mr. Bean.',
                'price'          => 550000,
                'sale_price'     => 449000,
                'size'           => '80cm',
                'color'          => 'Nâu Đất',
                'material'       => 'Bông PP 3 chiều, vải len cao cấp',
                'stock_quantity' => 20,
                'status'         => 'ACTIVE',
                'sold_count'     => 145,
                'images'         => [
                    '/images/products/mr_bean_giant.jpg',
                    '/images/products/mr_bean_vintage.jpg',
                ],
            ],
            [
                'category_id'    => $catMrBean,
                'name'           => 'Combo Gấu Mr. Bean Mini 25cm + Hộp Quà Vintage',
                'description'    => 'Set quà tặng gấu Mr. Bean mini 25cm kèm hộp giấy kraft thắt nơ sang trọng và thiệp viết tay vintage. Phù hợp làm quà tặng bạn thân, đồng nghiệp.',
                'price'          => 220000,
                'sale_price'     => 179000,
                'size'           => '25cm',
                'color'          => 'Nâu Đất',
                'material'       => 'Vải len mộc cao cấp, hộp quà carton cao cấp',
                'stock_quantity' => 50,
                'status'         => 'ACTIVE',
                'sold_count'     => 320,
                'images'         => [
                    '/images/products/mr_bean_giftbox.jpg',
                    '/images/products/mr_bean_vintage.jpg',
                ],
            ],

            // TEDDY COUPLE (3 sản phẩm)
            [
                'category_id'    => $catCouple,
                'name'           => 'Cặp Gấu Bông Teddy Cô Dâu Chú Rể 50cm',
                'description'    => 'Cặp đôi gấu bông cưới lộng lẫy: chú rể mặc vest thắt nơ bảnh bao, cô dâu diện váy cưới voan đính hoa lấp lánh. Món quà cưới, quà kỷ niệm ngày yêu đầy ý nghĩa.',
                'price'          => 780000,
                'sale_price'     => 620000,
                'size'           => '50cm',
                'color'          => 'Trắng & Đen',
                'material'       => 'Bông PP cao cấp, vải voan & satin cưới',
                'stock_quantity' => 16,
                'status'         => 'ACTIVE',
                'sold_count'     => 230,
                'images'         => [
                    '/images/products/teddy_couple_wedding.jpg',
                    '/images/products/teddy_couple_heart.jpg',
                ],
            ],
            [
                'category_id'    => $catCouple,
                'name'           => 'Cặp Gấu Teddy Áo Đôi Trái Tim Tình Yêu 60cm',
                'description'    => 'Hai chú gấu Teddy nâu sữa diện áo đôi dệt hình trái tim Love. Thiết kế ấm áp lãng mạn, gửi gắm thông điệp tình yêu bền chặt gắn kết.',
                'price'          => 690000,
                'sale_price'     => 550000,
                'size'           => '60cm',
                'color'          => 'Nâu & Kem',
                'material'       => 'Lông xoắn mềm mịn, áo len thêu trái tim',
                'stock_quantity' => 22,
                'status'         => 'ACTIVE',
                'sold_count'     => 195,
                'images'         => [
                    '/images/products/teddy_couple_heart.jpg',
                    '/images/products/teddy_couple_flower.jpg',
                ],
            ],
            [
                'category_id'    => $catCouple,
                'name'           => 'Set Gấu Bông Couple Ôm Bó Hoa Kỷ Niệm 40cm',
                'description'    => 'Cặp gấu bông ôm bó hoa hồng vĩnh cửu kèm hộp mica trong suốt sang chảnh. Sản phẩm bán chạy số 1 mỗi dịp Valentine và Quốc tế Phụ nữ 8/3.',
                'price'          => 520000,
                'sale_price'     => 419000,
                'size'           => '40cm',
                'color'          => 'Hồng & Trắng',
                'material'       => 'Bông xoắn 3D tinh khiết, hoa sáp thơm',
                'stock_quantity' => 35,
                'status'         => 'ACTIVE',
                'sold_count'     => 480,
                'images'         => [
                    '/images/products/teddy_couple_flower.jpg',
                    '/images/products/teddy_couple_heart.jpg',
                ],
            ],

            // GỐI BÔNG TEDDY (3 sản phẩm)
            [
                'category_id'    => $catPillow,
                'name'           => 'Gối Ôm Dài Gấu Teddy Cao Cấp 1m2 - 1m5',
                'description'    => 'Gối ôm hình gấu Teddy dáng dài êm ái, ruột nhồi 100% bông bi thái trắng tinh không xẹp lún. Giúp bạn ngủ ngon, nâng đỡ cột sống cổ và eo cực tốt.',
                'price'          => 450000,
                'sale_price'     => 359000,
                'size'           => '1m2',
                'color'          => 'Nâu Nhạt',
                'material'       => 'Vỏ nhung spandex co giãn 4 chiều, ruột bông gòn bi',
                'stock_quantity' => 40,
                'status'         => 'ACTIVE',
                'sold_count'     => 520,
                'images'         => [
                    '/images/products/teddy_long_pillow.jpg',
                    '/images/products/teddy_reference_main.png',
                ],
            ],
            [
                'category_id'    => $catPillow,
                'name'           => 'Gối Tựa Lưng Văn Phòng Hình Mặt Gấu Teddy 40cm',
                'description'    => 'Gối tựa lưng êm ái chống mỏi cột sống khi ngồi làm việc lâu. Thiết kế mặt gấu cười híp mắt dễ thương, có quai cài cố định vào ghế văn phòng.',
                'price'          => 220000,
                'sale_price'     => 169000,
                'size'           => '40cm',
                'color'          => 'Vàng Kem',
                'material'       => 'Bông PP cao cấp, vải nỉ nhung thoáng khí',
                'stock_quantity' => 55,
                'status'         => 'ACTIVE',
                'sold_count'     => 390,
                'images'         => [
                    '/images/products/teddy_reference_main.png',
                    '/images/products/teddy_logo_baby.jpg',
                ],
            ],
            [
                'category_id'    => $catPillow,
                'name'           => 'Gối Mền Gấu Bông 2 Trong 1 Đa Năng Kèm Chăn',
                'description'    => 'Bộ gối mền du lịch 3 trong 1: vừa làm gấu bông ôm, vừa làm gối tựa, mở khóa sau lưng có ngay chăn tuyết nhung ấm áp 1m1 x 1m6. Cực tiện lợi cho dân văn phòng và đi xe ô tô.',
                'price'          => 360000,
                'sale_price'     => 289000,
                'size'           => 'Gối 40cm + Chăn 1m6',
                'color'          => 'Nâu Socola',
                'material'       => 'Chăn nỉ tuyết siêu mềm, gối bông PP êm ái',
                'stock_quantity' => 30,
                'status'         => 'ACTIVE',
                'sold_count'     => 610,
                'images'         => [
                    '/images/products/teddy_socola_giant.jpg',
                    '/images/products/teddy_long_pillow.jpg',
                ],
            ],
        ];

        // Xóa sản phẩm cũ và tạo mới
        ProductImage::query()->delete();
        Product::query()->delete();

        foreach ($products as $pData) {
            $images = $pData['images'] ?? [];
            unset($pData['images']);

            $product = Product::create($pData);

            foreach ($images as $index => $imgUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url'  => $imgUrl,
                    'is_primary' => ($index === 0),
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
