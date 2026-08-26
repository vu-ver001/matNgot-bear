<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\Voucher;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
    }

    /**
     * Display checkout page with selected cart items.
     */
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $selectedItemIds = $request->input('selected_items', []);

        if (empty($selectedItemIds)) {
            return redirect()->route('customer.cart.index')->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.');
        }

        $userId = auth()->id() ?? User::where('role', 'CUSTOMER')->first()?->id ?? 1;
        $user = auth()->user() ?? User::find($userId);

        $cartItems = CartItem::where('user_id', $userId)
            ->whereIn('id', $selectedItemIds)
            ->with(['product.images'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart.index')->with('error', 'Sản phẩm đã chọn không hợp lệ hoặc đã bị xóa.');
        }

        // Calculate subtotal
        $subtotal = $cartItems->sum(function ($item) {
            $price = $item->product->sale_price ?? $item->product->price;
            return $price * $item->quantity;
        });

        $shippingFee = $subtotal >= 500000 ? 0 : 30000;

        // Fetch active vouchers
        $orderVouchers = Voucher::where('status', 'ACTIVE')
            ->where('voucher_type', 'ORDER')
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->get();

        $shippingVouchers = Voucher::where('status', 'ACTIVE')
            ->where('voucher_type', 'SHIPPING')
            ->where(fn($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
            ->get();

        return view('customer.checkout.index', compact(
            'cartItems',
            'subtotal',
            'shippingFee',
            'user',
            'selectedItemIds',
            'orderVouchers',
            'shippingVouchers'
        ));
    }

    /**
     * Process checkout and create order.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'recipient_address' => 'required|string|max:500',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:COD,BANK_TRANSFER,MOMO,VNPAY,E_WALLET,CARD',
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'required|integer',
            'voucher_id' => 'nullable|exists:vouchers,id',
            'shipping_voucher_id' => 'nullable|exists:vouchers,id',
        ], [
            'recipient_name.required' => 'Vui lòng nhập họ và tên người nhận.',
            'recipient_phone.required' => 'Vui lòng nhập số điện thoại người nhận.',
            'recipient_address.required' => 'Vui lòng nhập địa chỉ giao hàng.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'selected_items.required' => 'Không có sản phẩm nào được chọn.',
        ]);

        $userId = auth()->id() ?? User::where('role', 'CUSTOMER')->first()?->id ?? 1;

        $cartItems = CartItem::where('user_id', $userId)
            ->whereIn('id', $validated['selected_items'])
            ->with(['product'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart.index')->with('error', 'Sản phẩm trong giỏ hàng không tồn tại.');
        }

        // Map selected payment option to database enum
        $rawMethod = $validated['payment_method'];
        $dbPaymentMethod = match ($rawMethod) {
            'MOMO' => 'E_WALLET',
            'VNPAY' => 'CARD',
            'BANK_TRANSFER' => 'BANK_TRANSFER',
            'CARD' => 'CARD',
            'E_WALLET' => 'E_WALLET',
            default => 'COD',
        };

        try {
            $data = array_merge($validated, [
                'customer_id' => $userId,
                'payment_method' => $dbPaymentMethod,
            ]);

            $order = $this->orderService->createOrder($data, $cartItems->all());

            // Create initial payment tracking
            Payment::create([
                'order_id' => $order->id,
                'method' => $dbPaymentMethod,
                'status' => $dbPaymentMethod === 'COD' ? 'PENDING' : 'PENDING',
                'amount' => $order->total_amount,
                'transaction_ref' => 'TXN' . strtoupper(uniqid()),
            ]);

            // Delete purchased items from cart
            CartItem::where('user_id', $userId)
                ->whereIn('id', $validated['selected_items'])
                ->delete();

            // If online payment (MOMO, VNPAY, BANK_TRANSFER), redirect to interactive QR payment gateway
            if (in_array($rawMethod, ['MOMO', 'VNPAY', 'BANK_TRANSFER', 'E_WALLET', 'CARD'])) {
                return redirect()->route('customer.payment.qr', $order->id)
                    ->with('info', 'Đơn hàng ' . $order->order_code . ' đã tạo! Vui lòng quét mã để hoàn tất thanh toán.');
            }

            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', 'Đặt hàng thành công! Mã đơn hàng của bạn là: ' . $order->order_code);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Lỗi đặt hàng: ' . $e->getMessage());
        }
    }
}
