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
}
