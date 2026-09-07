<?php

namespace App\Http\Controllers\Admin;

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
        $query = Order::with(['customer', 'latestPayment']);

        // Filter for cancellation requests or orders needing refund
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

        $pendingCancelRequestsCount = Order::where('cancel_request_status', 'PENDING')->count();
        $needRefundCount = Order::where('order_status', 'CANCELLED')->where('payment_status', 'PAID')->count();
        $orders = $query->latest()->paginate(15);

        return view('admin.orders.index', compact('orders', 'pendingCancelRequestsCount', 'needRefundCount'));
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'details.product', 'payments', 'statusHistories.changedByUser', 'voucher']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'required|in:PENDING,CONFIRMED,PREPARING,SHIPPING,COMPLETED,CANCELLED,RETURNED',
            'cancel_reason' => 'nullable|string|max:255',
        ]);

        try {
            if ($validated['order_status'] === 'CANCELLED' && $order->hasPendingCancelRequest()) {
                $this->orderService->approveCancelOrder($order, auth()->id(), $validated['cancel_reason'] ?? null);
            } else {
                $this->orderService->updateStatus(
                    $order,
                    $validated['order_status'],
                    auth()->id(),
                    $validated['cancel_reason'] ?? null
                );
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }

    /**
     * Nhân viên duyệt hủy đơn hàng
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
     * Nhân viên từ chối hủy đơn hàng
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
     * Admin xác nhận đã hoàn tiền cho đơn hàng đã hủy
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
