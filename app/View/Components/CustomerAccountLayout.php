<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class CustomerAccountLayout extends Component
{
    public function __construct(
        public string $title = 'Tài khoản của tôi',
        public bool $flush = false,
    ) {}

    public function render(): View
    {
        return view('layouts.customer-account');
    }
}
