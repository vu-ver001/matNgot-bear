{{--
    TRANG PLACEHOLDER CHO CÁC MỤC CỦA STAFF
    =========================================
    Trang này hiển thị khi click vào các mục xử lý của Staff.
    Khi bạn phụ trách phần Staff hoàn thành, chỉ cần thay route trong routes/staff.php.
--}}

@extends('layouts.staff-dashboard')

@section('page-title')
    @switch($currentPage)
        @case('dashboard') Dashboard Vận Hành @break
        @case('orders') Quản Lý Đơn Hàng @break
        @case('order-status') Xử Lý Trạng Thái Đơn @break
        @case('payments') Xử Lý Thanh Toán @break
        @case('support') Hỗ Trợ Khách Hàng @break
        @default {{ ucfirst($currentPage) }}
    @endswitch
@endsection

@section('content')

@php
    $staffPages = [
        'dashboard'    => ['icon' => 'fa-chart-line', 'title' => 'Dashboard Vận Hành', 'desc' => 'Báo cáo tổng quan tiến độ đơn hàng, ca trực và hiệu suất nhân viên.'],
        'orders'       => ['icon' => 'fa-cart-shopping', 'title' => 'Quản Lý Đơn Hàng', 'desc' => 'Tiếp nhận, kiểm tra danh sách đơn đặt hàng từ khách hàng.'],
        'order-status' => ['icon' => 'fa-truck-ramp-box', 'title' => 'Xử Lý Trạng Thái Đơn', 'desc' => 'Cập nhật trạng thái đơn (Đang đóng gói, Đã bàn giao ĐVVC, Giao thành công).'],
        'payments'     => ['icon' => 'fa-receipt', 'title' => 'Xử Lý Thanh Toán', 'desc' => 'Xác thực thanh toán chuyển khoản, kiểm tra mã đối soát ngân hàng.'],
        'support'      => ['icon' => 'fa-comments', 'title' => 'Hỗ Trợ Khách Hàng', 'desc' => 'Tư vấn trực tuyến, tiếp nhận khiếu nại và đổi trả sản phẩm.'],
    ];
    $info = $staffPages[$currentPage] ?? ['icon' => 'fa-gear', 'title' => ucfirst($currentPage), 'desc' => 'Khu vực xử lý nghiệp vụ Staff.'];
@endphp

<div class="placeholder-page">
    <div class="placeholder-icon">
        <i class="fa-solid {{ $info['icon'] }}"></i>
    </div>
    <h2 class="placeholder-title">{{ $info['title'] }}</h2>
    <p class="placeholder-desc">{{ $info['desc'] }}</p>

    <div class="placeholder-tip">
        <strong>📋 Hướng dẫn thay link khi bạn nhóm bàn giao code:</strong><br><br>

        <strong>Bước 1:</strong> Mở file <code>routes/staff.php</code><br><br>

        <strong>Bước 2:</strong> Tìm mục "<strong>{{ $currentPage }}</strong>":<br>
        <code>Route::get('/{{ $currentPage }}', ...)->name('staff.{{ $currentPage }}');</code><br><br>

        <strong>Bước 3:</strong> Trỏ đến Controller thực tế của bạn nhóm:<br>
        <code>Route::get('/{{ $currentPage }}', [StaffController::class, '{{ $currentPage }}'])->name('{{ $currentPage }}');</code><br><br>

        <strong>Bước 4:</strong> Cập nhật sidebar tại <code>resources/views/layouts/staff-dashboard.blade.php</code>.
    </div>
</div>

@endsection
