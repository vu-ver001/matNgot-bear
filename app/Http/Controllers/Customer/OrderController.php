<?php

namespace App\Http\Controllers\Customer;

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
        if (auth()->check() && auth()->user()->role === 'STAFF') {
            return redirect()->route('staff.orders.index');
        }

        if (auth()->check() && auth()->user()->role === 'ADMIN') {
            return redirect()->route('admin.orders.index');
        }

        $query = Order::where('customer_id', auth()->id())->with(['latestPayment', 'details', 'reviews']);

        $counts = (clone $query)->selectRaw('order_status, COUNT(*) as aggregate')
            ->groupBy('order_status')->pluck('aggregate', 'order_status');
        $stats = ['total' => $counts->sum()];
        foreach ($counts as $status => $count) {
            $stats[strtolower($status)] = $count;
        }

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

        $orders = $query->latest()->paginate(10);

        return view('customer.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['details.product.images', 'payments', 'statusHistories', 'voucher', 'reviews']);

        return view('customer.orders.show', compact('order'));
    }

    /**
     * View and print customer e-invoice.
     */
    public function invoice(Order $order)
    {
        if ($order->customer_id !== auth()->id() && !in_array(auth()->user()->role, ['ADMIN', 'STAFF'])) {
            abort(403);
        }

        $order->load(['details.product.images', 'payments', 'voucher', 'customer']);

        return view('customer.orders.invoice', compact('order'));
    }

    /**
     * Update recipient shipping address before staff confirmation (while order is PENDING).
     */
    public function updateShippingAddress(Request $request, Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if ($order->order_status !== 'PENDING') {
            return back()->with('error', 'Đơn hàng đã được nhân viên tiếp nhận xử lý, không thể thay đổi địa chỉ nhận hàng.');
        }

        $validated = $request->validate([
            'recipient_name' => 'required|string|max:100',
            'recipient_phone' => 'required|string|max:20',
            'recipient_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
        ], [
            'recipient_name.required' => 'Vui lòng nhập tên người nhận.',
            'recipient_phone.required' => 'Vui lòng nhập số điện thoại.',
            'recipient_address.required' => 'Vui lòng nhập địa chỉ giao hàng.',
        ]);

        $checkoutController = app(\App\Http\Controllers\Customer\CheckoutController::class);
        $cleanAddress = $checkoutController->cleanAddress($validated['recipient_address']);

        $order->update([
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'recipient_address' => $cleanAddress,
            'note' => $validated['note'] ?? $order->note,
        ]);

        return back()->with('success', 'Đã cập nhật thông tin địa chỉ nhận hàng thành công!');
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if ($order->order_status !== 'PENDING') {
            return redirect()->back()->with('error', 'Bạn chỉ có thể hủy đơn hàng đang chờ xác nhận.');
        }

        try {
            $this->orderService->cancelOrder($order, auth()->id(), 'Khách hàng yêu cầu hủy');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('customer.orders.index')->with('success', 'Hủy đơn hàng thành công.');
    }

    /**
     * Customer confirms they have received the package and completed the order.
     * Marks order as COMPLETED, updates completed_at, payment_status, and increments sold_count!
     */
    public function complete(Request $request, Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if (!in_array($order->order_status, ['SHIPPING', 'PREPARING', 'CONFIRMED'])) {
            return back()->with('error', 'Đơn hàng chưa ở trạng thái đang giao hàng để xác nhận hoàn tất.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                $oldStatus = $order->order_status;
                
                $order->update([
                    'order_status' => 'COMPLETED',
                    'completed_at' => now(),
                    'payment_status' => 'PAID',
                ]);

                // Record status history
                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'COMPLETED',
                    'changed_by' => auth()->id(),
                    'note' => 'Khách hàng đã nhận hàng và xác nhận hoàn tất đơn hàng.',
                    'changed_at' => now(),
                ]);

                // Tăng lượt đã bán (sold_count) cho các sản phẩm trong đơn hàng
                foreach ($order->details as $detail) {
                    $detail->product()->withTrashed()->first()?->increment('sold_count', $detail->quantity);
                }

                // Cập nhật bản ghi thanh toán thành PAID nếu là COD
                if ($order->payment_method === 'COD') {
                    $payment = \App\Models\Payment::firstOrCreate(
                        ['order_id' => $order->id],
                        [
                            'method' => 'COD',
                            'amount' => $order->total_amount,
                            'status' => 'PAID',
                            'transaction_ref' => 'COD_' . $order->order_code,
                            'paid_at' => now(),
                        ]
                    );
                    $payment->update([
                        'status' => 'PAID',
                        'paid_at' => now(),
                    ]);
                }
            });

            return back()->with('success', '🎉 Cảm ơn bạn! Đơn hàng đã được xác nhận hoàn tất thành công. Hãy để lại đánh giá cho bé gấu nhé!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
