<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\Voucher;
use App\Services\OrderService;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ShippingService $shippingService
    ) {
    }

    /**
     * Display checkout page with selected cart items.
     */
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        if (!auth()->check()) {
            session()->put('url.intended', $request->fullUrl());
            return redirect()->route('login')->with('info', 'Vui lòng đăng nhập hoặc đăng ký tài khoản để tiến hành thanh toán đơn hàng.');
        }

        $userId = auth()->id();
        $user = auth()->user();

        // Support direct Buy Now via ?product_id=X&quantity=Y
        if ($request->filled('product_id')) {
            $productId = (int) $request->input('product_id');
            $quantity = max(1, (int) $request->input('quantity', 1));

            $cartItem = CartItem::updateOrCreate(
                ['user_id' => $userId, 'product_id' => $productId],
                ['quantity' => $quantity]
            );
            $cartItem->touch();
            $selectedItemIds = [$cartItem->id];
        } else {
            $rawSelected = $request->input('selected_items', []);
            $selectedItemIds = is_array($rawSelected) ? $rawSelected : [$rawSelected];
        }

        // If no selected items passed, fallback to all user's cart items
        if (empty($selectedItemIds) || empty(array_filter($selectedItemIds))) {
            $allCartIds = CartItem::where('user_id', $userId)->pluck('id')->toArray();
            if (empty($allCartIds)) {
                return redirect()->route('customer.cart.index')->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.');
            }
            $selectedItemIds = $allCartIds;
        }

        $cartItems = CartItem::where('user_id', $userId)
            ->whereIn('id', $selectedItemIds)
            ->with(['product.images', 'product.category'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart.index')->with('error', 'Sản phẩm đã chọn không hợp lệ hoặc đã bị xóa.');
        }

        // Calculate subtotal
        $subtotal = $cartItems->sum(function ($item) {
            $price = $item->product->sale_price ?? $item->product->price;
            return $price * $item->quantity;
        });

        // Fetch previous order (if any) or user profile to remember customer shipping details
        $latestOrder = \App\Models\Order::where('customer_id', $userId)->latest()->first();

        $savedRecipientName = $latestOrder->recipient_name ?? $user->full_name ?? '';
        $savedRecipientPhone = $latestOrder->recipient_phone ?? $user->phone ?? '';
        $savedRecipientEmail = $latestOrder->recipient_email ?? $user->email ?? '';
        $savedRecipientAddress = $latestOrder->recipient_address ?? $user->address ?? '';

        // Intelligently parse province, ward, and street from saved address
        $savedProvince = 'Hà Nội';
        $savedWard = '';
        $savedStreet = $savedRecipientAddress;

        if (!empty($savedRecipientAddress)) {
            $parts = array_map('trim', explode(',', $savedRecipientAddress));
            if (count($parts) >= 3) {
                // e.g. "Số 41A Phú Diễn, Phường Cầu Giấy, Hà Nội"
                $savedProvince = end($parts);
                $savedWard = $parts[count($parts) - 2];
                $savedStreet = implode(', ', array_slice($parts, 0, count($parts) - 2));
            } elseif (count($parts) === 2) {
                // e.g. "Số 41A Phú Diễn, Hà Nội"
                $savedProvince = end($parts);
                $savedStreet = $parts[0];
            }
        }

        $savedProfile = [
            'recipient_name' => $savedRecipientName,
            'recipient_phone' => $savedRecipientPhone,
            'recipient_email' => $savedRecipientEmail,
            'province' => $savedProvince,
            'ward' => $savedWard,
            'street' => $savedStreet,
            'full_address' => $savedRecipientAddress,
        ];

        // Calculate initial shipping options based on remembered user address
        $initialShipping = $this->shippingService->calculateShippingOptions(
            $savedProvince ?: 'Hà Nội',
            '',
            $savedWard ?: '',
            $savedStreet ?: '',
            $subtotal
        );

        $shippingFee = $initialShipping['options']['standard']['fee'] ?? 22000;

        // 1. Identify voucher IDs already used by this customer (excluding cancelled orders)
        $usedOrders = \App\Models\Order::where('customer_id', $userId)
            ->where(function ($q) {
                $q->whereNull('order_status')
                  ->orWhere('order_status', '!=', 'CANCELLED');
            })
            ->get(['voucher_id', 'shipping_voucher_id']);

        $usedVoucherIds = $usedOrders
            ->flatMap(fn($order) => array_filter([$order->voucher_id, $order->shipping_voucher_id]))
            ->unique()
            ->values()
            ->toArray();

        $usedVoucherCodes = Voucher::withTrashed()->whereIn('id', $usedVoucherIds)->pluck('code')->toArray();

        // 2. Fetch active vouchers that this user has NOT used and haven't exceeded usage limits
        $voucherFields = [
            'id', 'code', 'voucher_type', 'discount_type', 'discount_value',
            'min_order_value', 'max_discount_value', 'start_date', 'end_date',
            'usage_limit', 'used_count', 'status'
        ];

        $now = now();
        $voucherBaseQuery = Voucher::where('status', 'ACTIVE')
            ->where('end_date', '>=', $now)
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                  ->orWhere('usage_limit', '<=', 0)
                  ->orWhereRaw('used_count < usage_limit');
            });

        if (!empty($usedVoucherIds)) {
            $voucherBaseQuery->whereNotIn('id', $usedVoucherIds);
        }

        $orderVouchers = (clone $voucherBaseQuery)->where('voucher_type', 'ORDER')->select($voucherFields)->get();
        $shippingVouchers = (clone $voucherBaseQuery)->where('voucher_type', 'SHIPPING')->select($voucherFields)->get();
        $allVouchers = (clone $voucherBaseQuery)->select($voucherFields)->get();

        $googleMapsApiKey = config('services.google_maps.api_key', '');

        return view('customer.checkout.index', compact(
            'cartItems',
            'subtotal',
            'shippingFee',
            'user',
            'savedProfile',
            'selectedItemIds',
            'orderVouchers',
            'shippingVouchers',
            'allVouchers',
            'usedVoucherCodes',
            'initialShipping',
            'googleMapsApiKey'
        ));
    }

    /**
     * API to calculate distance and dynamic shipping options based on address & Google Maps.
     */
    public function calculateShipping(Request $request): JsonResponse
    {
        $province = $request->input('province', 'Hà Nội');
        $district = $request->input('district', '');
        $ward = $request->input('ward', '');
        $address = $request->input('address', '');
        $subtotal = (float) $request->input('subtotal', 0);

        $result = $this->shippingService->calculateShippingOptions($province, $district, $ward, $address, $subtotal);

        return response()->json($result);
    }

    /**
     * Process checkout and create order.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'recipient_email' => 'nullable|email|max:255',
            'recipient_address' => 'required|string|max:500',
            'province' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:COD,BANK_TRANSFER,MOMO,VNPAY,E_WALLET,CARD',
            'shipping_method' => 'nullable|string|in:standard,fast,express',
            'shipping_fee' => 'nullable|numeric|min:0',
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

        $shippingMethod = $validated['shipping_method'] ?? 'standard';
        $province = $request->input('province', '');
        $district = $request->input('district', '');
        $ward = $request->input('ward', '');

        // Validation for Express Shipping: Only allowed in Hanoi Inner City
        if ($shippingMethod === 'express') {
            $isHanoiInner = $this->shippingService->isHanoiInnerCity($province, $district, $ward);
            if (!$isHanoiInner) {
                return back()->withInput()->with('error', 'Phương thức "Giao hàng hoả tốc (2 - 4 giờ)" chỉ áp dụng cho khu vực nội thành Hà Nội. Vui lòng chọn phương thức giao hàng tiêu chuẩn hoặc giao hàng nhanh.');
            }
        }

        if (!auth()->check()) {
            return redirect()->route('login')->with('info', 'Vui lòng đăng nhập hoặc đăng ký tài khoản để hoàn tất đặt hàng.');
        }

        $userId = auth()->id();

        // Check if customer already used any of the applied vouchers
        $usedVoucherIds = \App\Models\Order::where('customer_id', $userId)
            ->where(function ($q) {
                $q->whereNull('order_status')
                  ->orWhere('order_status', '!=', 'CANCELLED');
            })
            ->get(['voucher_id', 'shipping_voucher_id'])
            ->flatMap(fn($order) => array_filter([$order->voucher_id, $order->shipping_voucher_id]))
            ->unique()
            ->values()
            ->toArray();

        if (!empty($validated['voucher_id']) && in_array((int) $validated['voucher_id'], $usedVoucherIds)) {
            return back()->withInput()->with('error', 'Mã giảm giá này bạn đã sử dụng trên đơn hàng trước đó rồi.');
        }
        if (!empty($validated['shipping_voucher_id']) && in_array((int) $validated['shipping_voucher_id'], $usedVoucherIds)) {
            return back()->withInput()->with('error', 'Voucher vận chuyển này bạn đã sử dụng trên đơn hàng trước đó rồi.');
        }

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

            // Remember and sync recipient information into Customer profile for future checkouts
            $currentUser = auth()->user();
            if ($currentUser) {
                $currentUser->update([
                    'full_name' => $validated['recipient_name'] ?: $currentUser->full_name,
                    'phone' => $validated['recipient_phone'] ?: $currentUser->phone,
                    'address' => $validated['recipient_address'] ?: $currentUser->address,
                ]);
            }

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

            // If VNPAY / CARD, redirect directly to official VNPAY Gateway (Visa/Mastercard/ATM/QR)
            if ($rawMethod === 'VNPAY' || $rawMethod === 'CARD') {
                return redirect()->route('customer.payment.vnpay.redirect', $order->id);
            }

            // If MoMo or Bank Transfer, redirect to interactive payment gateway page
            if (in_array($rawMethod, ['MOMO', 'BANK_TRANSFER', 'E_WALLET'])) {
                return redirect()->route('customer.payment.qr', $order->id)
                    ->with('info', 'Đơn hàng ' . $order->order_code . ' đã tạo! Vui lòng hoàn tất thanh toán.');
            }

            return redirect()->route('customer.orders.show', $order->id)
                ->with('success', 'Đặt hàng thành công! Mã đơn hàng của bạn là: ' . $order->order_code);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Lỗi đặt hàng: ' . $e->getMessage());
        }
    }
}
