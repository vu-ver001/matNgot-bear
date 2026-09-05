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
        $now = now();
        $userId = auth()->id();

        // 1. Base query for all vouchers (excluding soft deleted)
        $query = Voucher::where('status', 'ACTIVE')
            ->with(['categories', 'products'])
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
            $limitPerUser = max(1, (int) ($voucher->usage_limit_per_user ?? 1));
            $customerReachedLimit = $userId ? ($customerUsedCount >= $limitPerUser) : false;
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
}
