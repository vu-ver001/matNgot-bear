<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'ADMIN']);
    }

    public function test_admin_can_create_user(): void
    {
        $this->actingAs($this->admin);

        $this->post('/admin/users', [
            'full_name' => 'Nguyễn Văn A',
            'email' => 'staff@example.com',
            'phone' => '0912345678',
            'address' => 'Hà Nội',
            'password' => 'password123',
            'role' => 'STAFF',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'email' => 'staff@example.com',
            'role' => 'STAFF',
            'status' => 'ACTIVE',
        ]);
    }

    public function test_create_user_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $this->actingAs($this->admin);

        $this->post('/admin/users', [
            'full_name' => 'Nguyễn Văn B',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'role' => 'CUSTOMER',
        ])->assertSessionHasErrors('email');
    }

    public function test_create_user_requires_min_password(): void
    {
        $this->actingAs($this->admin);

        $this->post('/admin/users', [
            'full_name' => 'Nguyễn Văn C',
            'email' => 'short@example.com',
            'password' => 'short',
            'role' => 'CUSTOMER',
        ])->assertSessionHasErrors('password');
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create(['role' => 'CUSTOMER']);

        $this->actingAs($this->admin);

        $this->put('/admin/users/'.$user->id, [
            'full_name' => 'Tên mới',
            'email' => 'updated@example.com',
            'role' => 'STAFF',
            'password' => '',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'full_name' => 'Tên mới',
            'email' => 'updated@example.com',
            'role' => 'STAFF',
        ]);
    }

    public function test_update_user_keeps_password_when_blank(): void
    {
        $user = User::factory()->create(['role' => 'CUSTOMER']);

        $this->actingAs($this->admin);

        $this->put('/admin/users/'.$user->id, [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'role' => 'CUSTOMER',
        ])->assertRedirect('/admin/users');

        $this->assertEquals($user->fresh()->password, $user->password);
    }

    public function test_admin_can_delete_customer_without_orders(): void
    {
        $customer = User::factory()->create(['role' => 'CUSTOMER']);

        $this->actingAs($this->admin);

        $this->delete('/admin/users/'.$customer->id)->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
    }

    public function test_cannot_delete_user_with_orders(): void
    {
        $customer = User::factory()->create(['role' => 'CUSTOMER']);
        $category = Category::create(['name' => 'Cat', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm test',
            'price' => 100000,
            'stock_quantity' => 10,
            'status' => 'ACTIVE',
        ]);

        app(OrderService::class)->createOrder([
            'customer_id' => $customer->id,
            'recipient_name' => $customer->full_name,
            'recipient_phone' => '0980000000',
            'recipient_address' => 'Hà Nội',
            'payment_method' => 'COD',
        ], [(object) ['product_id' => $product->id, 'quantity' => 1]]);

        $this->actingAs($this->admin);

        $this->delete('/admin/users/'.$customer->id)->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $customer->id]);
    }

    public function test_cannot_delete_self(): void
    {
        $this->actingAs($this->admin);

        $this->delete('/admin/users/'.$this->admin->id)->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_cannot_delete_admin(): void
    {
        $otherAdmin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($this->admin);

        $this->delete('/admin/users/'.$otherAdmin->id)->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
    }

    public function test_update_own_account_keeps_role(): void
    {
        $this->actingAs($this->admin);

        $this->put('/admin/users/'.$this->admin->id, [
            'full_name' => 'Admin Mới',
            'email' => $this->admin->email,
            'role' => 'STAFF',
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'full_name' => 'Admin Mới', 'role' => 'ADMIN']);
    }

    public function test_cannot_self_block(): void
    {
        $this->actingAs($this->admin);

        $this->patch('/admin/users/'.$this->admin->id.'/status', ['status' => 'BLOCKED'])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'role' => 'ADMIN', 'status' => 'ACTIVE']);
    }
}
