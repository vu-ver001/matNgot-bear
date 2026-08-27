<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboard)
    {
        $data = $dashboard->getAdminDashboardData();

        return view('admin.dashboard.index', array_merge(
            $data['kpi'],
            ['monthlyRevenue' => $data['monthlyRevenue']],
            ['topProducts' => $data['topProducts']],
            ['recentOrders' => $data['recentOrders']],
        ));
    }
}
