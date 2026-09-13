<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Tạo dữ liệu mẫu 2-3 kích thước và 2-3 màu sắc kèm hình ảnh cho từng sản phẩm.
     */
    public function run(): void
    {
        // Xóa các biến thể cũ để nạp mới đồng bộ
        ProductVariant::truncate();

        $products = Product::with('images', 'category')->get();

        // Cấu hình phân loại mẫu theo từng danh mục
        $categoryConfigs = [
            'BUTTER BEAR' => [
                'sizes' => ['40cm', '60cm', '80cm'],
                'colors' => [
                    ['name' => 'Vàng Bơ', 'suffix' => 'vang_bo', 'price_diff' => 0],
                    ['name' => 'Hồng Dâu', 'suffix' => 'hong_dau', 'price_diff' => 20000],
                    ['name' => 'Nâu Caramel', 'suffix' => 'nau_caramel', 'price_diff' => 30000],
                ],
                'size_price_add' => ['40cm' => 0, '60cm' => 70000, '80cm' => 140000],
            ],
            'TEDDY CLASSIC' => [
                'sizes' => ['1m2', '1m5', '1m8'],
                'colors' => [
                    ['name' => 'Nâu Socola', 'suffix' => 'nau_socola', 'price_diff' => 0],
                    ['name' => 'Vàng Kem', 'suffix' => 'vang_kem', 'price_diff' => 15000],
                    ['name' => 'Hồng Pastel', 'suffix' => 'hong_pastel', 'price_diff' => 25000],
                ],
                'size_price_add' => ['1m2' => 0, '1m5' => 120000, '1m8' => 250000],
            ],
            'TEDDY MR. BEAN' => [
                'sizes' => ['35cm', '50cm', '80cm'],
                'colors' => [
                    ['name' => 'Nâu Cổ Điển', 'suffix' => 'nau_co_dien', 'price_diff' => 0],
                    ['name' => 'Xám Bạc', 'suffix' => 'xam_bac', 'price_diff' => 20000],
                    ['name' => 'Nâu Quế', 'suffix' => 'nau_que', 'price_diff' => 25000],
                ],
                'size_price_add' => ['35cm' => 0, '50cm' => 60000, '80cm' => 150000],
            ],
            'TEDDY COUPLE' => [
                'sizes' => ['50cm (Cặp)', '70cm (Cặp)', '1m (Cặp)'],
                'colors' => [
                    ['name' => 'Trắng Kem', 'suffix' => 'trang_kem', 'price_diff' => 0],
                    ['name' => 'Hồng Pastel', 'suffix' => 'hong_pastel', 'price_diff' => 30000],
                    ['name' => 'Đỏ Rượu', 'suffix' => 'do_ruou', 'price_diff' => 50000],
                ],
                'size_price_add' => ['50cm (Cặp)' => 0, '70cm (Cặp)' => 150000, '1m (Cặp)' => 320000],
            ],
            'GỐI BÔNG TEDDY' => [
                'sizes' => ['90cm', '1m2', '1m5'],
                'colors' => [
                    ['name' => 'Xám Khói', 'suffix' => 'xam_khoi', 'price_diff' => 0],
                    ['name' => 'Xanh Biển', 'suffix' => 'xanh_bien', 'price_diff' => 20000],
                    ['name' => 'Hồng Pastel', 'suffix' => 'hong_pastel', 'price_diff' => 20000],
                ],
                'size_price_add' => ['90cm' => 0, '1m2' => 80000, '1m5' => 160000],
            ],
        ];

        // Mặc định cho các loại khác
        $defaultConfig = [
            'sizes' => ['30cm', '50cm', '65cm'],
            'colors' => [
                ['name' => 'Xám Khói', 'suffix' => 'xam_khoi', 'price_diff' => 0],
                ['name' => 'Hồng Phấn', 'suffix' => 'hong_dau', 'price_diff' => 15000],
                ['name' => 'Xanh Biển', 'suffix' => 'xanh_bien', 'price_diff' => 20000],
            ],
            'size_price_add' => ['30cm' => 0, '50cm' => 50000, '65cm' => 100000],
        ];

        foreach ($products as $product) {
            $catName = $product->category ? $product->category->name : '';
            $config = $categoryConfigs[$catName] ?? $defaultConfig;

            // Ảnh đại diện chính gốc của sản phẩm
            $primaryImg = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
            $baseImgUrl = $primaryImg ? $primaryImg->image_url : '/images/products/teddy_socola_giant.jpg';
            $baseImgName = pathinfo($baseImgUrl, PATHINFO_FILENAME);

            $isFirst = true;

            foreach ($config['sizes'] as $sizeIdx => $size) {
                $sizeAdd = $config['size_price_add'][$size] ?? 0;

                foreach ($config['colors'] as $colorIdx => $colorItem) {
                    $colorName = $colorItem['name'];
                    $colorSuffix = $colorItem['suffix'];
                    $colorDiff = $colorItem['price_diff'];

                    // Tìm ảnh biến thể tương ứng
                    $variantImgPath = "/images/products/variants/{$baseImgName}_{$colorSuffix}.jpg";
                    if (!file_exists(public_path($variantImgPath))) {
                        // Thử fallback ảnh biến thể khác
                        $variantImgPath = $baseImgUrl;
                    }

                    $variantPrice = (float) ($product->price + $sizeAdd + $colorDiff);
                    
                    // Thiết lập kịch bản khuyến mãi đa dạng theo yêu cầu:
                    // - Màu đầu tiên: ĐANG GIẢM GIÁ (Active Sale) có đếm ngược còn 51h 33m 45s như Ảnh 2
                    // - Màu thứ 2: SẮP DIỄN RA (Upcoming Sale) lúc 21:00 như Ảnh 3
                    // - Màu thứ 3 ở size đầu tiên: ĐÃ HẾT HẠN (Expired Sale) trong quá khứ
                    // - Các biến thể còn lại: KHÔNG CÓ SALE (chỉ hiện 1 giá)
                    $variantSalePrice = null;
                    $saleStartAt = null;
                    $saleEndAt = null;

                    if ($colorIdx === 0) {
                        // 1. Đang trong đợt sale (Active)
                        $variantSalePrice = (float) (round(($variantPrice * 0.79) / 1000) * 1000); // Giảm ~21%
                        $saleStartAt = now()->subDay()->setHour(9)->setMinute(0);
                        // Kết thúc sau 51 giờ 33 phút 45 giây như ảnh mẫu
                        $saleEndAt = now()->addHours(51)->addMinutes(33)->addSeconds(45);
                    } elseif ($colorIdx === 1) {
                        // 2. Khuyến mãi sắp diễn ra trong tương lai (Upcoming)
                        $variantSalePrice = (float) (round(($variantPrice * 0.85) / 1000) * 1000); // Giảm 15%
                        $saleStartAt = now()->copy()->setHour(21)->setMinute(0)->setSecond(0);
                        if (now()->hour >= 21) {
                            $saleStartAt->addDay();
                        }
                        $saleEndAt = (clone $saleStartAt)->addDays(2);
                    } elseif ($colorIdx === 2 && $sizeIdx === 0) {
                        // 3. Khuyến mãi trong quá khứ đã hết hạn (Expired)
                        $variantSalePrice = (float) (round(($variantPrice * 0.80) / 1000) * 1000);
                        $saleStartAt = now()->subDays(5);
                        $saleEndAt = now()->subDays(1);
                    } else {
                        // 4. Không có khuyến mãi
                        $variantSalePrice = null;
                        $saleStartAt = null;
                        $saleEndAt = null;
                    }

                    $sku = 'MNB-' . $product->id . '-' . strtoupper(Str::slug($size)) . '-' . strtoupper(Str::slug($colorName));

                    ProductVariant::create([
                        'product_id'     => $product->id,
                        'sku'            => $sku,
                        'size'           => $size,
                        'color'          => $colorName,
                        'price'          => $variantPrice,
                        'sale_price'     => $variantSalePrice,
                        'sale_start_at'  => $saleStartAt,
                        'sale_end_at'    => $saleEndAt,
                        'stock_quantity' => rand(15, 60),
                        'image_url'      => $variantImgPath,
                        'status'         => 'ACTIVE',
                    ]);
                }
            }
        }
    }
}
