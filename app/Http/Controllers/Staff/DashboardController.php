<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboard)
    {
        $data = $dashboard->getStaffDashboardData();

        return view('staff.dashboard.index', array_merge(
            $data['kpi'],
            ['recentOrders' => $data['recentOrders']],
        ));
    }
}
