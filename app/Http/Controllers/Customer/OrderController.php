<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
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
                    ->orWhere('recipient_phone', 'like', "%{$search}%")
                    ->orWhereHas('details', function ($dq) use ($search) {
                        $dq->where('product_name', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->with([
            'details.product.images',
            'voucher',
            'shippingVoucher',
            'reviews',
            'payments',
        ])->latest()->paginate(10);

        if ($request->wantsJson() || $request->query('format') === 'json') {
            return response()->json([
                'success' => true,
                'data' => $orders->getCollection()->map(fn ($order) => $order->toCustomerCardData()),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                ],
            ]);
        }

        return view('customer.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        if (auth()->check()) {
            if (auth()->user()->role === 'ADMIN') {
                return redirect()->route('admin.orders.show', $order);
            }
            if (auth()->user()->role === 'STAFF') {
                return redirect()->route('staff.orders.show', $order);
            }
        }

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

    /**
     * Khách hàng gửi yêu cầu hủy đơn hàng kèm lý do và thông tin hoàn tiền (nếu đã thanh toán)
     */
    public function requestCancel(Request $request, Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if ($order->order_status !== 'PENDING' && ! $order->canRequestCancel()) {
            return redirect()->back()->with('error', 'Bạn chỉ có thể hủy đơn hàng đang chờ xác nhận.');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'refund_bank_name' => 'nullable|string|max:100',
            'refund_bank_account' => 'nullable|string|max:50',
            'refund_account_holder' => 'nullable|string|max:100',
        ]);

        $reason = !empty($validated['reason']) ? $validated['reason'] : 'Khách hàng hủy đơn hàng';

        try {
            // Trường hợp 1: Đơn ở trạng thái Chờ xác nhận (PENDING) -> HỦY TRỰC TIẾP KHÔNG CẦN NHÂN VIÊN DUYỆT
            // Kể cả đơn đã thanh toán online hay chưa thanh toán, trạng thái nhảy ngay sang ĐÃ HỦY
            if ($order->canCancelDirectly()) {
                $this->orderService->cancelOrder($order, auth()->id(), $reason, [
                    'refund_bank_name' => $validated['refund_bank_name'] ?? null,
                    'refund_bank_account' => $validated['refund_bank_account'] ?? null,
                    'refund_account_holder' => $validated['refund_account_holder'] ?? null,
                ]);

                $msg = 'Đơn hàng của bạn đã được hủy thành công.';
                if ($order->payment_status === 'PAID') {
                    $msg .= ' Do đơn hàng đã được thanh toán online, Mật Ngọt Bear sẽ sớm liên hệ qua số điện thoại để hoàn tiền lại cho bạn.';
                }

                return redirect()->back()->with('success', $msg);
            }

            // Trường hợp 2: Đơn đã được nhân viên xác nhận (CONFIRMED) nhưng chưa đóng gói -> CẦN NHÂN VIÊN XÁC NHẬN HỦY
            if ($order->canRequestCancel()) {
                $this->orderService->requestCancelOrder($order, $validated, auth()->id());

                $msg = 'Yêu cầu hủy đơn hàng đã được gửi thành công và đang chờ nhân viên xác nhận.';
                if ($order->payment_status === 'PAID') {
                    $msg .= ' Do đơn hàng đã thanh toán, sau khi nhân viên duyệt hủy sẽ liên hệ với bạn để hoàn tiền.';
                }

                return redirect()->back()->with('success', $msg);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Khách hàng rút lại yêu cầu hủy đơn
     */
    public function withdrawCancel(Request $request, Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->orderService->withdrawCancelRequest($order, auth()->id());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Bạn đã rút lại yêu cầu hủy đơn hàng thành công.');
    }

    // Alias for backward compatibility
    public function cancel(Request $request, Order $order)
    {
        return $this->requestCancel($request, $order);
    }

    /**
     * Customer confirms they have received the package and completed the order.
     * Completes a shipped order and confirms pending COD payments.
     */
    public function complete(Request $request, Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if ($order->order_status !== 'SHIPPING') {
            return back()->with('error', 'Đơn hàng chưa ở trạng thái đang giao hàng để xác nhận hoàn tất.');
        }

        try {
            $this->orderService->updateStatus(
                $order, 'COMPLETED', auth()->id(),
                'Khách hàng đã nhận hàng và xác nhận hoàn tất đơn hàng.'
            );

            return redirect()->route('customer.orders.review', $order->id)
                ->with('success', '🎉 Bạn đã xác nhận đã nhận hàng thành công! Hãy gửi đánh giá để chia sẻ trải nghiệm về sản phẩm nhé.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Customer reorders products from a previous order into their cart.
     */
    public function reorder(Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        if (! $order->canBeReordered()) {
            return back()->with('error', 'Bạn chỉ có thể mua lại từ đơn hàng đã hoàn thành, đã hủy hoặc đã trả hàng.');
        }

        $order->loadMissing('details.product');
        $addedCount = 0;

        foreach ($order->details as $detail) {
            $product = $detail->product;
            if ($product && $product->status === Product::STATUS_ACTIVE && $product->stock_quantity > 0) {
                $cartItem = CartItem::firstOrNew([
                    'user_id' => auth()->id(),
                    'product_id' => $product->id,
                ]);

                $newQty = ($cartItem->exists ? $cartItem->quantity : 0) + $detail->quantity;
                $cartItem->quantity = min($newQty, $product->stock_quantity);
                $cartItem->save();
                $addedCount++;
            }
        }

        if ($addedCount > 0) {
            return redirect()->route('customer.cart')->with('success', "Đã thêm các sản phẩm từ đơn hàng #{$order->order_code} vào giỏ hàng của bạn!");
        }

        return back()->with('error', 'Rất tiếc, các sản phẩm trong đơn hàng này hiện đã hết hàng hoặc không còn kinh doanh.');
    }
}
