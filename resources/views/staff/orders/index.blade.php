@extends('layouts.staff-dashboard')
@section('page-title', 'Xử lý đơn hàng')
@section('content')
    @include('orders.index', ['routePrefix' => 'staff.orders', 'isStaff' => true])
@endsection
