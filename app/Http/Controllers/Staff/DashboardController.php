<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingOrders = Order::where('order_status', 'PENDING')->count();
        $processingOrders = Order::where('order_status', 'CONFIRMED')
            ->orWhere('order_status', 'PREPARING')
            ->count();
        $shippingOrders = Order::where('order_status', 'SHIPPING')->count();
        $completedToday = Order::where('order_status', 'COMPLETED')
            ->whereDate('completed_at', Carbon::today())
            ->count();

        $revenueToday = Order::where('order_status', 'COMPLETED')
            ->where('payment_status', 'PAID')
            ->whereDate('completed_at', Carbon::today())
            ->sum('total_amount');

        $recentOrders = Order::with('customer')
            ->latest()
            ->limit(10)
            ->get();

        return view('staff.dashboard.index', compact(
            'pendingOrders',
            'processingOrders',
            'shippingOrders',
            'completedToday',
            'revenueToday',
            'recentOrders',
        ));
    }
}
