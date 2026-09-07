<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboard)
    {
        $month = $request->filled('month') ? (int) $request->input('month') : null;
        $year = $request->filled('year') ? (int) $request->input('year') : null;

        $data = $dashboard->getAdminDashboardData($month, $year);

        return view('admin.dashboard.index', array_merge(
            $data['kpi'],
            [
                'monthlyRevenue' => $data['monthlyRevenue'],
                'topProducts' => $data['topProducts'],
                'recentOrders' => $data['recentOrders'],
                'selectedMonth' => $data['selectedMonth'],
                'selectedYear' => $data['selectedYear'],
            ]
        ));
    }
}
