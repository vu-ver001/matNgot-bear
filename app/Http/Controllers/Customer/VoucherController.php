<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    /**
     * Display the customer voucher collection / wallet.
     */
    public function index(Request $request): View
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['CUSTOMER', 'STAFF'])) {
            abort(403, 'Trang này chỉ dành cho Khách hàng và Nhân viên tư vấn.');
        }

        $now = now();
        $userId = auth()->id();

        // 1. Base query for all vouchers (excluding soft deleted)
        $query = Voucher::where('status', 'ACTIVE')
            ->with(['categories', 'products', 'productVariants.product'])
            ->orderBy('start_date', 'asc');

        // Optional search filter
        if ($search = trim($request->get('search', ''))) {
            $query->where('code', 'like', "%{$search}%");
        }

        // Optional voucher type filter (ORDER vs SHIPPING)
        $typeFilter = $request->get('type', 'all');
        if (in_array($typeFilter, ['ORDER', 'SHIPPING'])) {
            $query->where('voucher_type', $typeFilter);
        }

        $allVouchers = $query->get();

        // 2. Enhance vouchers with customer-specific and timeline metadata
        $enhanced = $allVouchers->map(function ($voucher) use ($now, $userId) {
            $isUpcoming = $voucher->start_date > $now;
            $isExpired = $voucher->end_date < $now;
            $isDepleted = $voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit;

            $customerUsedCount = $userId ? $voucher->countUsedByCustomer($userId) : 0;
            $limitPerUser = ($voucher->usage_limit_per_user !== null && (int)$voucher->usage_limit_per_user > 0) ? (int) $voucher->usage_limit_per_user : null;
            $customerReachedLimit = ($userId && $limitPerUser !== null) ? ($customerUsedCount >= $limitPerUser) : false;
            $isUsedByCustomer = $customerUsedCount > 0;

            // An active, ready-to-use voucher
            $isAvailable = !$isUpcoming && !$isExpired && !$isDepleted && !$customerReachedLimit;

            // Attached computed properties
            $voucher->is_upcoming = $isUpcoming;
            $voucher->is_expired = $isExpired;
            $voucher->is_depleted = $isDepleted;
            $voucher->customer_used_count = $customerUsedCount;
            $voucher->limit_per_user = $limitPerUser;
            $voucher->customer_reached_limit = $customerReachedLimit;
            $voucher->is_used_by_customer = $isUsedByCustomer;
            $voucher->is_available = $isAvailable;

            return $voucher;
        });

        // 3. Count vouchers in each category
        $counts = [
            'all' => $enhanced->count(),
            'upcoming' => $enhanced->where('is_upcoming', true)->count(),
            'available' => $enhanced->where('is_available', true)->count(),
            'used' => $userId ? $enhanced->where('is_used_by_customer', true)->count() : 0,
        ];

        // 4. Filter by selected Tab
        $currentTab = $request->get('tab', 'all');
        $filteredVouchers = match ($currentTab) {
            'upcoming' => $enhanced->where('is_upcoming', true),
            'available' => $enhanced->where('is_available', true),
            'used' => $enhanced->where('is_used_by_customer', true),
            default => $enhanced,
        };

        // Sort upcoming vouchers by nearest start date first, others by end date
        if ($currentTab === 'upcoming') {
            $filteredVouchers = $filteredVouchers->sortBy('start_date');
        } else {
            $filteredVouchers = $filteredVouchers->sortBy(fn($v) => $v->is_available ? 0 : 1)
                ->sortBy('end_date');
        }

        return view('customer.vouchers.index', [
            'vouchers' => $filteredVouchers,
            'counts' => $counts,
            'currentTab' => $currentTab,
            'currentType' => $typeFilter,
            'search' => $search,
            'now' => $now,
            'isAuthenticated' => auth()->check(),
        ]);
    }

    /**
     * Tra cứu chi tiết điều kiện sử dụng của voucher (dành cho Khách hàng & Nhân viên tư vấn).
     */
    public function conditions(Request $request, string $code): \Illuminate\Http\JsonResponse
    {
        $code = trim($code);
        $voucher = Voucher::where('code', $code)
            ->where('status', 'ACTIVE')
            ->with([
                'categories:id,name',
                'products:id,name,price',
                'productVariants.product:id,name',
            ])
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy mã voucher [{$code}] hoặc voucher đã tạm ngưng.",
            ], 404);
        }

        $now = now();
        $userId = auth()->id();
        $subtotal = (float) $request->input('subtotal', 0);
        $shippingFee = (float) $request->input('shipping_fee', 30000);
        $cartItems = $request->input('cart_items', []);

        $userUsed = $userId ? $voucher->countUsedByCustomer($userId) : 0;
        $userLimit = ($voucher->usage_limit_per_user !== null && (int)$voucher->usage_limit_per_user > 0) ? (int)$voucher->usage_limit_per_user : null;
        $userRemaining = $userLimit !== null ? max(0, $userLimit - $userUsed) : null;

        $globalLimit = (int) ($voucher->usage_limit ?? 0);
        $globalUsed = (int) ($voucher->used_count ?? 0);
        $globalRemaining = $globalLimit > 0 ? max(0, $globalLimit - $globalUsed) : null;

        $isUpcoming = $voucher->start_date > $now;
        $isExpired = $voucher->end_date < $now;
        $isGlobalExhausted = $globalLimit > 0 && $globalUsed >= $globalLimit;
        $isUserExhausted = $userLimit !== null && $userUsed >= $userLimit;

        // Đánh giá điều kiện thực tế với đơn hàng nếu có truyền subtotal hoặc cart_items
        $validation = [
            'valid' => true,
            'message' => 'Mã voucher hợp lệ.',
        ];
        if ($userId) {
            $validation = $voucher->validateForCustomer($userId, $subtotal, $shippingFee, $cartItems);
        }

        $isPercent = in_array($voucher->discount_type, ['PERCENT', 'PERCENTAGE']);
        $discountDisplay = $isPercent
            ? ((int)$voucher->discount_value . '%')
            : (number_format($voucher->discount_value, 0, ',', '.') . 'đ');
        $discountSubtext = ($isPercent && (float)$voucher->max_discount_value > 0)
            ? ('Tối đa ' . number_format($voucher->max_discount_value, 0, ',', '.') . 'đ')
            : ($isPercent ? 'Giảm theo %' : 'Giảm trực tiếp');

        return response()->json([
            'success' => true,
            'voucher' => [
                'id' => $voucher->id,
                'code' => $voucher->code,
                'voucher_type' => $voucher->voucher_type,
                'type_label' => $voucher->voucher_type === 'SHIPPING' ? 'Miễn phí vận chuyển (Freeship)' : 'Giảm giá đơn hàng',
                'discount_type' => $voucher->discount_type,
                'discount_value' => (float)$voucher->discount_value,
                'discount_display' => $discountDisplay,
                'discount_subtext' => $discountSubtext,
                'min_order_value' => (float)$voucher->min_order_value,
                'min_order_formatted' => number_format($voucher->min_order_value ?? 0, 0, ',', '.') . 'đ',
                'max_discount_value' => (float)$voucher->max_discount_value,
                'max_discount_formatted' => ((float)$voucher->max_discount_value > 0) ? (number_format($voucher->max_discount_value, 0, ',', '.') . 'đ') : null,
                'start_date' => $voucher->start_date?->format('H:i d/m/Y'),
                'end_date' => $voucher->end_date?->format('H:i d/m/Y'),
                'is_upcoming' => $isUpcoming,
                'is_expired' => $isExpired,
                'is_global_exhausted' => $isGlobalExhausted,
                'is_user_exhausted' => $isUserExhausted,
                'usage_limit' => $globalLimit > 0 ? $globalLimit : null,
                'used_count' => $globalUsed,
                'remaining_count' => $globalRemaining,
                'limit_per_user' => $userLimit,
                'user_used_count' => $userUsed,
                'user_remaining' => $userRemaining,
                'apply_scope' => $voucher->apply_scope,
                'apply_scope_label' => match($voucher->apply_scope) {
                    'CATEGORY' => 'Danh mục chỉ định',
                    'PRODUCT' => 'Sản phẩm chỉ định',
                    default => 'Toàn bộ sản phẩm'
                },
                'categories' => $voucher->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values(),
                'products' => $voucher->products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => number_format($p->price, 0, ',', '.') . 'đ'])->values(),
                'variants' => $voucher->productVariants->map(fn($v) => [
                    'id' => $v->id,
                    'product_id' => $v->product_id,
                    'product_name' => $v->product?->name,
                    'color' => $v->color,
                    'size' => $v->size,
                    'price' => number_format($v->effective_price ?? ($v->sale_price ?? $v->price), 0, ',', '.') . 'đ',
                ])->values(),
                'copy_url' => route('products.index', ['voucher' => $voucher->code]),
                'is_applicable' => (bool) ($validation['valid'] ?? false),
                'inapplicable_reason' => !($validation['valid'] ?? false) ? ($validation['message'] ?? 'Không đủ điều kiện áp dụng') : null,
                'expected_discount' => (float) ($validation['discount_amount'] ?? 0),
                'expected_discount_formatted' => isset($validation['discount_amount']) ? number_format($validation['discount_amount'], 0, ',', '.') . 'đ' : null,
            ],
        ]);
    }
}
