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
    public function getAdminDashboardData(): array
    {
        return [
            'kpi' => $this->getAdminKpi(),
            'monthlyRevenue' => $this->getMonthlyRevenue(),
            'topProducts' => $this->getTopProducts(),
            'recentOrders' => $this->getRecentOrders(),
        ];
    }

    public function getStaffDashboardData(): array
    {
        return [
            'kpi' => $this->getStaffKpi(),
            'recentOrders' => $this->getRecentOrders(),
        ];
    }

    private function getAdminKpi(): array
    {
        $completedPaid = fn ($query) => $query
            ->where('order_status', 'COMPLETED')
            ->where('payment_status', 'PAID');

        $totalRevenue = (clone $completedPaid(Order::query()))->sum('total_amount');

        $currentMonthRevenue = (clone $completedPaid(Order::query()))
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('total_amount');

        $previousMonthRevenue = (clone $completedPaid(Order::query()))
            ->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
            ->sum('total_amount');

        return [
            'totalRevenue' => $totalRevenue,
            'currentMonthRevenue' => $currentMonthRevenue,
            'previousMonthRevenue' => $previousMonthRevenue,
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

    private function getMonthlyRevenue(): Collection
    {
        $start = Carbon::now()->startOfMonth()->subMonths(11);

        $monthlyRows = Order::where('order_status', 'COMPLETED')
            ->where('payment_status', 'PAID')
            ->where('created_at', '>=', $start)
            ->selectRaw(DB::getDriverName() === 'sqlite'
                ? "CAST(strftime('%m', created_at) AS INTEGER) as month, CAST(strftime('%Y', created_at) AS INTEGER) as year, SUM(total_amount) as total"
                : 'MONTH(created_at) as month, YEAR(created_at) as year, SUM(total_amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn ($row) => $row->year.'-'.$row->month);

        return collect(range(0, 11))
            ->map(function (int $i) use ($start, $monthlyRows) {
                $month = $start->copy()->addMonths($i);
                $row = $monthlyRows->get($month->format('Y-n'));

                return (object) [
                    'year' => $month->year,
                    'month' => $month->month,
                    'total' => (float) ($row->total ?? 0),
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
