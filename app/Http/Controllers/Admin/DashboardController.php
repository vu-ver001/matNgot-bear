<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = Order::where('order_status', 'COMPLETED')
            ->where('payment_status', 'PAID')
            ->sum('total_amount');

        $totalOrders = Order::count();
        $pendingOrders = Order::where('order_status', 'PENDING')->count();
        $completedOrders = Order::where('order_status', 'COMPLETED')->count();
        $cancelledOrders = Order::where('order_status', 'CANCELLED')->count();

        $totalCustomers = User::where('role', 'CUSTOMER')->count();
        $totalStaff = User::where('role', 'STAFF')->count();
        $totalProducts = Product::count();

        $recentOrders = Order::with('customer')
            ->latest()
            ->limit(10)
            ->get();

        $start = Carbon::now()->startOfMonth()->subMonths(11);

        $monthlyRows = Order::where('order_status', 'COMPLETED')
            ->where('payment_status', 'PAID')
            ->where('created_at', '>=', $start)
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(total_amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn ($row) => $row->year.'-'.$row->month);

        $monthlyRevenue = collect(range(0, 11))
            ->map(function (int $i) use ($start, $monthlyRows) {
                $month = $start->copy()->addMonths($i);
                $row = $monthlyRows->get($month->format('Y-n'));

                return (object) [
                    'year' => $month->year,
                    'month' => $month->month,
                    'total' => (float) ($row->total ?? 0),
                ];
            });

        $topProducts = Product::where('status', 'ACTIVE')
            ->orderByDesc('sold_count')
            ->limit(10)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalRevenue',
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'cancelledOrders',
            'totalCustomers',
            'totalStaff',
            'totalProducts',
            'recentOrders',
            'monthlyRevenue',
            'topProducts',
        ));
    }
}
