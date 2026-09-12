<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo thêm các tài khoản khách hàng thực tế nếu chưa có
        $sampleCustomers = [
            ['full_name' => 'Nguyễn Thị Thuỳ Trang', 'email' => 'thuytrang@example.com'],
            ['full_name' => 'Vũ Hoàng Nam', 'email' => 'hoangnam@example.com'],
            ['full_name' => 'Đặng Minh Quân', 'email' => 'minhquan@example.com'],
            ['full_name' => 'Trịnh Thảo Linh', 'email' => 'thaolinh@example.com'],
            ['full_name' => 'Bùi Đức Anh', 'email' => 'ducanh@example.com'],
            ['full_name' => 'Đỗ Bích Ngọc', 'email' => 'bichngoc@example.com'],
            ['full_name' => 'Lê Thanh Tùng', 'email' => 'thanhtung@example.com'],
            ['full_name' => 'Phạm Mai Hương', 'email' => 'maihuong@example.com'],
            ['full_name' => 'Hồ Gia Bảo', 'email' => 'giabao@example.com'],
            ['full_name' => 'Lý Ánh Tuyết', 'email' => 'anhtuyet@example.com'],
            ['full_name' => 'Võ Quỳnh Anh', 'email' => 'quynhanh@example.com'],
            ['full_name' => 'Dương Quốc Trí', 'email' => 'quoctri@example.com'],
        ];

        foreach ($sampleCustomers as $cData) {
            User::firstOrCreate(
                ['email' => $cData['email']],
                [
                    'full_name' => $cData['full_name'],
                    'phone' => '09' . rand(10000000, 99999999),
                    'role' => User::ROLE_CUSTOMER,
                    'status' => User::STATUS_ACTIVE,
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );
        }

        $allCustomers = User::where('role', User::ROLE_CUSTOMER)->get();
        if ($allCustomers->isEmpty()) {
            return;
        }

        // Helper lấy hoặc tạo Order hoàn thành cho khách hàng
        $getOrderId = function ($userId) {
            $existing = Order::where('customer_id', $userId)->value('id');
            if ($existing) {
                return $existing;
            }

            $order = Order::create([
                'order_code' => 'MNB-' . strtoupper(substr(uniqid(), -8)),
                'customer_id' => $userId,
                'recipient_name' => 'Khách hàng thân thiết',
                'recipient_phone' => '09' . rand(10000000, 99999999),
                'recipient_address' => 'Hà Nội, Việt Nam',
                'subtotal' => 380000,
                'shipping_fee' => 30000,
                'total_amount' => 410000,
                'order_status' => 'COMPLETED',
                'payment_method' => 'COD',
                'payment_status' => 'PAID',
                'confirmed_at' => now()->subDays(rand(5, 30)),
                'completed_at' => now()->subDays(rand(1, 4)),
            ]);

            return $order->id;
        };

        // Helper gán review an toàn
        $createReview = function ($userEmail, $productId, $rating, $comment, $images = null) use ($allCustomers, $getOrderId) {
            $user = $allCustomers->firstWhere('email', $userEmail);
            if (!$user) {
                return;
            }

            $product = Product::find($productId);
            if (!$product) {
                return;
            }

            Review::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ],
                [
                    'order_id' => $getOrderId($user->id),
                    'rating' => $rating,
                    'comment' => $comment,
                    'images' => $images,
                    'is_hidden' => false,
                ]
            );
        };

        // =========================================================================
        // 1. SẢN PHẨM #63: Gấu Bông Teddy Socola (Có đầy đủ 5, 4, 3, 2, 1 Sao & Có Ảnh)
        // =========================================================================
        // 5 Sao - Có ảnh 1 + 3
        $createReview(
            'customer@matngotbear.com',
            63,
            5,
            'Bé gấu siêu mềm mịn và đáng yêu xỉu luôn! Gói hàng trong hộp quà thắt nơ cẩn thận kèm thiệp cảm ơn rất dễ thương. Mình để trên giường ngắm suốt ngày thôi 🥰',
            ['images/reviews/review_bear_1.jpg', 'images/reviews/review_bear_3.jpg']
        );

        // 5 Sao - Có ảnh 3
        $createReview(
            'nguyenvana@example.com',
            63,
            5,
            'Mua tặng sinh nhật bạn gái mà người yêu khen nức nở. Shop đóng gói sang xịn mịn, gấu bông gòn êm ái thơm tho, 10/10 nha shop!',
            ['images/reviews/review_bear_3.jpg']
        );

        // 5 Sao - Không ảnh
        $createReview(
            'thuytrang@example.com',
            63,
            5,
            'Chất vải nhung mềm mại sờ thích mê, lông gấu không bị rụng hay xù chút nào. Mình giặt thử rồi phơi khô vẫn giữ nguyên form đẹp như mới!'
        );

        // 4 Sao - Có ảnh 1
        $createReview(
            'hoangnam@example.com',
            63,
            4,
            'Gấu xinh lắm nhé mọi người, màu nâu socola ấm áp đúng ý mình. Trừ 1 sao nhỏ vì đơn vị ship giao hơi muộn nửa ngày nhưng bù lại bé gấu quá dễ thương.',
            ['images/reviews/review_bear_1.jpg']
        );

        // 4 Sao - Không ảnh
        $createReview(
            'tranthibinh@example.com',
            63,
            4,
            'Giao hàng nhanh, gấu dày dặn chuẩn form, chất vải sờ rất êm tay, đường chỉ may kỹ càng không thấy sợi thừa nào.'
        );

        // 3 Sao - Có ảnh 3
        $createReview(
            'minhquan@example.com',
            63,
            3,
            'Bé gấu màu sắc giống hình chụp, đóng gói cẩn thận. Tuy nhiên nơ áo hơi bị lệch nhẹ phải tự chỉnh lại một chút, gòn nhồi vừa phải.',
            ['images/reviews/review_bear_3.jpg']
        );

        // 3 Sao - Không ảnh
        $createReview(
            'thaolinh@example.com',
            63,
            3,
            'Gấu nhìn chung đẹp và êm, nhưng kích thước cảm giác hơi nhỏ hơn mình tưởng tượng một chút. Bạn nào thích to thì nên chọn size 1m trở lên nha.'
        );

        // 2 Sao - Không ảnh
        $createReview(
            'ducanh@example.com',
            63,
            2,
            'Bé gấu thì xinh xắn mềm mại, nhưng hộp giấy đóng gói lúc nhận bị móp một góc do bên vận chuyển quăng quật. May mà gấu bên trong không bị rách bẩn, shop nên bọc thêm bóng khí bảo vệ bên ngoài.'
        );

        // 1 Sao - Không ảnh
        $createReview(
            'bichngoc@example.com',
            63,
            1,
            'Shipper giao hàng chậm hơn ngày hẹn tới 3 hôm làm mình bị trễ quà tặng sinh nhật em gái. Mặc dù gấu đẹp nhưng trải nghiệm vận chuyển làm mình rất thất vọng.'
        );

        // =========================================================================
        // 2. SẢN PHẨM #67: Gấu Bơ Butter Bear Má Hồng (5 Sao, 4 Sao, 3 Sao, 2 Sao & Có Ảnh)
        // =========================================================================
        // 5 Sao - Có ảnh 2
        $createReview(
            'phamthidung@example.com',
            67,
            5,
            'Bé Butter Bear cưng xỉu luôn mọi người ơi! Đội mũ bơ má hồng xinh yêu cực kì, mang đi cafe chụp hình ai cũng khen nức nở 🥑🧸',
            ['images/reviews/review_bear_2.jpg']
        );

        // 5 Sao - Có ảnh 2 + 1
        $createReview(
            'levancuong@example.com',
            67,
            5,
            'Chất lông nhung mềm mướt không rụng sợi nào, nhồi bông gòn trắng đàn hồi tốt. Mình mua size 45cm ôm vừa vặn, rất đáng tiền!',
            ['images/reviews/review_bear_2.jpg', 'images/reviews/review_bear_1.jpg']
        );

        // 5 Sao - Không ảnh
        $createReview(
            'thanhtung@example.com',
            67,
            5,
            'Mặt bé bơ ngây thơ cute, má hồng phấn tự nhiên nhìn cưng muốn xỉu. Shop tư vấn nhiệt tình, đóng gói hộp xinh xắn.'
        );

        // 4 Sao - Không ảnh
        $createReview(
            'maihuong@example.com',
            67,
            4,
            'Bé gấu xinh xắn, bông êm, ôm ngủ rất thích. Giá hơi cao một xíu nhưng chất lượng xứng đáng.'
        );

        // 3 Sao - Không ảnh
        $createReview(
            'giabao@example.com',
            67,
            3,
            'Mũ bơ có thể tháo rời được nhưng hơi lỏng một chút dễ bị tuột, còn lại gấu thì mềm và thơm.'
        );

        // 2 Sao - Không ảnh
        $createReview(
            'anhtuyet@example.com',
            67,
            2,
            'Chờ shop đóng gói và gửi hàng hơi lâu, mất gần 4 ngày mới nhận được trong khi cùng nội thành.'
        );

        // =========================================================================
        // 3. SẢN PHẨM #70: Gấu Bông Mr. Bean Mắt Cúc (5, 4, 3, 1 Sao & Có Ảnh)
        // =========================================================================
        // 5 Sao - Có ảnh 3
        $createReview(
            'hoangvanem@example.com',
            70,
            5,
            'Chuẩn gấu Mr Bean tuổi thơ luôn! Mắt cúc đính chắc chắn, hộp quà vintage rất đẹp mắt, tặng bạn bè hay để bàn làm việc đều mê.',
            ['images/reviews/review_bear_3.jpg']
        );

        // 5 Sao - Không ảnh
        $createReview(
            'quynhanh@example.com',
            70,
            5,
            'Mình mua để sưu tầm kỷ niệm phim Mr Bean thời bé. Chất vải len dệt sợi thô cổ điển chuẩn phong cách châu Âu, rất ưng ý.'
        );

        // 4 Sao - Không ảnh
        $createReview(
            'quoctri@example.com',
            70,
            4,
            'Gấu chắc chắn, đường may tỉ mỉ, nơ vải kẻ sọc vintage rất phong cách.'
        );

        // 3 Sao - Không ảnh
        $createReview(
            'thuytrang@example.com',
            70,
            3,
            'Chất liệu len dệt sợi thô nên sờ không mềm nhung mịn như gấu teddy thông thường, bạn nào thích mềm mịn thì nên cân nhắc nha. Nhưng đúng phong cách vintage.'
        );

        // 1 Sao - Không ảnh
        $createReview(
            'ducanh@example.com',
            70,
            1,
            'Giao nhầm size nhỏ hơn cho mình, liên hệ bộ phận hỗ trợ đổi trả mất 2 ngày mới phản hồi.'
        );

        // =========================================================================
        // 4. SẢN PHẨM #73: Cặp Gấu Bông Teddy Cô Dâu Chú Rể (5, 4, 3, 2 Sao & Có Ảnh)
        // =========================================================================
        // 5 Sao - Có ảnh 4
        $createReview(
            'customer@matngotbear.com',
            73,
            5,
            'Set đôi xinh xắn lắm luôn, áo len dệt tinh xảo từng chiếc cúc gỗ nhỏ. Để trên bàn trang điểm cạnh hoa khô chụp hình đẹp mê ly 💕',
            ['images/reviews/review_bear_4.jpg']
        );

        // 5 Sao - Không ảnh
        $createReview(
            'minhquan@example.com',
            73,
            5,
            'Làm quà cưới cho bạn thân, cả cô dâu chú rể đều khen nức nở vì độ tinh xảo. Đóng gói hộp đôi rất sang trọng và lịch sự.'
        );

        // 4 Sao - Có ảnh 4
        $createReview(
            'thaolinh@example.com',
            73,
            4,
            'Cặp gấu dễ thương, đan len tỉ mỉ, màu sắc ấm áp. Rất thích hợp làm quà kỷ niệm tình yêu.',
            ['images/reviews/review_bear_4.jpg']
        );

        // 3 Sao - Không ảnh
        $createReview(
            'bichngoc@example.com',
            73,
            3,
            'Bé gấu nữ nơ hoa hơi nhỏ hơn bé trai một chút, nhưng tổng quan thì vẫn rất hài lòng.'
        );

        // 2 Sao - Không ảnh
        $createReview(
            'anhtuyet@example.com',
            73,
            2,
            'Thiệp đính kèm shop quên viết lời chúc theo ghi chú đơn hàng giúp mình, phải tự mua bút viết tay lại.'
        );

        // =========================================================================
        // 5. SẢN PHẨM #64: Gấu Bông Teddy Logo Baby
        // =========================================================================
        $createReview(
            'hoangnam@example.com',
            64,
            5,
            'Gấu thêu logo Baby ở bàn chân rất sắc nét, bông gòn trắng đàn hồi tốt, bé nhà mình ôm ngủ suốt ngày.',
            ['images/reviews/review_bear_1.jpg']
        );
        $createReview(
            'tranthibinh@example.com',
            64,
            4,
            'Chất lượng tốt, đóng gói chu đáo, giao hàng đúng hẹn.'
        );
        $createReview(
            'quoctri@example.com',
            64,
            3,
            'Gấu xinh nhưng kích thước hơi vừa phải, shop nên có thêm size khủng 1m5.'
        );

        // =========================================================================
        // 6. SẢN PHẨM #68: Butter Bear Váy Hồng Bồng Bềnh
        // =========================================================================
        $createReview(
            'quynhanh@example.com',
            68,
            5,
            'Chiếc váy hồng công chúa bồng bềnh siêu xinh, đường ren may tỉ mỉ. Rất đáng mua nha mn!',
            ['images/reviews/review_bear_2.jpg']
        );
        $createReview(
            'thanhtung@example.com',
            68,
            4,
            'Bé bơ mặc váy hồng dễ thương lắm, bạn gái mình thích mê.'
        );
    }
}
