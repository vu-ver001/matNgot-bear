@extends('layouts.staff-dashboard')

@php
    $titles = [
        'dashboard'    => 'Dashboard Vận Hành',
        'orders'       => 'Quản Lý Đơn Hàng',
        'order-status' => 'Xử Lý Trạng Thái Đơn',
        'payments'     => 'Xử Lý Thanh Toán',
        'support'      => 'Hỗ Trợ Khách Hàng',
    ];
    $pageTitle = $titles[$currentPage ?? ''] ?? 'Khu Vực Xử Lý Staff';
@endphp

@section('page-title', $pageTitle)

@section('content')
<!-- Khu vực trang trống của bạn nhóm để sau này tự gắn code vào -->
@endsection
