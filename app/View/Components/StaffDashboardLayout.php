<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class StaffDashboardLayout extends Component
{
    public function __construct(
        public string $title = 'Bảng Xử Lý Nhân Viên',
        public bool $flush = false,
    ) {}

    public function render(): View
    {
        return view('layouts.staff-dashboard');
    }
}
