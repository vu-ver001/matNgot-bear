<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
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

        if (auth()->user()->role !== 'CUSTOMER') {
            $roleLabel = auth()->user()->role === 'ADMIN' ? 'Quản trị viên (Admin)' : 'Nhân viên (Staff)';
            return redirect()->route('home')->with('error', "Tài khoản {$roleLabel} không thể thực hiện đặt hàng. Vui lòng chuyển sang tài khoản Khách hàng để mua sắm.");
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
        $savedRecipientAddress = $this->cleanAddress($latestOrder->recipient_address ?? $user->address ?? '');

        // Intelligently parse province, ward, and street from saved user profile or previous order address
        $savedProvince = $user->province ?: 'Hà Nội';
        $savedWard = $user->ward ?: '';
        $savedStreet = $user->address_detail ?: '';

        if (empty($savedWard) && !empty($savedRecipientAddress)) {
            $parts = array_map('trim', explode(',', $savedRecipientAddress));
            if (count($parts) >= 3) {
                // e.g. "Thôn Đại Đồng, Xã Thiên Lộc, Hà Nội"
                $savedProvince = end($parts);
                $savedWard = $parts[count($parts) - 2];
                $savedStreet = implode(', ', array_slice($parts, 0, count($parts) - 2));
            } elseif (count($parts) === 2) {
                // e.g. "Thôn Đại Đồng, Hà Nội"
                $savedProvince = end($parts);
                $savedStreet = $parts[0];
            }
        }

        if (empty($savedStreet) && !empty($savedRecipientAddress)) {
            $savedStreet = $savedRecipientAddress;
        }

        // Normalise ward string if it contains extra notes
        if (str_contains($savedWard, '–') || str_contains($savedWard, '-')) {
            $subWards = preg_split('/[–\-]/u', $savedWard);
            if (!empty($subWards[0])) {
                $savedWard = trim($subWards[0]);
            }
        }

        // Clean street address so it only keeps house/street/village, stripping ward and province
        if (!empty($savedStreet)) {
            if (!empty($savedProvince)) {
                $savedStreet = preg_replace('/,?\s*' . preg_quote($savedProvince, '/') . '$/iu', '', $savedStreet);
            }
            if (!empty($savedWard)) {
                $savedStreet = preg_replace('/,?\s*' . preg_quote($savedWard, '/') . '$/iu', '', $savedStreet);
            }
            // Strip any remaining trailing comma-separated parts that match ward/province keywords
            $parts = array_map('trim', explode(',', $savedStreet));
            if (count($parts) >= 2) {
                $last = end($parts);
                if (mb_stripos($last, 'Hà Nội') !== false || mb_stripos($last, 'Hồ Chí Minh') !== false || (!empty($savedProvince) && mb_stripos($last, $savedProvince) !== false)) {
                    array_pop($parts);
                }
                if (!empty($parts)) {
                    $secondLast = end($parts);
                    if (mb_stripos($secondLast, 'Phường') !== false || mb_stripos($secondLast, 'Xã') !== false || mb_stripos($secondLast, 'Thị trấn') !== false || (!empty($savedWard) && mb_stripos($secondLast, $savedWard) !== false)) {
                        array_pop($parts);
                    }
                }
                $savedStreet = implode(', ', $parts);
            }
            $savedStreet = trim($savedStreet ?? '', ", \t\n\r\0\x0B");
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

        // Count how many times each voucher has been used by this customer
        $voucherUsageCounts = $usedOrders
            ->flatMap(fn($order) => array_filter([$order->voucher_id, $order->shipping_voucher_id]))
            ->countBy();

        // Only block vouchers where customer has reached their individual limit
        $blockedVoucherIds = [];
        if ($voucherUsageCounts->isNotEmpty()) {
            $relevantVouchers = Voucher::withTrashed()->whereIn('id', $voucherUsageCounts->keys())->get(['id', 'usage_limit_per_user', 'code']);
            foreach ($relevantVouchers as $v) {
                $limit = max(1, (int) ($v->usage_limit_per_user ?? 1));
                if (($voucherUsageCounts[$v->id] ?? 0) >= $limit) {
                    $blockedVoucherIds[] = $v->id;
                }
            }
        }

        $usedVoucherCodes = Voucher::withTrashed()->whereIn('id', $blockedVoucherIds)->pluck('code')->toArray();

        // 2. Fetch all active vouchers and enrich with remaining usage counts (both shop-wide and per-customer)
        $voucherFields = [
            'id', 'code', 'voucher_type', 'discount_type', 'discount_value',
            'min_order_value', 'max_discount_value', 'start_date', 'end_date',
            'usage_limit', 'usage_limit_per_user', 'used_count', 'status'
        ];

        $now = now();
        $rawVouchers = Voucher::where('status', 'ACTIVE')
            ->where('end_date', '>=', $now)
            ->select($voucherFields)
            ->orderBy('id', 'desc')
            ->get();

        $allVouchers = $rawVouchers->map(function ($v) use ($voucherUsageCounts) {
            $userUsed = (int) ($voucherUsageCounts[$v->id] ?? 0);
            $userLimit = max(1, (int) ($v->usage_limit_per_user ?? 1));
            $userRemaining = max(0, $userLimit - $userUsed);

            $globalLimit = (int) ($v->usage_limit ?? 0);
            $globalUsed = (int) ($v->used_count ?? 0);
            $globalRemaining = $globalLimit > 0 ? max(0, $globalLimit - $globalUsed) : null;

            $isUserExhausted = $userUsed >= $userLimit;
            $isGlobalExhausted = $globalLimit > 0 && $globalUsed >= $globalLimit;
            $isExhausted = $isUserExhausted || $isGlobalExhausted;

            $v->user_used_count = $userUsed;
            $v->user_limit = $userLimit;
            $v->user_remaining = $userRemaining;
            $v->global_limit = $globalLimit;
            $v->global_remaining = $globalRemaining;
            $v->is_exhausted = $isExhausted;
            $v->is_user_exhausted = $isUserExhausted;
            $v->is_global_exhausted = $isGlobalExhausted;

            return $v;
        });

        $orderVouchers = $allVouchers->where('voucher_type', 'ORDER')->values();
        $shippingVouchers = $allVouchers->where('voucher_type', 'SHIPPING')->values();

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

        // Check customer usage limit and global usage limit for applied vouchers
        $usedOrders = \App\Models\Order::where('customer_id', $userId)
            ->where(function ($q) {
                $q->whereNull('order_status')
                  ->orWhere('order_status', '!=', 'CANCELLED');
            })
            ->get(['voucher_id', 'shipping_voucher_id']);

        $userUsageCounts = $usedOrders
            ->flatMap(fn($order) => array_filter([$order->voucher_id, $order->shipping_voucher_id]))
            ->countBy();

        foreach (['voucher_id' => 'Mã giảm giá', 'shipping_voucher_id' => 'Voucher vận chuyển'] as $field => $label) {
            if (!empty($validated[$field])) {
                $voucher = Voucher::find($validated[$field]);
                if ($voucher) {
                    $globalLimit = (int) ($voucher->usage_limit ?? 0);
                    if ($globalLimit > 0 && ($voucher->used_count ?? 0) >= $globalLimit) {
                        return back()->withInput()->with('error', "{$label} [{$voucher->code}] đã hết lượt sử dụng trên toàn hệ thống.");
                    }

                    $perUserLimit = max(1, (int) ($voucher->usage_limit_per_user ?? 1));
                    $userUsed = (int) ($userUsageCounts[$voucher->id] ?? 0);
                    if ($userUsed >= $perUserLimit) {
                        return back()->withInput()->with('error', "Bạn đã sử dụng hết {$perUserLimit} lượt cho phép đối với {$label} [{$voucher->code}].");
                    }
                }
            }
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
            $cleanedAddress = $this->cleanAddress($validated['recipient_address']);

            $data = array_merge($validated, [
                'customer_id' => $userId,
                'recipient_address' => $cleanedAddress,
                'payment_method' => $dbPaymentMethod,
            ]);

            $order = $this->orderService->createOrder($data, $cartItems->all());

            // Remember and sync recipient information into Customer profile for future checkouts
            $currentUser = auth()->user();
            if ($currentUser) {
                $rawDetail = $request->input('address_detail') ?: $currentUser->address_detail;
                $p = $request->input('province') ?: $currentUser->province;
                $w = $request->input('ward') ?: $currentUser->ward;
                if ($rawDetail) {
                    if ($p) {
                        $rawDetail = preg_replace('/,?\s*' . preg_quote($p, '/') . '$/iu', '', $rawDetail);
                    }
                    if ($w) {
                        $rawDetail = preg_replace('/,?\s*' . preg_quote($w, '/') . '$/iu', '', $rawDetail);
                    }
                    $rawDetail = trim($rawDetail, ", \t\n\r\0\x0B");
                }

                $currentUser->update([
                    'full_name' => $validated['recipient_name'] ?: $currentUser->full_name,
                    'phone' => $validated['recipient_phone'] ?: $currentUser->phone,
                    'address' => $cleanedAddress ?: $currentUser->address,
                    'province' => $p,
                    'ward' => $w,
                    'address_detail' => $rawDetail,
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

            // If MoMo, redirect directly to official MoMo Gateway
            if ($rawMethod === 'MOMO') {
                return redirect()->route('customer.payment.momo.redirect', $order->id);
            }

            // If Bank Transfer, redirect to interactive payment gateway page
            if (in_array($rawMethod, ['BANK_TRANSFER', 'E_WALLET'])) {
                return redirect()->route('customer.payment.qr', $order->id)
                    ->with('info', 'Đơn hàng ' . $order->order_code . ' đã tạo! Vui lòng hoàn tất thanh toán.');
            }

            return redirect()->route('customer.checkout.success', $order->id)
                ->with('success', 'Đặt hàng thành công! Mã đơn hàng của bạn là: ' . $order->order_code);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Lỗi đặt hàng: ' . $e->getMessage());
        }
    }

    /**
     * Display order success / thank you page with clear navigation back to home, cart, and orders.
     */
    public function success(Order $order): View|\Illuminate\Http\RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if ($order->customer_id !== auth()->id() && auth()->user()->role !== 'ADMIN' && auth()->user()->role !== 'STAFF') {
            abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }

        $order->load(['details.product.images', 'details.product.category', 'voucher', 'shippingVoucher', 'payments']);

        return view('customer.checkout.success', compact('order'));
    }

    /**
     * Clean and remove duplicated segments (e.g. repeated Ward, Province) from address.
     */
    public function cleanAddress(?string $address): string
    {
        if (empty($address)) {
            return '';
        }

        $parts = array_filter(array_map('trim', explode(',', $address)));
        $unique = [];

        foreach ($parts as $part) {
            $norm = mb_strtolower(preg_replace('/\s+/', ' ', $part));
            $exists = false;
            foreach ($unique as $u) {
                if (mb_strtolower(preg_replace('/\s+/', ' ', $u)) === $norm) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $unique[] = $part;
            }
        }

        return implode(', ', $unique);
    }
}
