<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReviewManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createReview(): Review
    {
        $customer = User::factory()->create(['role' => User::ROLE_CUSTOMER]);
        $category = Category::query()->create(['name' => 'Đồ chơi']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Gấu bông thử nghiệm',
            'price' => 250000,
            'status' => Product::STATUS_ACTIVE,
            'stock_quantity' => 10,
            'sold_count' => 0,
        ]);
        $order = Order::query()->create([
            'order_code' => 'ORD-ADMIN-REVIEW-'.uniqid(),
            'customer_id' => $customer->id,
            'recipient_name' => $customer->full_name,
            'recipient_phone' => '0987654321',
            'recipient_address' => '123 Đường Test, Hà Nội',
            'subtotal' => 250000,
            'total_amount' => 250000,
            'order_status' => 'COMPLETED',
            'payment_method' => 'COD',
            'payment_status' => 'PAID',
            'completed_at' => now(),
        ]);

        return Review::query()->create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Đánh giá cần kiểm tra.',
        ]);
    }

    public function test_admin_can_soft_delete_a_review_from_admin_page(): void
    {
        $admin = User::factory()->admin()->create();
        $review = $this->createReview();

        $response = $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Đã xóa đánh giá thành công.');
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }

    public function test_admin_can_still_toggle_review_visibility(): void
    {
        $admin = User::factory()->admin()->create();
        $review = $this->createReview();

        $response = $this->actingAs($admin)->patch(route('admin.reviews.toggle', $review));

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'is_hidden' => true,
        ]);
    }
}
