<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'latestPayment']);

        if ($request->filled('order_status')) {
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

        $orders = $query->latest()->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'details.product', 'payments', 'statusHistories.changedByUser', 'voucher']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => 'required|in:PENDING,CONFIRMED,PREPARING,SHIPPING,COMPLETED,CANCELLED',
            'cancel_reason' => 'required_if:order_status,CANCELLED|nullable|string|max:255',
        ]);

        $oldStatus = $order->order_status;
        $newStatus = $validated['order_status'];

        $order->update([
            'order_status' => $newStatus,
            'cancel_reason' => $validated['cancel_reason'] ?? null,
            'cancelled_by' => $newStatus === 'CANCELLED' ? auth()->id() : $order->cancelled_by,
            'cancelled_at' => $newStatus === 'CANCELLED' ? now() : $order->cancelled_at,
            'confirmed_at' => $newStatus === 'CONFIRMED' ? now() : $order->confirmed_at,
            'completed_at' => $newStatus === 'COMPLETED' ? now() : $order->completed_at,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_by' => auth()->id(),
            'note' => $validated['cancel_reason'] ?? null,
            'changed_at' => now(),
        ]);

        if ($newStatus === 'CANCELLED' && !$order->stock_restored) {
            foreach ($order->details as $detail) {
                $detail->product->increment('stock_quantity', $detail->quantity);
            }
            $order->update(['stock_restored' => true]);
        }

        if ($newStatus === 'COMPLETED') {
            foreach ($order->details as $detail) {
                $detail->product->increment('sold_count', $detail->quantity);
            }
        }

        return redirect()->back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }
}
