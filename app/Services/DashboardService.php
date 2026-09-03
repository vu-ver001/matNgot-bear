<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getAdminDashboardData(?int $month = null, ?int $year = null): array
    {
        $selectedYear = $year ?: Carbon::now()->year;
        $selectedMonth = $month ?: Carbon::now()->month;

        return [
            'kpi' => $this->getAdminKpi($selectedMonth, $selectedYear),
            'monthlyRevenue' => $this->getMonthlyRevenue($selectedYear),
            'topProducts' => $this->getTopProducts(),
            'recentOrders' => $this->getRecentOrders(),
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
        ];
    }

    public function getStaffDashboardData(): array
    {
        return [
            'kpi' => $this->getStaffKpi(),
            'recentOrders' => $this->getRecentOrders(),
        ];
    }

    private function getAdminKpi(int $selectedMonth, int $selectedYear): array
    {
        $completedPaid = fn ($query) => $query
            ->where('order_status', 'COMPLETED')
            ->where('payment_status', 'PAID');

        $totalRevenue = (clone $completedPaid(Order::query()))->sum('total_amount');

        $targetDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
        $monthStart = $targetDate->copy()->startOfMonth();
        $monthEnd = $targetDate->copy()->endOfMonth();

        $currentMonthRevenue = (clone $completedPaid(Order::query()))
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('total_amount');

        $prevTargetDate = $targetDate->copy()->subMonth();
        $prevMonthStart = $prevTargetDate->copy()->startOfMonth();
        $prevMonthEnd = $prevTargetDate->copy()->endOfMonth();

        $previousMonthRevenue = (clone $completedPaid(Order::query()))
            ->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])
            ->sum('total_amount');

        $monthChange = null;
        if ($previousMonthRevenue > 0) {
            $monthChange = round(($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue * 100, 1);
        } elseif ($currentMonthRevenue > 0) {
            $monthChange = 100.0;
        } elseif ($currentMonthRevenue == 0 && $previousMonthRevenue == 0) {
            $monthChange = 0.0;
        }

        return [
            'totalRevenue' => $totalRevenue,
            'currentMonthRevenue' => $currentMonthRevenue,
            'previousMonthRevenue' => $previousMonthRevenue,
            'monthChange' => $monthChange,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'isCurrentMonth' => ($selectedMonth === Carbon::now()->month && $selectedYear === Carbon::now()->year),
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('order_status', 'PENDING')->count(),
            'completedOrders' => Order::where('order_status', 'COMPLETED')->count(),
            'cancelledOrders' => Order::where('order_status', 'CANCELLED')->count(),
            'totalCustomers' => User::where('role', 'CUSTOMER')->count(),
            'totalStaff' => User::where('role', 'STAFF')->count(),
            'totalProducts' => Product::count(),
        ];
    }

    private function getStaffKpi(): array
    {
        $pendingOrders = Order::where('order_status', 'PENDING')->count();
        $processingOrders = Order::whereIn('order_status', ['CONFIRMED', 'PREPARING'])->count();
        $shippingOrders = Order::where('order_status', 'SHIPPING')->count();

        $completedToday = Order::where('order_status', 'COMPLETED')
            ->whereDate('completed_at', Carbon::today())
            ->count();

        $revenueToday = Order::where('order_status', 'COMPLETED')
            ->where('payment_status', 'PAID')
            ->whereDate('completed_at', Carbon::today())
            ->sum('total_amount');

        return compact('pendingOrders', 'processingOrders', 'shippingOrders', 'completedToday', 'revenueToday');
    }

    private function getMonthlyRevenue(int $year): Collection
    {
        $monthlyRows = Order::where('order_status', 'COMPLETED')
            ->where('payment_status', 'PAID')
            ->whereYear('created_at', $year)
            ->selectRaw(DB::getDriverName() === 'sqlite'
                ? "CAST(strftime('%m', created_at) AS INTEGER) as month, SUM(total_amount) as total, COUNT(*) as order_count"
                : 'MONTH(created_at) as month, SUM(total_amount) as total, COUNT(*) as order_count')
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        return collect(range(1, 12))
            ->map(function (int $m) use ($year, $monthlyRows) {
                $row = $monthlyRows->get($m);

                return (object) [
                    'year' => $year,
                    'month' => $m,
                    'total' => (float) ($row->total ?? 0),
                    'order_count' => (int) ($row->order_count ?? 0),
                ];
            });
    }

    private function getTopProducts(): Collection
    {
        return Product::where('status', 'ACTIVE')
            ->orderByDesc('sold_count')
            ->limit(10)
            ->get();
    }

    private function getRecentOrders(): Collection
    {
        return Order::with('customer')
            ->latest()
            ->limit(10)
            ->get();
    }
}
