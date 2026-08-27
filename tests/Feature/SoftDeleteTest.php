<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'full_name' => 'Nguyen Van A',
            'email' => 'user'.uniqid().'@example.com',
            'password' => 'password',
            'role' => 'CUSTOMER',
            'status' => 'ACTIVE',
        ], $attributes));
    }

    private function createAdmin(): User
    {
        return $this->createUser([
            'email' => 'admin'.uniqid().'@matngotbear.com',
            'role' => 'ADMIN',
        ]);
    }

    private function createProduct(?Category $category = null): Product
    {
        $category ??= Category::create(['name' => 'Gau Bong '.uniqid()]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Gau Thu '.uniqid(),
            'price' => 150000,
            'stock_quantity' => 10,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_soft_deleted_user_is_hidden_and_cannot_authenticate(): void
    {
        $user = $this->createUser();
        $user->delete();

        $this->assertSoftDeleted($user);
        $this->assertNull(User::find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
        $this->assertFalse(Auth::attempt([
            'email' => $user->email,
            'password' => 'password',
        ]));
    }

    public function test_email_can_be_reused_after_user_soft_delete(): void
    {
        $user = $this->createUser(['email' => 'reuse@example.com']);
        $user->delete();

        $newUser = User::create([
            'full_name' => 'Tran Thi B',
            'email' => 'reuse@example.com',
            'password' => 'password',
            'role' => 'CUSTOMER',
            'status' => 'ACTIVE',
        ]);

        $this->assertNotNull($newUser->id);
        $this->assertNotSame($user->id, $newUser->id);
    }

    public function test_product_soft_delete_hides_and_restores(): void
    {
        $product = $this->createProduct();
        $product->delete();

        $this->assertSoftDeleted($product);
        $this->assertNull(Product::find($product->id));
        $this->assertNotNull(Product::withTrashed()->find($product->id));

        $product->restore();

        $this->assertNotNull(Product::find($product->id));
    }

    public function test_category_with_active_product_cannot_be_deleted(): void
    {
        $category = Category::create(['name' => 'Danh muc co san pham']);
        $this->createProduct($category);

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(400);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_with_only_trashed_products_can_be_soft_deleted(): void
    {
        $category = Category::create(['name' => 'Danh muc san pham da xoa']);
        $product = $this->createProduct($category);
        $product->delete();

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertSoftDeleted($category->fresh());
    }

    public function test_voucher_code_can_be_reused_after_soft_delete(): void
    {
        $voucher = Voucher::create([
            'code' => 'SALE'.rand(1000, 9999),
            'discount_type' => 'PERCENTAGE',
            'discount_value' => 10,
            'min_order_value' => 0,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 100,
            'used_count' => 0,
            'status' => 'ACTIVE',
        ]);
        $voucher->delete();

        $newVoucher = Voucher::create([
            'code' => $voucher->code,
            'discount_type' => 'FIXED',
            'discount_value' => 50000,
            'min_order_value' => 0,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 50,
            'used_count' => 0,
            'status' => 'ACTIVE',
        ]);

        $this->assertNotNull($newVoucher->id);
        $this->assertSame(1, Voucher::where('code', $voucher->code)->count());
        $this->assertSame(2, Voucher::withTrashed()->where('code', $voucher->code)->count());
    }

    public function test_review_can_be_recreated_after_soft_delete(): void
    {
        $customer = $this->createUser();
        $product = $this->createProduct();

        $order = Order::create([
            'order_code' => 'MNB'.strtoupper(uniqid()),
            'customer_id' => $customer->id,
            'recipient_name' => $customer->full_name,
            'recipient_phone' => '0123456789',
            'recipient_address' => 'Ha Noi',
            'subtotal' => 150000,
            'total_amount' => 180000,
            'order_status' => 'COMPLETED',
            'payment_method' => 'COD',
            'payment_status' => 'PAID',
        ]);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'San pham tuyet voi',
        ]);
        $review->delete();

        $recreated = Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Review lai sau xoa mem',
        ]);

        $this->assertNotNull($recreated->id);
        $this->assertSoftDeleted($review);
    }
}
