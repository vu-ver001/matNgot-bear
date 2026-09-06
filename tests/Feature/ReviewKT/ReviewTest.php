<?php

namespace Tests\Feature\ReviewKT;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $attributes = []): Product
    {
        $category = Category::query()->create([
            'name' => fake()->unique()->words(2, true),
            'is_active' => true,
        ]);

        return Product::query()->create(array_merge([
            'category_id' => $category->id,
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => 250000,
            'sale_price' => null,
            'stock_quantity' => 10,
            'status' => Product::STATUS_ACTIVE,
            'sold_count' => 0,
        ], $attributes));
    }

    private function createOrder(User $customer, array $attributes = []): Order
    {
        static $orderCount = 1;

        return Order::query()->create(array_merge([
            'order_code' => 'ORD-TEST-'.uniqid().'-'.($orderCount++),
            'customer_id' => $customer->id,
            'recipient_name' => $customer->full_name ?? 'Test Customer',
            'recipient_phone' => '0987654321',
            'recipient_address' => '123 Đường Test, Hà Nội',
            'subtotal' => 250000,
            'shipping_fee' => 30000,
            'total_amount' => 280000,
            'order_status' => 'COMPLETED',
            'payment_method' => 'COD',
            'payment_status' => 'PAID',
            'completed_at' => now(),
        ], $attributes));
    }

    public function test_guest_cannot_create_review(): void
    {
        $product = $this->createProduct();

        $response = $this->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Sản phẩm rất đẹp.',
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_who_has_not_purchased_product_cannot_review(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();

        $response = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Chưa mua mà muốn đánh giá.',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
        $this->assertDatabaseMissing('reviews', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_user_cannot_review_if_order_is_not_completed(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();

        // Tạo đơn hàng đang giao (SHIPPING)
        $order = $this->createOrder($user, [
            'order_status' => 'SHIPPING',
            'completed_at' => null,
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 150000,
            'quantity' => 1,
            'line_total' => 150000,
        ]);

        $response = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Đang giao hàng chưa nhận được.',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
        $this->assertDatabaseMissing('reviews', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_user_can_review_product_when_order_is_completed(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();

        $order = $this->createOrder($user, [
            'order_status' => 'COMPLETED',
            'completed_at' => now(),
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 200000,
            'quantity' => 1,
            'line_total' => 200000,
        ]);

        $response = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Gấu rất mềm mại và đáng yêu, giao hàng nhanh!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Cảm ơn bạn đã đánh giá sản phẩm!');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Gấu rất mềm mại và đáng yêu, giao hàng nhanh!',
            'is_edited' => false,
        ]);
    }

    public function test_single_order_with_two_different_products_allows_reviewing_both(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $productA = $this->createProduct(['name' => 'Gấu Teddy']);
        $productB = $this->createProduct(['name' => 'Thỏ Bông']);

        $order = $this->createOrder($user, [
            'order_status' => 'COMPLETED',
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $productA->id,
            'product_name' => $productA->name,
            'product_price' => 100000,
            'quantity' => 1,
            'line_total' => 100000,
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $productB->id,
            'product_name' => $productB->name,
            'product_price' => 120000,
            'quantity' => 1,
            'line_total' => 120000,
        ]);

        // Đánh giá sản phẩm A
        $responseA = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $productA->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Gấu Teddy siêu xịn.',
        ]);
        $responseA->assertCreated();

        // Đánh giá sản phẩm B trong cùng đơn hàng
        $responseB = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $productB->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Thỏ bông xinh xắn.',
        ]);
        $responseB->assertCreated();

        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'product_id' => $productA->id]);
        $this->assertDatabaseHas('reviews', ['user_id' => $user->id, 'product_id' => $productB->id]);
    }

    public function test_same_product_in_two_different_completed_orders_can_be_reviewed_twice(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();

        // Đơn 1
        $order1 = $this->createOrder($user, [
            'order_status' => 'COMPLETED',
        ]);
        OrderDetail::create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 150000,
            'quantity' => 1,
            'line_total' => 150000,
        ]);

        // Đơn 2
        $order2 = $this->createOrder($user, [
            'order_status' => 'COMPLETED',
        ]);
        OrderDetail::create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 150000,
            'quantity' => 2,
            'line_total' => 300000,
        ]);

        // Đánh giá đơn 1
        $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'order_id' => $order1->id,
            'rating' => 5,
            'comment' => 'Mua lần 1 rất thích.',
        ])->assertCreated();

        // Đánh giá đơn 2 (chuẩn Shopee)
        $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'order_id' => $order2->id,
            'rating' => 5,
            'comment' => 'Mua lần 2 tặng bạn vẫn ưng ý.',
        ])->assertCreated();

        $this->assertSame(2, Review::query()->where('user_id', $user->id)->where('product_id', $product->id)->count());
    }

    public function test_user_cannot_create_duplicate_review_for_the_same_order(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();

        $order = $this->createOrder($user, [
            'order_status' => 'COMPLETED',
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 200000,
            'quantity' => 1,
            'line_total' => 200000,
        ]);

        // Lần 1: Thành công
        $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Đánh giá lần 1.',
        ])->assertCreated();

        // Lần 2 trong cùng đơn: Bị chặn duplicate
        $response = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Đánh giá lần 2 bị chặn.',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
        $this->assertSame(1, Review::query()->where('user_id', $user->id)->where('product_id', $product->id)->count());
    }

    public function test_validation_requires_valid_rating_between_one_and_five(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();

        // Rating = 0
        $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 0,
            'comment' => 'Test rating 0',
        ])->assertJsonValidationErrors(['rating']);

        // Rating = 6
        $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 6,
            'comment' => 'Test rating 6',
        ])->assertJsonValidationErrors(['rating']);

        // Missing rating
        $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'comment' => 'Test missing rating',
        ])->assertJsonValidationErrors(['rating']);
    }

    public function test_validation_rejects_comment_exceeding_max_characters(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();

        $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => str_repeat('a', 1001),
        ])->assertJsonValidationErrors(['comment']);
    }

    public function test_user_can_edit_review_only_once(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();
        $order = $this->createOrder($user, ['order_status' => 'COMPLETED']);

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Đánh giá ban đầu.',
            'is_edited' => false,
        ]);

        // Sửa lần 1: Thành công
        $this->actingAs($user)->putJson(route('customer.reviews.update', $review), [
            'rating' => 5,
            'comment' => 'Đã sửa lần 1 thành 5 sao tuyệt vời.',
        ])->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue($review->fresh()->is_edited);
        $this->assertSame(5, $review->fresh()->rating);

        // Sửa lần 2: Bị chặn (chỉ được sửa 1 lần duy nhất)
        $this->actingAs($user)->putJson(route('customer.reviews.update', $review), [
            'rating' => 3,
            'comment' => 'Cố tình sửa lần 2.',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['review']);

        $this->assertSame(5, $review->fresh()->rating);
    }

    public function test_user_cannot_edit_or_delete_another_users_review(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $attacker = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();
        $order = $this->createOrder($owner, ['order_status' => 'COMPLETED']);

        $review = Review::create([
            'user_id' => $owner->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Review của chính chủ.',
            'is_edited' => false,
        ]);

        // Attacker cố tình sửa
        $this->actingAs($attacker)->putJson(route('customer.reviews.update', $review), [
            'rating' => 1,
            'comment' => 'Hacker sửa đánh giá.',
        ])->assertStatus(422);

        $this->assertSame('Review của chính chủ.', $review->fresh()->comment);

        // Attacker cố tình xóa
        $this->actingAs($attacker)->deleteJson(route('customer.reviews.destroy', $review))
            ->assertStatus(422);

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    public function test_check_eligibility_api_returns_correct_status(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct(['name' => 'Gấu Teddy Mật Ong 45cm']);

        // Chưa mua
        $response = $this->actingAs($user)->getJson(route('customer.reviews.eligibility', $product));
        $response->assertOk()
            ->assertJsonPath('data.eligible', false);

        // Đã mua và COMPLETED
        $order = $this->createOrder($user, ['order_status' => 'COMPLETED']);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 250000,
            'quantity' => 1,
            'line_total' => 250000,
        ]);

        $responseEligible = $this->actingAs($user)->getJson(route('customer.reviews.eligibility', $product));
        $responseEligible->assertOk()
            ->assertJsonPath('data.eligible', true)
            ->assertJsonPath('data.order_id', $order->id)
            ->assertJsonPath('data.product.name', 'Gấu Teddy Mật Ong 45cm');
    }

    public function test_customer_can_view_reviews_index_page(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $response = $this->actingAs($user)->get(route('customer.reviews.index'));
        $response->assertOk()
            ->assertSee('Đánh giá của tôi');
    }

    public function test_get_order_review_data_returns_all_products_in_order(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $p1 = $this->createProduct(['name' => 'Sản phẩm 1']);
        $p2 = $this->createProduct(['name' => 'Sản phẩm 2']);

        $order = $this->createOrder($user, ['order_status' => 'COMPLETED']);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'product_price' => 100000,
            'quantity' => 1,
            'line_total' => 100000,
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $p2->id,
            'product_name' => $p2->name,
            'product_price' => 150000,
            'quantity' => 2,
            'line_total' => 300000,
        ]);

        $response = $this->actingAs($user)->getJson(route('customer.reviews.order', $order));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_id', $order->id)
            ->assertJsonPath('data.order_code', $order->order_code)
            ->assertJsonCount(2, 'data.items')
            ->assertJsonPath('data.items.0.product_id', $p1->id)
            ->assertJsonPath('data.items.0.product_name', 'Sản phẩm 1')
            ->assertJsonPath('data.items.0.review', null)
            ->assertJsonPath('data.items.1.product_id', $p2->id)
            ->assertJsonPath('data.items.1.product_name', 'Sản phẩm 2');
    }

    public function test_user_can_review_multiple_products_in_one_completed_order_simultaneously(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $p1 = $this->createProduct(['name' => 'Gấu Teddy Nâu']);
        $p2 = $this->createProduct(['name' => 'Gấu Bông Thỏ Hồng']);

        $order = $this->createOrder($user, ['order_status' => 'COMPLETED']);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'product_price' => 120000,
            'quantity' => 1,
            'line_total' => 120000,
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $p2->id,
            'product_name' => $p2->name,
            'product_price' => 180000,
            'quantity' => 1,
            'line_total' => 180000,
        ]);

        // Submit cả 2 sản phẩm cùng lúc
        $payload = [
            'order_id' => $order->id,
            'items' => [
                [
                    'product_id' => $p1->id,
                    'rating' => 5,
                    'comment' => 'Gấu Teddy Nâu cực kỳ êm ái, đóng gói cẩn thận!',
                ],
                [
                    'product_id' => $p2->id,
                    'rating' => 4,
                    'comment' => 'Thỏ Hồng xinh xắn đáng yêu.',
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson(route('customer.reviews.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Cảm ơn bạn đã đánh giá đơn hàng!');

        // Cả 2 đánh giá được lưu trong DB
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $p1->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Gấu Teddy Nâu cực kỳ êm ái, đóng gói cẩn thận!',
            'is_edited' => false,
        ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $p2->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Thỏ Hồng xinh xắn đáng yêu.',
            'is_edited' => false,
        ]);

        // Kiểm tra API lấy lại thông tin đơn hàng đã có review đính kèm
        $orderDataResponse = $this->actingAs($user)->getJson(route('customer.reviews.order', $order));
        $orderDataResponse->assertOk()
            ->assertJsonPath('data.items.0.review.rating', 5)
            ->assertJsonPath('data.items.1.review.rating', 4);
    }

    public function test_user_cannot_view_order_review_data_of_another_user(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $stranger = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();

        $order = $this->createOrder($owner, ['order_status' => 'COMPLETED']);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 100000,
            'quantity' => 1,
            'line_total' => 100000,
        ]);

        $response = $this->actingAs($stranger)->getJson(route('customer.reviews.order', $order));
        $response->assertStatus(422);
    }

    public function test_user_can_edit_batch_order_reviews_only_once(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $p1 = $this->createProduct(['name' => 'Gấu Bông 1']);
        $p2 = $this->createProduct(['name' => 'Gấu Bông 2']);

        $order = $this->createOrder($user, ['order_status' => 'COMPLETED']);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'product_price' => 100000,
            'quantity' => 1,
            'line_total' => 100000,
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $p2->id,
            'product_name' => $p2->name,
            'product_price' => 100000,
            'quantity' => 1,
            'line_total' => 100000,
        ]);

        // Lần 1: Tạo mới đánh giá
        $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'order_id' => $order->id,
            'items' => [
                ['product_id' => $p1->id, 'rating' => 4, 'comment' => 'Comment gốc 1'],
                ['product_id' => $p2->id, 'rating' => 4, 'comment' => 'Comment gốc 2'],
            ],
        ])->assertCreated();

        $r1 = Review::where('user_id', $user->id)->where('product_id', $p1->id)->where('order_id', $order->id)->first();
        $r2 = Review::where('user_id', $user->id)->where('product_id', $p2->id)->where('order_id', $order->id)->first();
        $this->assertFalse((bool) $r1->is_edited);
        $this->assertFalse((bool) $r2->is_edited);

        // Lần 2: Sửa đánh giá lần 1 (cho phép)
        $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'order_id' => $order->id,
            'items' => [
                ['product_id' => $p1->id, 'rating' => 5, 'comment' => 'Comment đã sửa lần 1'],
                ['product_id' => $p2->id, 'rating' => 5, 'comment' => 'Comment đã sửa lần 2'],
            ],
        ])->assertCreated();

        $r1->refresh();
        $r2->refresh();
        $this->assertSame('Comment đã sửa lần 1', $r1->comment);
        $this->assertSame('Comment đã sửa lần 2', $r2->comment);
        $this->assertTrue((bool) $r1->is_edited);
        $this->assertTrue((bool) $r2->is_edited);

        // Lần 3: Cố tình gửi sửa lần 2 (bị bỏ qua vì đã sửa 1 lần duy nhất)
        $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'order_id' => $order->id,
            'items' => [
                ['product_id' => $p1->id, 'rating' => 1, 'comment' => 'Comment cố tình sửa lần 2'],
                ['product_id' => $p2->id, 'rating' => 1, 'comment' => 'Comment cố tình sửa lần 2'],
            ],
        ])->assertCreated();

        $r1->refresh();
        $r2->refresh();
        $this->assertSame('Comment đã sửa lần 1', $r1->comment);
        $this->assertSame('Comment đã sửa lần 2', $r2->comment);
    }

    public function test_user_cannot_upload_more_than_5_images_per_review(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();

        $order = $this->createOrder($user, ['order_status' => 'COMPLETED']);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 100000,
            'quantity' => 1,
            'line_total' => 100000,
        ]);

        // 6 ảnh (vượt quá giới hạn 5 ảnh)
        $sixImages = [
            UploadedFile::fake()->image('img1.jpg'),
            UploadedFile::fake()->image('img2.jpg'),
            UploadedFile::fake()->image('img3.jpg'),
            UploadedFile::fake()->image('img4.jpg'),
            UploadedFile::fake()->image('img5.jpg'),
            UploadedFile::fake()->image('img6.jpg'),
        ];

        $response = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'order_id' => $order->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'rating' => 5,
                    'comment' => 'Đánh giá kèm 6 ảnh',
                    'images' => $sixImages,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.images']);

        // 5 ảnh (hợp lệ trong giới hạn 5 ảnh)
        $fiveImages = array_slice($sixImages, 0, 5);

        $responseValid = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'order_id' => $order->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'rating' => 5,
                    'comment' => 'Đánh giá kèm đúng 5 ảnh hợp lệ',
                    'images' => $fiveImages,
                ],
            ],
        ]);

        $responseValid->assertCreated();
    }

    public function test_my_reviews_page_shows_only_completed_order_unreviewed_products_in_pending_tab(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        // TC01: Order PENDING
        $pPending = $this->createProduct(['name' => 'Gấu Order Pending']);
        $oPending = $this->createOrder($user, ['order_status' => 'PENDING', 'completed_at' => null]);
        OrderDetail::create(['order_id' => $oPending->id, 'product_id' => $pPending->id, 'product_name' => $pPending->name, 'product_price' => 100000, 'quantity' => 1, 'line_total' => 100000]);

        // TC02: Order SHIPPING
        $pShipping = $this->createProduct(['name' => 'Gấu Order Shipping']);
        $oShipping = $this->createOrder($user, ['order_status' => 'SHIPPING', 'completed_at' => null]);
        OrderDetail::create(['order_id' => $oShipping->id, 'product_id' => $pShipping->id, 'product_name' => $pShipping->name, 'product_price' => 100000, 'quantity' => 1, 'line_total' => 100000]);

        // TC03: Order CANCELLED
        $pCancelled = $this->createProduct(['name' => 'Gấu Order Cancelled']);
        $oCancelled = $this->createOrder($user, ['order_status' => 'CANCELLED', 'completed_at' => null]);
        OrderDetail::create(['order_id' => $oCancelled->id, 'product_id' => $pCancelled->id, 'product_name' => $pCancelled->name, 'product_price' => 100000, 'quantity' => 1, 'line_total' => 100000]);

        // TC04: Order COMPLETED (Chưa đánh giá)
        $pCompleted = $this->createProduct(['name' => 'Gấu Order Completed Chưa Đánh Giá']);
        $oCompleted = $this->createOrder($user, ['order_status' => 'COMPLETED', 'completed_at' => now()]);
        OrderDetail::create(['order_id' => $oCompleted->id, 'product_id' => $pCompleted->id, 'product_name' => $pCompleted->name, 'product_price' => 100000, 'quantity' => 1, 'line_total' => 100000]);

        // TC05: Order COMPLETED (Đã đánh giá)
        $pReviewed = $this->createProduct(['name' => 'Gấu Order Completed Đã Đánh Giá']);
        $oReviewed = $this->createOrder($user, ['order_status' => 'COMPLETED', 'completed_at' => now()]);
        OrderDetail::create(['order_id' => $oReviewed->id, 'product_id' => $pReviewed->id, 'product_name' => $pReviewed->name, 'product_price' => 100000, 'quantity' => 1, 'line_total' => 100000]);
        Review::create([
            'user_id' => $user->id,
            'product_id' => $pReviewed->id,
            'order_id' => $oReviewed->id,
            'rating' => 5,
            'comment' => 'Đã đánh giá rồi.',
        ]);

        $response = $this->actingAs($user)->get(route('customer.reviews.index'));
        $response->assertOk();

        // Kiểm tra biến truyền vào view
        $response->assertViewHas('pendingCount', 1);
        $response->assertViewHas('reviewedCount', 1);

        // Kiểm tra HTML hiển thị đúng sản phẩm
        $response->assertSee('Gấu Order Completed Chưa Đánh Giá');
        $response->assertDontSee('Gấu Order Pending');
        $response->assertDontSee('Gấu Order Shipping');
        $response->assertDontSee('Gấu Order Cancelled');
    }

    public function test_my_reviews_page_deduplicates_same_product_in_same_order(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct(['name' => 'Gấu Teddy Mua 2 Lần']);

        // Mua cùng 1 sản phẩm 2 dòng trong cùng 1 đơn COMPLETED
        $o1 = $this->createOrder($user, ['order_status' => 'COMPLETED', 'completed_at' => now()->subDays(2)]);
        OrderDetail::create(['order_id' => $o1->id, 'product_id' => $product->id, 'product_name' => $product->name, 'product_price' => 100000, 'quantity' => 1, 'line_total' => 100000]);
        OrderDetail::create(['order_id' => $o1->id, 'product_id' => $product->id, 'product_name' => $product->name, 'product_price' => 100000, 'quantity' => 1, 'line_total' => 100000]);

        $response = $this->actingAs($user)->get(route('customer.reviews.index'));
        $response->assertOk();

        // Trong cùng 1 đơn hàng, cùng 1 sản phẩm chỉ hiển thị 1 dòng để đánh giá
        $response->assertViewHas('pendingCount', 1);
        $pendingItems = $response->viewData('pendingItems');
        $this->assertCount(1, $pendingItems);
    }

    public function test_my_reviews_page_shows_multiple_products_in_same_order(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $p1 = $this->createProduct(['name' => 'Gấu Bông Nâu']);
        $p2 = $this->createProduct(['name' => 'Hộp Quà Kèm Thiệp']);

        // 1 đơn COMPLETED chứa 2 sản phẩm khác nhau
        $order = $this->createOrder($user, ['order_status' => 'COMPLETED']);
        OrderDetail::create(['order_id' => $order->id, 'product_id' => $p1->id, 'product_name' => $p1->name, 'product_price' => 200000, 'quantity' => 1, 'line_total' => 200000]);
        OrderDetail::create(['order_id' => $order->id, 'product_id' => $p2->id, 'product_name' => $p2->name, 'product_price' => 50000, 'quantity' => 1, 'line_total' => 50000]);

        $response = $this->actingAs($user)->get(route('customer.reviews.index'));
        $response->assertOk();

        // Cả 2 sản phẩm đều phải xuất hiện trong danh sách chưa đánh giá
        $response->assertViewHas('pendingCount', 2);
        $response->assertSee('Gấu Bông Nâu');
        $response->assertSee('Hộp Quà Kèm Thiệp');
    }

    public function test_my_reviews_page_reviewed_tab_shows_only_own_reviews(): void
    {
        $user1 = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $user2 = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $p1 = $this->createProduct(['name' => 'Sản phẩm của User 1']);
        $o1 = $this->createOrder($user1, ['order_status' => 'COMPLETED']);
        Review::create(['user_id' => $user1->id, 'product_id' => $p1->id, 'order_id' => $o1->id, 'rating' => 5, 'comment' => 'Đánh giá tuyệt vời của User 1']);

        $p2 = $this->createProduct(['name' => 'Sản phẩm của User 2']);
        $o2 = $this->createOrder($user2, ['order_status' => 'COMPLETED']);
        Review::create(['user_id' => $user2->id, 'product_id' => $p2->id, 'order_id' => $o2->id, 'rating' => 1, 'comment' => 'Đánh giá bí mật của User 2']);

        // User 1 xem trang Đánh giá của tôi
        $response = $this->actingAs($user1)->get(route('customer.reviews.index', ['tab' => 'reviewed']));
        $response->assertOk();

        $response->assertSee('Đánh giá tuyệt vời của User 1');
        $response->assertDontSee('Đánh giá bí mật của User 2');
        $response->assertViewHas('reviewedCount', 1);
    }

    public function test_customer_can_delete_own_review_and_product_returns_to_pending(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct(['name' => 'Gấu Bông Xóa Đánh Giá']);
        $order = $this->createOrder($user, ['order_status' => 'COMPLETED']);
        OrderDetail::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => $product->name, 'product_price' => 100000, 'quantity' => 1, 'line_total' => 100000]);

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Muốn xóa để viết lại sau',
        ]);

        // Ban đầu: pendingCount = 0, reviewedCount = 1
        $resBefore = $this->actingAs($user)->get(route('customer.reviews.index'));
        $resBefore->assertViewHas('pendingCount', 0);
        $resBefore->assertViewHas('reviewedCount', 1);

        // Xóa review của chính mình
        $deleteRes = $this->actingAs($user)->delete(route('customer.reviews.destroy', $review));
        $deleteRes->assertRedirect();
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);

        // Sau khi xóa: review đã biến mất, sản phẩm quay lại tab Chưa đánh giá (pendingCount = 1, reviewedCount = 0)
        $resAfter = $this->actingAs($user)->get(route('customer.reviews.index'));
        $resAfter->assertViewHas('pendingCount', 1);
        $resAfter->assertViewHas('reviewedCount', 0);
        $resAfter->assertSee('Gấu Bông Xóa Đánh Giá');
    }

    public function test_customer_cannot_delete_other_user_review(): void
    {
        $user1 = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $user2 = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

        $product = $this->createProduct();
        $order = $this->createOrder($user1, ['order_status' => 'COMPLETED']);
        $review = Review::create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Đánh giá của user 1',
        ]);

        // User 2 cố tình gửi request xóa review của User 1
        $response = $this->actingAs($user2)->deleteJson(route('customer.reviews.destroy', $review));
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['review']);

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'deleted_at' => null]);
    }

    public function test_cannot_review_order_after_30_days_from_completed(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct(['name' => 'Gấu Bông Quá Hạn 30 Ngày']);

        // Đơn hàng hoàn thành cách đây 35 ngày (> 30 ngày)
        $oldOrder = $this->createOrder($user, [
            'order_status' => 'COMPLETED',
            'completed_at' => now()->subDays(35),
        ]);
        OrderDetail::create([
            'order_id' => $oldOrder->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 150000,
            'quantity' => 1,
            'line_total' => 150000,
        ]);

        // Kiểm tra danh sách chưa đánh giá: không hiển thị đơn hàng đã quá 30 ngày
        $response = $this->actingAs($user)->get(route('customer.reviews.index'));
        $response->assertOk();
        $response->assertViewHas('pendingCount', 0);
        $response->assertDontSee('Gấu Bông Quá Hạn 30 Ngày');

        // Kiểm tra API check eligibility: báo lỗi quá 30 ngày
        $checkRes = $this->actingAs($user)->getJson(route('customer.reviews.eligibility', [
            'product' => $product,
            'order_id' => $oldOrder->id,
        ]));
        $checkRes->assertOk();
        $this->assertFalse($checkRes->json('data.eligible'));
        $this->assertStringContainsString('30 ngày', $checkRes->json('data.message'));

        // Cố tình gửi đánh giá cho đơn hàng quá 30 ngày: bị từ chối
        $submitRes = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'order_id' => $oldOrder->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'rating' => 5,
                    'comment' => 'Thử đánh giá đơn hàng đã quá hạn 30 ngày',
                ],
            ],
        ]);
        $submitRes->assertStatus(422)
            ->assertJsonValidationErrors(['order']);
    }

    public function test_user_can_create_review_with_images_and_they_are_displayed_in_reviewed_tab(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct(['name' => 'Gấu Bông Có Ảnh Đánh Giá']);
        $order = $this->createOrder($user);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 180000,
            'quantity' => 1,
            'line_total' => 180000,
        ]);

        $imageFile1 = UploadedFile::fake()->image('review_photo1.jpg', 500, 500);
        $imageFile2 = UploadedFile::fake()->image('review_photo2.png', 500, 500);

        $response = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'order_id' => $order->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'rating' => 5,
                    'comment' => 'Sản phẩm có kèm ảnh chụp thực tế rất xinh.',
                    'images' => [$imageFile1, $imageFile2],
                ],
            ],
        ]);

        $response->assertCreated();

        $review = Review::where('user_id', $user->id)->where('product_id', $product->id)->first();
        $this->assertNotNull($review);
        $this->assertIsArray($review->images);
        $this->assertCount(2, $review->images);

        // Xem tab đã đánh giá
        $viewRes = $this->actingAs($user)->get(route('customer.reviews.index', ['tab' => 'reviewed']));
        $viewRes->assertOk();
        $viewRes->assertSee('my-reviews-reviewed-photos');
        $viewRes->assertSee($review->images[0]);
        $viewRes->assertSee($review->images[1]);
    }

    public function test_user_can_create_review_with_5_images(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct(['name' => 'Gấu Bông Đủ 5 Ảnh']);
        $order = $this->createOrder($user);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 200000,
            'quantity' => 1,
            'line_total' => 200000,
        ]);

        $files = [
            UploadedFile::fake()->image('p1.jpg', 400, 400),
            UploadedFile::fake()->image('p2.jpg', 400, 400),
            UploadedFile::fake()->image('p3.jpg', 400, 400),
            UploadedFile::fake()->image('p4.jpg', 400, 400),
            UploadedFile::fake()->image('p5.jpg', 400, 400),
        ];

        $response = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'order_id' => $order->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'rating' => 5,
                    'comment' => 'Đánh giá có đúng 5 ảnh.',
                    'images' => $files,
                ],
            ],
        ]);

        $response->assertCreated();

        $review = Review::where('user_id', $user->id)->where('product_id', $product->id)->first();
        $this->assertNotNull($review);
        $this->assertIsArray($review->images);
        $this->assertCount(5, $review->images);

        // Kiểm tra API order-review-data cũng trả về images
        $orderDataRes = $this->actingAs($user)->getJson(route('customer.reviews.order', $order));
        $orderDataRes->assertOk();
        $this->assertCount(5, $orderDataRes->json('data.items.0.review.images'));
    }

    public function test_user_can_edit_review_and_preserve_or_update_images(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct(['name' => 'Gấu Bông Sửa Đánh Giá']);
        $order = $this->createOrder($user);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => 150000,
            'quantity' => 1,
            'line_total' => 150000,
        ]);

        // Tạo review ban đầu có 2 ảnh
        $initialReview = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Nhận xét ban đầu',
            'images' => ['https://example.com/img1.jpg', 'https://example.com/img2.jpg'],
            'is_edited' => false,
        ]);

        $newUploadedFile = UploadedFile::fake()->image('new_p.jpg', 500, 500);

        // Chỉnh sửa đánh giá: giữ 1 ảnh cũ, thêm 1 ảnh mới
        $response = $this->actingAs($user)->postJson(route('customer.reviews.store'), [
            'order_id' => $order->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'review_id' => $initialReview->id,
                    'rating' => 5,
                    'comment' => 'Nhận xét sau khi sửa',
                    'existing_images' => ['https://example.com/img1.jpg'],
                    'images' => [$newUploadedFile],
                ],
            ],
        ]);

        $response->assertCreated();

        $updated = $initialReview->fresh();
        $this->assertTrue($updated->is_edited);
        $this->assertEquals('Nhận xét sau khi sửa', $updated->comment);
        $this->assertEquals(5, $updated->rating);
        $this->assertIsArray($updated->images);
        $this->assertCount(2, $updated->images);
        $this->assertEquals('https://example.com/img1.jpg', $updated->images[0]);
        $this->assertStringContainsString('storage/reviews/', $updated->images[1]);
    }

    public function test_user_cannot_edit_review_after_7_days(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product = $this->createProduct();
        $order = $this->createOrder($user, ['order_status' => 'COMPLETED']);

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Đánh giá từ 8 ngày trước.',
            'is_edited' => false,
        ]);

        // Giả lập review đã được tạo 8 ngày trước
        $review->created_at = now()->subDays(8);
        $review->save();

        $this->assertFalse($review->fresh()->canBeEdited());

        // Cố tình chỉnh sửa review sau 7 ngày: Bị từ chối
        $response = $this->actingAs($user)->putJson(route('customer.reviews.update', $review), [
            'rating' => 5,
            'comment' => 'Cố tình sửa sau 7 ngày.',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['review']);

        $this->assertStringContainsString('7 ngày', $response->json('errors.review.0'));
        $this->assertSame('Đánh giá từ 8 ngày trước.', $review->fresh()->comment);
    }

    public function test_reviewed_tab_hides_edit_button_after_7_days_and_never_shows_da_sua_button(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $product1 = $this->createProduct(['name' => 'Sản phẩm có thể sửa']);
        $product2 = $this->createProduct(['name' => 'Sản phẩm quá 7 ngày']);
        $product3 = $this->createProduct(['name' => 'Sản phẩm đã từng sửa']);

        $order = $this->createOrder($user, ['order_status' => 'COMPLETED']);

        // Review 1: Có thể sửa (mới tạo hôm nay, chưa sửa)
        $review1 = Review::create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Review mới 1',
            'is_edited' => false,
        ]);

        // Review 2: Hết hạn sửa (tạo 10 ngày trước, chưa sửa)
        $review2 = Review::create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Review cũ quá 7 ngày',
            'is_edited' => false,
        ]);
        $review2->created_at = now()->subDays(10);
        $review2->save();

        // Review 3: Đã từng chỉnh sửa
        $review3 = Review::create([
            'user_id' => $user->id,
            'product_id' => $product3->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Review đã sửa',
            'is_edited' => true,
        ]);

        $this->assertTrue($review1->fresh()->canBeEdited());
        $this->assertFalse($review2->fresh()->canBeEdited());
        $this->assertFalse($review3->fresh()->canBeEdited());

        // Truy cập trang Đã đánh giá
        $response = $this->actingAs($user)->get(route('customer.reviews.index', ['tab' => 'reviewed']));
        $response->assertOk();

        // 1. Tuyệt đối không còn nút "Đã sửa (1/1)" hoặc class nút is-edited
        $response->assertDontSee('Đã sửa (1/1)');
        $response->assertDontSee('my-reviews-action-btn is-edited');

        // 2. Nút "Sửa đánh giá" CHỈ xuất hiện 1 lần cho review1, không xuất hiện cho review2 hay review3
        $content = $response->getContent();
        $this->assertSame(1, substr_count($content, 'Sửa đánh giá'));
        $this->assertStringContainsString('data-review-id="'.$review1->id.'"', $content);
        $this->assertStringNotContainsString('data-review-id="'.$review2->id.'"', $content);
        $this->assertStringNotContainsString('data-review-id="'.$review3->id.'"', $content);
    }
}



