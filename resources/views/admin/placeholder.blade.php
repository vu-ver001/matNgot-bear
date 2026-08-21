{{--
    TRANG PLACEHOLDER CHO CÁC MỤC CỦA BẠN NHÓM (ADMIN)
    =====================================================
    Trang này hiển thị khi click vào các mục quản lý chưa có giao diện.
    Khi bạn nhóm hoàn thành giao diện, chỉ cần thay route trong admin.php.

    Xem phần "HƯỚNG DẪN THAY LINK" bên dưới.
--}}

@extends('layouts.admin-dashboard')

@section('page-title')
    @switch($currentPage)
        @case('dashboard') Dashboard & Thống kê @break
        @case('vouchers') Quản lý Voucher @break
        @case('orders') Quản lý Đơn hàng @break
        @case('payments') Quản lý Thanh toán @break
        @case('customers') Quản lý Customer @break
        @case('staff') Quản lý Staff @break
        @case('reviews') Quản lý Review @break
        @case('support') Hỗ trợ Khách hàng @break
        @default {{ ucfirst($currentPage) }}
    @endswitch
@endsection

@section('content')

@php
    $pageConfig = [
        'dashboard' => ['icon' => 'fa-chart-pie', 'title' => 'Dashboard & Thống kê', 'teammate' => 'Bạn phụ trách Dashboard'],
        'vouchers'  => ['icon' => 'fa-ticket', 'title' => 'Quản lý Voucher', 'teammate' => 'Bạn phụ trách Voucher & Khuyến mãi'],
        'orders'    => ['icon' => 'fa-cart-shopping', 'title' => 'Quản lý Đơn hàng', 'teammate' => 'Bạn phụ trách Đơn hàng'],
        'payments'  => ['icon' => 'fa-credit-card', 'title' => 'Quản lý Thanh toán', 'teammate' => 'Bạn phụ trách Thanh toán'],
        'customers' => ['icon' => 'fa-users', 'title' => 'Quản lý Customer', 'teammate' => 'Bạn phụ trách Users/Customer'],
        'staff'     => ['icon' => 'fa-user-tie', 'title' => 'Quản lý Staff', 'teammate' => 'Bạn phụ trách Users/Staff'],
        'reviews'   => ['icon' => 'fa-star-half-stroke', 'title' => 'Quản lý Review', 'teammate' => 'Bạn phụ trách Review'],
        'support'   => ['icon' => 'fa-headset', 'title' => 'Hỗ trợ Khách hàng', 'teammate' => 'Bạn phụ trách Chat/Support'],
    ];
    $config = $pageConfig[$currentPage] ?? ['icon' => 'fa-puzzle-piece', 'title' => ucfirst($currentPage), 'teammate' => 'Bạn nhóm'];
@endphp

<div class="placeholder-page">
    <div class="placeholder-icon">
        <i class="fa-solid {{ $config['icon'] }}"></i>
    </div>
    <h2 class="placeholder-title">{{ $config['title'] }}</h2>
    <p class="placeholder-desc">
        Trang này đang chờ <strong>{{ $config['teammate'] }}</strong> hoàn thiện giao diện.<br>
        Khi bạn nhóm hoàn thành, hãy thay link trong file <code>routes/admin.php</code> để kết nối.
    </p>

    <div class="placeholder-tip">
        <strong>📋 Hướng dẫn thay link cho bạn nhóm:</strong><br><br>

        <strong>Bước 1:</strong> Mở file <code>routes/admin.php</code><br><br>

        <strong>Bước 2:</strong> Tìm dòng route của mục "<strong>{{ $currentPage }}</strong>":<br>
        <code>Route::get('/{{ $currentPage }}', ...)->name('page');</code><br><br>

        <strong>Bước 3:</strong> Thay bằng route trỏ đến Controller hoặc View mới:<br>
        <code>Route::get('/{{ $currentPage }}', [YourController::class, 'index'])->name('{{ $currentPage }}.index');</code><br><br>

        <strong>Bước 4:</strong> Cập nhật link trong sidebar (<code>layouts/admin-dashboard.blade.php</code>):<br>
        Đổi <code>route('admin.page', '{{ $currentPage }}')</code> thành <code>route('admin.{{ $currentPage }}.index')</code>
    </div>
</div>

@endsection
