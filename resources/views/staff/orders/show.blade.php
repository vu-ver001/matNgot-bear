@extends('layouts.staff-dashboard')
@section('page-title', "Chi tiết đơn hàng #{$order->order_code}")
@section('content')
    @include('orders.show', ['routePrefix' => 'staff.orders', 'isStaff' => true])
@endsection
