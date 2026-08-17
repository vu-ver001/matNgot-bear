<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::where('customer_id', auth()->id())->with(['latestPayment', 'details']);

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        $orders = $query->latest()->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['details.product', 'payments', 'statusHistories', 'voucher']);

        return view('customer.orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($order->order_status, ['PENDING'])) {
            return redirect()->back()->with('error', 'Không thể hủy đơn hàng ở trạng thái hiện tại.');
        }

        $order->update([
            'order_status' => 'CANCELLED',
            'cancelled_at' => now(),
        ]);

        if (!$order->stock_restored) {
            foreach ($order->details as $detail) {
                $detail->product->increment('stock_quantity', $detail->quantity);
            }
            $order->update(['stock_restored' => true]);
        }

        return redirect()->route('customer.orders.show', $order)->with('success', 'Đã hủy đơn hàng thành công.');
    }
}
