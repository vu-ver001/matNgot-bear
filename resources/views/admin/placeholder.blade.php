@extends('layouts.admin-dashboard')

@php
    $titles = [
        'dashboard' => 'Dashboard & Thống kê',
        'vouchers'  => 'Quản lý Voucher',
        'orders'    => 'Quản lý Đơn hàng',
        'payments'  => 'Quản lý Thanh toán',
        'customers' => 'Quản lý Customer',
        'staff'     => 'Quản lý Staff',
        'reviews'   => 'Quản lý Review',
        'support'   => 'Hỗ trợ Khách hàng',
    ];
    $pageTitle = $titles[$currentPage ?? ''] ?? 'Bảng Quản Trị';
@endphp

@section('page-title', $pageTitle)

@section('content')
<!-- Khu vực trang trống của bạn nhóm để sau này tự gắn code vào -->
@endsection
