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
     * Tạo sản phẩm mẫu gấu bông Butterbear cao cấp, chân thực với bộ ảnh studio sang trọng.
     */
    public function run(): void
    {
        $defaultCat = Category::first()?->id ?? 1;
        $catTeddy = Category::where('name', 'LIKE', '%Teddy%')->orWhere('name', 'LIKE', '%Butter%')->first()?->id ?? $defaultCat;
        $catAnimation = Category::where('name', 'LIKE', '%Hoạt Hình%')->orWhere('name', 'LIKE', '%Phụ Kiện%')->first()?->id ?? $defaultCat;
        $catPillow = Category::where('name', 'LIKE', '%Gối%')->first()?->id ?? $defaultCat;

        $products = [
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
                'material'       => 'Bông gòn bi 3D, Vải lông nhung tuyết cao cấp',
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
            [
                'id'             => 2,
                'category_id'    => $catTeddy,
                'name'           => 'Gấu Bơ Butterbear Váy Hồng Công Chúa 50cm',
                'description'    => 'Bé gấu Butterbear điệu đà trong chiếc đầm ren hoa nhí hồng pastel, thắt nơ satin hồng bên tai cực ngọt ngào. Món quà hoàn hảo đốn tim phái đẹp, người yêu hoặc bạn bè dịp sinh nhật và ngày kỷ niệm.',
                'price'          => 420000,
                'sale_price'     => 360000,
                'size'           => '50cm',
                'color'          => 'Vàng Bơ & Hồng Pastel',
                'material'       => 'Vải nhung mịn màng, ren hoa cao cấp',
                'stock_quantity' => 38,
                'status'         => 'ACTIVE',
                'sold_count'     => 395,
                'images'         => [
                    '/images/products/teddy_long_pillow.jpg',
                    '/images/products/teddy_reference_main.png',
                ],
            ],
            [
                'id'             => 3,
                'category_id'    => $catPillow,
                'name'           => 'Gối Ôm Gấu Bơ Butterbear Ngủ Ngon Kèm Bịt Mắt 60cm',
                'description'    => 'Gối ôm nhồi bông Butterbear phiên bản say giấc nồng diện bộ pijama chấm bi xanh bạc hà, đeo bịt mắt thêu lông mi xinh xắn và ôm ngôi sao vàng nhỏ. Giúp bé và bạn có giấc ngủ sâu, thư giãn sau ngày dài làm việc.',
                'price'          => 390000,
                'sale_price'     => 310000,
                'size'           => '60cm',
                'color'          => 'Vàng Bơ & Xanh Mint',
                'material'       => 'Vải Spandex co giãn 4 chiều mềm mướt tay',
                'stock_quantity' => 50,
                'status'         => 'ACTIVE',
                'sold_count'     => 530,
                'images'         => [
                    '/images/products/butterbear-sleeping.jpg',
                    '/images/products/butterbear-giant.jpg',
                ],
            ],
            [
                'id'             => 4,
                'category_id'    => $catAnimation,
                'name'           => 'Móc Khóa Gấu Bơ Butterbear Mini Đeo Yếm 15cm',
                'description'    => 'Móc khóa nhồi bông Butterbear size mini 15cm đeo yếm thêu hoa vintage, móc treo kim loại không gỉ cao cấp và dây dù đan chắc chắn. Dễ dàng móc vào balo, túi xách, chìa khóa xe tạo điểm nhấn siêu dễ thương.',
                'price'          => 120000,
                'sale_price'     => 89000,
                'size'           => '15cm',
                'color'          => 'Vàng Kem Sữa',
                'material'       => 'Bông gòn cao cấp, móc khóa mạ bạc',
                'stock_quantity' => 120,
                'status'         => 'ACTIVE',
                'sold_count'     => 950,
                'images'         => [
                    '/images/products/butterbear-keychain.jpg',
                    '/images/products/butterbear-chef.jpg',
                ],
            ],
            [
                'id'             => 5,
                'category_id'    => $catTeddy,
                'name'           => 'Gấu Bơ Butterbear Ôm Hũ Mật Ong Mật Ngọt 40cm',
                'description'    => 'Biểu tượng độc quyền của Mật Ngọt Bear! Chú gấu bơ tròn trịa đội băng đô len đính chú ong nhỏ, hai tay ôm trọn hũ mật ong vàng óng kèm que khuấy gỗ. Nụ cười híp mí ngọt ngào xua tan mọi mệt mỏi.',
                'price'          => 350000,
                'sale_price'     => 290000,
                'size'           => '40cm',
                'color'          => 'Vàng Bơ Mật Ong',
                'material'       => 'Lông nhung mịn xốp, ruột bông gòn bi 3D',
                'stock_quantity' => 60,
                'status'         => 'ACTIVE',
                'sold_count'     => 620,
                'images'         => [
                    '/images/products/butterbear-honey-pot.jpg',
                    '/images/products/butterbear-toast.jpg',
                ],
            ],
            [
                'id'             => 6,
                'category_id'    => $catTeddy,
                'name'           => 'Gấu Bơ Butterbear Khổng Lồ 1m2 Siêu To Khổng Lồ',
                'description'    => 'Phiên bản Butterbear ngoại cỡ 1m2 ôm trọn vòng tay, quàng khăn len xanh ấm áp và ôm gối êm ái. Thích hợp trang trí phòng khách, giường ngủ hoặc làm quà bất ngờ cực lớn cho người thương.',
                'price'          => 1250000,
                'sale_price'     => 980000,
                'size'           => '1m2',
                'color'          => 'Vàng Mật Ong',
                'material'       => '100% Bông PP tinh khiết đàn hồi 4 chiều',
                'stock_quantity' => 18,
                'status'         => 'ACTIVE',
                'sold_count'     => 180,
                'images'         => [
                    '/images/products/butterbear-giant.jpg',
                    '/images/products/butterbear-sleeping.jpg',
                ],
            ],
            [
                'id'             => 7,
                'category_id'    => $catAnimation,
                'name'           => 'Gấu Bơ Butterbear Cầm Bánh Mì Nướng Mũ Nồi 35cm',
                'description'    => 'Bé gấu Butterbear phong cách quý tộc Pháp với chiếc mũ beret vàng quý phái, quàng khăn caro cổ điển, tay ôm lát bánh mì nướng bơ vàng ruộm thơm lừng. Thiết kế nhỏ gọn xinh xắn thích hợp để bàn học, bàn làm việc.',
                'price'          => 290000,
                'sale_price'     => 245000,
                'size'           => '35cm',
                'color'          => 'Vàng Kem',
                'material'       => 'Bông PP cao cấp, nỉ len dệt kim',
                'stock_quantity' => 55,
                'status'         => 'ACTIVE',
                'sold_count'     => 410,
                'images'         => [
                    '/images/products/teddy_reference_main.png',
                    '/images/products/teddy_logo_baby.jpg',
                ],
            ],
            [
                'id'             => 8,
                'category_id'    => $catTeddy,
                'name'           => 'Set Cặp Đôi Gấu Bơ Butterbear Cô Dâu Chú Rể 45cm',
                'description'    => 'Cặp đôi uyên ương Butterbear lộng lẫy: Chú rể diện gile tweed thắt nơ cài hoa lịch lãm, Cô dâu đội khăn voan cưới đính vương miện ngọc trai quý phái. Món quà cưới, quà kỷ niệm tình yêu sang trọng và đầy ý nghĩa.',
                'price'          => 850000,
                'sale_price'     => 690000,
                'size'           => '45cm',
                'color'          => 'Vàng Bơ & Trắng Kem',
                'material'       => 'Vải nhung lông mịn, voan lưới & gile tweed cao cấp',
                'stock_quantity' => 15,
                'status'         => 'ACTIVE',
                'sold_count'     => 210,
                'images'         => [
                    '/images/products/teddy_socola_giant.jpg',
                    '/images/products/teddy_long_pillow.jpg',
                ],
            ],
        ];

        foreach ($products as $pData) {
            $images = $pData['images'] ?? [];
            unset($pData['images']);

            $product = Product::updateOrCreate(
                ['id' => $pData['id']],
                $pData
            );

            ProductImage::where('product_id', $product->id)->delete();
            foreach ($images as $index => $imgUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url'  => $imgUrl,
                    'is_primary' => ($index === 0),
                    'sort_order' => $index + 1,
                ]);
            }
        }
    }
}
