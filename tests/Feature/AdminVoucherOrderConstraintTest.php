<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVoucherOrderConstraintTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function createSampleVoucher(array $attributes = []): Voucher
    {
        return Voucher::create(array_merge([
            'code' => 'DISCOUNT' . rand(1000, 9999),
            'voucher_type' => 'ORDER',
            'discount_type' => 'FIXED',
            'discount_value' => 20000,
            'min_order_value' => 100000,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 50,
            'used_count' => 0,
            'status' => 'ACTIVE',
        ], $attributes));
    }

    private function createSampleOrder(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'order_code' => 'MNB' . strtoupper(uniqid()),
            'customer_id' => $this->customer->id,
            'recipient_name' => 'Nguyễn Văn A',
            'recipient_phone' => '0901234567',
            'recipient_address' => '123 Đường Test, Quận 1, TP.HCM',
            'subtotal' => 150000,
            'discount_amount' => 20000,
            'shipping_fee' => 30000,
            'total_amount' => 160000,
            'order_status' => 'PENDING',
            'payment_method' => 'COD',
            'payment_status' => 'UNPAID',
        ], $attributes));
    }

    /**
     * Voucher đang diễn ra (ACTIVE và còn hạn/lượt) -> Không cho xóa
     */
    public function test_cannot_delete_running_voucher(): void
    {
        $voucher = $this->createSampleVoucher([
            'status' => 'ACTIVE',
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'usage_limit' => 50,
            'used_count' => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.vouchers.destroy', $voucher));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'deleted_at' => null]);
    }

    /**
     * Voucher đã vô hiệu hóa (INACTIVE) -> Cho phép xóa mềm
     */
    public function test_can_soft_delete_inactive_voucher(): void
    {
        $voucher = $this->createSampleVoucher([
            'status' => 'INACTIVE',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.vouchers.destroy', $voucher));

        $response->assertSessionHas('success');
        $this->assertSoftDeleted('vouchers', ['id' => $voucher->id]);
    }

    /**
     * Voucher đang có đơn hàng chưa hoàn tất -> Không thể xóa
     */
    public function test_cannot_delete_voucher_with_active_orders(): void
    {
        $voucher = $this->createSampleVoucher([
            'usage_limit' => 1,
            'used_count' => 1,
        ]);

        $this->createSampleOrder([
            'voucher_id' => $voucher->id,
            'order_status' => 'PREPARING',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.vouchers.destroy', $voucher));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'deleted_at' => null]);
    }

    /**
     * Voucher đã xóa mềm KHÔNG hiển thị trên trang danh sách chính mà chỉ hiển thị trong Thùng rác
     */
    public function test_trashed_voucher_not_shown_on_main_index_but_shown_in_trash(): void
    {
        $voucher = $this->createSampleVoucher([
            'code' => 'TRASHEDTEST1',
        ]);
        $voucher->delete();

        // 1. Kiểm tra danh sách chính KHÔNG hiển thị voucher đã xóa mềm
        $indexResponse = $this->actingAs($this->admin)->get(route('admin.vouchers.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('TRASHEDTEST1');

        // 2. Kiểm tra khi vào Thùng rác (?status=TRASHED) thì hiển thị và có nút Khôi phục
        $trashResponse = $this->actingAs($this->admin)->get(route('admin.vouchers.index', ['status' => 'TRASHED']));
        $trashResponse->assertStatus(200);
        $trashResponse->assertSee('TRASHEDTEST1');
        $trashResponse->assertSee('Khôi phục');

        // 3. Bấm khôi phục
        $restoreResponse = $this->actingAs($this->admin)
            ->post(route('admin.vouchers.restore', $voucher->id));

        $restoreResponse->assertSessionHas('success');
        $this->assertNull($voucher->fresh()->deleted_at);

        // 4. Sau khi khôi phục, hiển thị lại trên trang danh sách chính
        $mainResponse = $this->actingAs($this->admin)->get(route('admin.vouchers.index'));
        $mainResponse->assertStatus(200);
        $mainResponse->assertSee('TRASHEDTEST1');
    }

    /**
     * Voucher đã hết hạn nhưng chưa xóa mềm vẫn hiển thị nút khôi phục ở cột thao tác
     */
    public function test_expired_non_trashed_voucher_shows_restore_button(): void
    {
        $voucher = $this->createSampleVoucher([
            'code' => 'EXPIREDACTIVE1',
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDay(),
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.vouchers.index'));
        $response->assertStatus(200);
        $response->assertSee('EXPIREDACTIVE1');
        $response->assertSee('Khôi phục');
    }

    /**
     * Khôi phục voucher kèm gia hạn thời gian và tăng lượt dùng
     */
    public function test_can_restore_voucher_with_extended_end_date_and_usage_limit(): void
    {
        $oldEndDate = now()->subDays(2);
        $voucher = $this->createSampleVoucher([
            'code' => 'EXPIRED123',
            'start_date' => now()->subDays(10),
            'end_date' => $oldEndDate,
            'usage_limit' => 5,
            'used_count' => 5,
            'status' => 'INACTIVE',
        ]);
        $voucher->delete();

        $newEndDate = now()->addDays(15)->format('Y-m-d\TH:i');
        $response = $this->actingAs($this->admin)
            ->post(route('admin.vouchers.restore', $voucher->id), [
                'end_date' => $newEndDate,
                'usage_limit' => 20,
            ]);

        $response->assertSessionHas('success');
        $refreshed = $voucher->fresh();
        $this->assertNull($refreshed->deleted_at);
        $this->assertEquals(20, $refreshed->usage_limit);
        $this->assertEquals('ACTIVE', $refreshed->status);
        $this->assertTrue($refreshed->end_date->gt(now()));
    }

    /**
     * Không thể gia hạn lượt dùng nhỏ hơn số lượt đã sử dụng
     */
    public function test_cannot_restore_voucher_with_usage_limit_lower_than_used_count(): void
    {
        $voucher = $this->createSampleVoucher([
            'code' => 'LIMITTEST1',
            'usage_limit' => 10,
            'used_count' => 8,
        ]);
        $voucher->delete();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.vouchers.restore', $voucher->id), [
                'usage_limit' => 5, // nhỏ hơn 8 đã dùng
            ]);

        $response->assertSessionHasErrors(['usage_limit']);
        $this->assertNotNull($voucher->fresh()->deleted_at);
    }

    /**
     * Không thể gia hạn với thời gian kết thúc ở quá khứ hoặc hiện tại (phải ở tương lai)
     */
    public function test_cannot_restore_voucher_with_past_or_current_end_date(): void
    {
        $voucher = $this->createSampleVoucher([
            'code' => 'PASTDATETEST',
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDay(),
        ]);
        $voucher->delete();

        // 1. Thử gia hạn ngày trong quá khứ
        $responsePast = $this->actingAs($this->admin)
            ->post(route('admin.vouchers.restore', $voucher->id), [
                'end_date' => now()->subHour()->format('Y-m-d H:i'),
            ]);

        $responsePast->assertSessionHasErrors(['end_date']);
        $this->assertNotNull($voucher->fresh()->deleted_at);

        // 2. Thử gia hạn ngày hiện tại / vừa trôi qua
        $responseCurrent = $this->actingAs($this->admin)
            ->post(route('admin.vouchers.restore', $voucher->id), [
                'end_date' => now()->subSecond()->format('Y-m-d H:i'),
            ]);

        $responseCurrent->assertSessionHasErrors(['end_date']);
    }
}



