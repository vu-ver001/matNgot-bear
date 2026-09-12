<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminDashboardLayout extends Component
{
    public function __construct(
        public string $title = 'Quản Trị Hệ Thống',
        public bool $flush = false,
    ) {}

    public function render(): View
    {
        return view('layouts.admin-dashboard');
    }
}
