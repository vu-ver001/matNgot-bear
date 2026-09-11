<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $query = Order::with(['customer', 'latestPayment', 'details.product.images']);

        // Lọc theo tab Yêu cầu hủy hoặc Cần hoàn tiền
        if ($request->query('tab') === 'cancel_requests') {
            $query->where('cancel_request_status', 'PENDING');
        } elseif ($request->query('tab') === 'need_refund') {
            $query->where('order_status', 'CANCELLED')->where('payment_status', 'PAID');
        } elseif ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('recipient_phone', 'like', "%{$search}%");
            });
        }

        $perPage = in_array((int) $request->input('per_page'), [15, 30, 50, 100], true)
            ? (int) $request->input('per_page')
            : 15;

        $orders = $query->latest()->paginate($perPage);

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('order_status', 'PENDING')->count(),
            'confirmed' => Order::where('order_status', 'CONFIRMED')->count(),
            'preparing' => Order::where('order_status', 'PREPARING')->count(),
            'shipping' => Order::where('order_status', 'SHIPPING')->count(),
            'completed' => Order::where('order_status', 'COMPLETED')->count(),
            'returned' => Order::where('order_status', 'RETURNED')->count(),
            'cancelled' => Order::where('order_status', 'CANCELLED')->count(),
        ];

        return view('staff.orders.index', compact('orders', 'stats'));
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'required|integer|exists:orders,id',
            'target_status' => 'nullable|in:CONFIRMED,PREPARING,SHIPPING',
        ]);

        $targetStatus = $validated['target_status'] ?? 'SHIPPING';
        $changedBy = auth()->id();

        try {
            $result = $this->orderService->bulkUpdateStatus(
                $validated['order_ids'],
                $targetStatus,
                $changedBy,
                'Cập nhật trạng thái hàng loạt bởi ' . (auth()->user()->full_name ?? auth()->user()->name ?? 'Nhân viên')
            );

            $statusLabels = [
                'SHIPPING' => 'Đang giao hàng',
                'CONFIRMED' => 'Đã xác nhận',
                'PREPARING' => 'Chờ lấy hàng',
                'COMPLETED' => 'Đã giao thành công',
            ];
            $label = $statusLabels[$targetStatus] ?? $targetStatus;

            if ($result['updated'] > 0) {
                $msg = "Đã chuyển {$result['updated']} đơn hàng sang trạng thái '{$label}' thành công.";
                if ($result['skipped'] > 0) {
                    $msg .= " (Bỏ qua {$result['skipped']} đơn do trạng thái không phù hợp).";
                }
                return redirect()->back()->with('success', $msg);
            }

            return redirect()->back()->with('error', "Không có đơn hàng nào hợp lệ để chuyển sang trạng thái '{$label}'.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi thao tác hàng loạt: ' . $e->getMessage());
        }
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'details.product', 'payments', 'statusHistories.changedByUser', 'voucher']);

        return view('staff.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'required|in:PENDING,CONFIRMED,PREPARING,SHIPPING,COMPLETED,CANCELLED,RETURNED',
            'cancel_reason' => 'required_if:order_status,CANCELLED|nullable|string|max:255',
            'stock_returned' => 'nullable|boolean',
        ]);

        try {
            $this->orderService->updateStatus(
                $order,
                $validated['order_status'],
                auth()->id(),
                $validated['cancel_reason'] ?? match ($validated['order_status']) {
                    'SHIPPING' => 'Shop bắt đầu giao hàng thủ công.',
                    'COMPLETED' => 'Shop xác nhận đã giao hàng thành công.',
                    default => null,
                },
                (bool) ($validated['stock_returned'] ?? false)
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }

    /**
     * Nhân viên duyệt hủy đơn hàng (Không bắt buộc nhập lý do)
     */
    public function approveCancel(Request $request, Order $order)
    {
        $validated = $request->validate([
            'refund_note' => 'nullable|string|max:500',
        ]);

        try {
            $this->orderService->approveCancelOrder($order, auth()->id(), $validated['refund_note'] ?? null);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Đã duyệt yêu cầu hủy đơn hàng thành công!');
    }

    /**
     * Nhân viên từ chối hủy đơn hàng (Bắt buộc nhập lý do từ chối)
     */
    public function rejectCancel(Request $request, Order $order)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ], [
            'rejection_reason.required' => 'Vui lòng nhập lý do từ chối hủy đơn.',
            'rejection_reason.min' => 'Lý do từ chối cần ít nhất 5 ký tự.',
        ]);

        try {
            $this->orderService->rejectCancelOrder($order, auth()->id(), $validated['rejection_reason']);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Đã từ chối yêu cầu hủy đơn hàng.');
    }

    /**
     * Nhân viên xác nhận đã hoàn tiền cho đơn hàng đã hủy
     */
    public function confirmRefund(Request $request, Order $order)
    {
        $validated = $request->validate([
            'refund_note' => 'nullable|string|max:500',
        ]);

        try {
            $this->orderService->confirmRefundOrder($order, auth()->id(), $validated['refund_note'] ?? null);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Đã xác nhận hoàn tiền thành công cho đơn hàng.');
    }
}
