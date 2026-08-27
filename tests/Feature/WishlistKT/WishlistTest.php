<?php

namespace Tests\Feature\WishlistKT;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_use_wishlist(): void
    {
        $product = $this->createProduct();

        $this->getJson(route('customer.wishlist.index'))->assertUnauthorized();
        $this->deleteJson(route('customer.wishlist.clear'))->assertUnauthorized();
        $this->deleteJson(route('customer.wishlist.destroy', $product))->assertUnauthorized();
        $this->postJson(route('customer.cart.store', $product))->assertUnauthorized();
    }

    public function test_customer_can_render_wishlist_page(): void
    {
        $customer = User::factory()->create();
        $product = $this->createProduct();

        WishlistItem::query()->create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.wishlist.index'))
            ->assertOk()
            ->assertViewIs('customer.wishlistKT.index')
            ->assertSee($product->name)
            ->assertSee('Danh sách yêu thích');
    }

    public function test_staff_and_admin_cannot_use_customer_wishlist(): void
    {
        foreach ([User::ROLE_STAFF, User::ROLE_ADMIN] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->getJson(route('customer.wishlist.index'))
                ->assertForbidden();
        }
    }

    public function test_empty_wishlist_returns_successful_response(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->getJson(route('customer.wishlist.index'))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Danh sách yêu thích đang trống.')
            ->assertJsonCount(0, 'data.items');
    }

    public function test_out_of_stock_product_remains_visible_without_changing_stock(): void
    {
        $customer = User::factory()->create();
        $product = $this->createProduct(['stock_quantity' => 0]);

        WishlistItem::query()->create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($customer)
            ->getJson(route('customer.wishlist.index'))
            ->assertOk()
            ->assertJsonPath('data.items.0.product_id', $product->id)
            ->assertJsonPath('data.items.0.stock_quantity', 0);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);
        $this->assertSame(0, $product->fresh()->stock_quantity);
    }

    public function test_customer_only_sees_own_wishlist_with_expected_product_data(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $ownProduct = $this->createProduct();
        $otherProduct = $this->createProduct();

        ProductImage::query()->create([
            'product_id' => $ownProduct->id,
            'image_url' => '/images/bear.jpg',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        WishlistItem::query()->create([
            'user_id' => $customer->id,
            'product_id' => $ownProduct->id,
        ]);
        WishlistItem::query()->create([
            'user_id' => $otherCustomer->id,
            'product_id' => $otherProduct->id,
        ]);

        $this->actingAs($customer)
            ->getJson(route('customer.wishlist.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.product_id', $ownProduct->id)
            ->assertJsonPath('data.items.0.primary_image', '/images/bear.jpg')
            ->assertJsonPath('data.pagination.total', 1);
    }

    public function test_saved_product_remains_visible_after_becoming_inactive(): void
    {
        $customer = User::factory()->create();
        $product = $this->createProduct();

        WishlistItem::query()->create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);
        $product->update(['status' => Product::STATUS_INACTIVE]);

        $this->actingAs($customer)
            ->getJson(route('customer.wishlist.index'))
            ->assertOk()
            ->assertJsonPath('data.items.0.status', Product::STATUS_INACTIVE);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_can_delete_only_own_wishlist_item(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $ownProduct = $this->createProduct();
        $otherProduct = $this->createProduct();

        WishlistItem::query()->create([
            'user_id' => $customer->id,
            'product_id' => $ownProduct->id,
        ]);
        WishlistItem::query()->create([
            'user_id' => $otherCustomer->id,
            'product_id' => $otherProduct->id,
        ]);

        $this->actingAs($customer)
            ->deleteJson(route('customer.wishlist.destroy', $ownProduct))
            ->assertOk();

        $this->actingAs($customer)
            ->deleteJson(route('customer.wishlist.destroy', $otherProduct))
            ->assertNotFound();

        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $customer->id,
            'product_id' => $ownProduct->id,
        ]);
        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $otherCustomer->id,
            'product_id' => $otherProduct->id,
        ]);
    }

    public function test_customer_can_clear_only_own_wishlist(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $ownProduct = $this->createProduct();
        $otherProduct = $this->createProduct();

        WishlistItem::query()->create([
            'user_id' => $customer->id,
            'product_id' => $ownProduct->id,
        ]);
        WishlistItem::query()->create([
            'user_id' => $otherCustomer->id,
            'product_id' => $otherProduct->id,
        ]);

        $this->actingAs($customer)
            ->deleteJson(route('customer.wishlist.clear'))
            ->assertOk()
            ->assertJsonPath('data.removed_count', 1);

        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $customer->id,
        ]);
        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $otherCustomer->id,
            'product_id' => $otherProduct->id,
        ]);
    }

    public function test_customer_can_sort_wishlist_by_price(): void
    {
        $customer = User::factory()->create();
        $expensiveProduct = $this->createProduct(['price' => 500000]);
        $affordableProduct = $this->createProduct(['price' => 200000]);

        WishlistItem::query()->create([
            'user_id' => $customer->id,
            'product_id' => $expensiveProduct->id,
        ]);
        WishlistItem::query()->create([
            'user_id' => $customer->id,
            'product_id' => $affordableProduct->id,
        ]);

        $this->actingAs($customer)
            ->getJson(route('customer.wishlist.index', ['sort' => 'price_asc']))
            ->assertOk()
            ->assertJsonPath('data.items.0.product_id', $affordableProduct->id)
            ->assertJsonPath('data.items.1.product_id', $expensiveProduct->id);
    }

    public function test_customer_can_add_wishlist_product_to_cart(): void
    {
        $customer = User::factory()->create();
        $product = $this->createProduct(['stock_quantity' => 5]);

        WishlistItem::query()->create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($customer)
            ->postJson(route('customer.cart.store', $product))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.quantity', 1);

        $this->actingAs($customer)
            ->postJson(route('customer.cart.store', $product))
            ->assertOk()
            ->assertJsonPath('data.quantity', 2);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_customer_cannot_add_unavailable_product_to_cart(): void
    {
        $customer = User::factory()->create();
        $product = $this->createProduct(['stock_quantity' => 0]);

        $this->actingAs($customer)
            ->postJson(route('customer.cart.store', $product))
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_cart_quantity_cannot_exceed_product_stock(): void
    {
        $customer = User::factory()->create();
        $product = $this->createProduct(['stock_quantity' => 1]);

        CartItem::query()->create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($customer)
            ->postJson(route('customer.cart.store', $product))
            ->assertUnprocessable();

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
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
}
