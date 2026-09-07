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
                'id'             => 1,
                'category_id'    => $catTeddy,
                'name'           => 'Gấu Bơ Butterbear Đầu Bếp Bánh Mì 45cm',
                'description'    => 'Siêu phẩm Gấu Bơ Butterbear phiên bản Đầu bếp tiệm bánh Butterbear Bakery Thái Lan. Bé gấu béo tròn má hồng phúng phính, diện chiếc tạp dề hồng thêu logo và đội mũ đầu bếp tinh nghịch, cầm cây lăn bột mini. Chất lông xoắn nhung siêu mềm mịn, 100% bông bi kháng khuẩn đàn hồi cao cấp.',
                'price'          => 380000,
                'sale_price'     => 320000,
                'size'           => '45cm',
                'color'          => 'Vàng Bơ',
                'material'       => 'Bông gòn bi 3D, Vải lông nhung tuyết cao cấp',
                'stock_quantity' => 45,
                'status'         => 'ACTIVE',
                'sold_count'     => 480,
                'images'         => [
                    '/images/products/butterbear-chef.jpg',
                    '/images/products/butterbear-toast.jpg',
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
                    '/images/products/butterbear-pink-dress.jpg',
                    '/images/products/butterbear-couple.jpg',
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
                    '/images/products/butterbear-toast.jpg',
                    '/images/products/butterbear-chef.jpg',
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
                    '/images/products/butterbear-couple.jpg',
                    '/images/products/butterbear-pink-dress.jpg',
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
