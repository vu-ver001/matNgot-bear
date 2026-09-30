<?php

namespace Tests\Unit;

use App\Models\OrderStatusHistory;
use Tests\TestCase;

class OrderStatusHistoryTest extends TestCase
{
    public function test_status_label_returns_vietnamese_text(): void
    {
        $this->assertSame('Chờ xác nhận', OrderStatusHistory::statusLabel('PENDING'));
        $this->assertSame('Đã xác nhận', OrderStatusHistory::statusLabel('CONFIRMED'));
        $this->assertSame('Đang chuẩn bị hàng', OrderStatusHistory::statusLabel('PREPARING'));
        $this->assertSame('Đang giao hàng', OrderStatusHistory::statusLabel('SHIPPING'));
        $this->assertSame('Đã giao thành công', OrderStatusHistory::statusLabel('COMPLETED'));
        $this->assertSame('Đã hủy đơn', OrderStatusHistory::statusLabel('CANCELLED'));
        $this->assertSame('Đã trả hàng / hoàn tiền', OrderStatusHistory::statusLabel('RETURNED'));
    }

    public function test_display_title_for_creation_and_transitions(): void
    {
        // 1. Đơn hàng được tạo
        $h1 = new OrderStatusHistory([
            'from_status' => null,
            'to_status' => 'PENDING',
            'note' => 'Đơn hàng được tạo',
        ]);
        $this->assertSame('Đơn hàng được tạo', $h1->display_title);

        // 2. PENDING -> CONFIRMED
        $h2 = new OrderStatusHistory([
            'from_status' => 'PENDING',
            'to_status' => 'CONFIRMED',
        ]);
        $this->assertSame('Chờ xác nhận → Đã xác nhận', $h2->display_title);

        // 3. CONFIRMED -> PREPARING
        $h3 = new OrderStatusHistory([
            'from_status' => 'CONFIRMED',
            'to_status' => 'PREPARING',
        ]);
        $this->assertSame('Đã xác nhận → Đang chuẩn bị hàng', $h3->display_title);

        // 4. PREPARING -> PREPARING (Khách yêu cầu hủy)
        $h4 = new OrderStatusHistory([
            'from_status' => 'PREPARING',
            'to_status' => 'PREPARING',
            'note' => 'Khách hàng gửi yêu cầu hủy đơn hàng (Đang chuẩn bị hàng): Đổi ý, không còn nhu cầu mua nữa',
        ]);
        $this->assertSame('Khách gửi yêu cầu hủy đơn', $h4->display_title);

        // 5. PREPARING -> PREPARING (Từ chối yêu cầu hủy)
        $h5 = new OrderStatusHistory([
            'from_status' => 'PREPARING',
            'to_status' => 'PREPARING',
            'note' => 'Từ chối yêu cầu hủy đơn. Lý do: Đơn hàng vừa được giao cho đv vận chuyển',
        ]);
        $this->assertSame('Shop đã từ chối yêu cầu hủy đơn', $h5->display_title);

        // 6. PREPARING -> SHIPPING
        $h6 = new OrderStatusHistory([
            'from_status' => 'PREPARING',
            'to_status' => 'SHIPPING',
            'note' => 'Shop bắt đầu giao hàng thủ công.',
        ]);
        $this->assertSame('Đang chuẩn bị hàng → Đang giao hàng', $h6->display_title);

        // 7. SHIPPING -> COMPLETED
        $h7 = new OrderStatusHistory([
            'from_status' => 'SHIPPING',
            'to_status' => 'COMPLETED',
        ]);
        $this->assertSame('Đang giao hàng → Đã giao thành công', $h7->display_title);

        // 8. Duyệt hủy đơn
        $h8 = new OrderStatusHistory([
            'from_status' => 'PREPARING',
            'to_status' => 'CANCELLED',
            'note' => 'Shop đã duyệt yêu cầu hủy đơn của khách hàng',
        ]);
        $this->assertSame('Shop duyệt hủy đơn hàng', $h8->display_title);

        // 9. Xác nhận thanh toán
        $h9 = new OrderStatusHistory([
            'from_status' => 'PENDING',
            'to_status' => 'CONFIRMED',
            'note' => 'Xác nhận thanh toán thành công qua chuyển khoản',
        ]);
        $this->assertSame('Xác nhận thanh toán (+Bill)', $h9->display_title);

        // 10. Tự động hoàn thành
        $h10 = new OrderStatusHistory([
            'from_status' => 'SHIPPING',
            'to_status' => 'COMPLETED',
            'note' => 'Hệ thống tự động chuyển trạng thái hoàn thành đơn hàng sau 7 ngày',
        ]);
        $this->assertSame('Tự động hoàn thành đơn (sau 7 ngày)', $h10->display_title);
    }
}
