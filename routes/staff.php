<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Staff Routes (Phân quyền Nhân viên)
|--------------------------------------------------------------------------
| Khung nghiệp vụ cho Staff: Dashboard, Đơn hàng, Trạng thái, Thanh toán, Hỗ trợ.
| Khi bạn nhóm bàn giao, thay các closure bằng Controller tương ứng.
*/

Route::prefix('staff')->name('staff.')->group(function () {
    
    // Trang chủ Staff (mặc định vào Dashboard vận hành)
    Route::get('/', function () {
        return view('staff.placeholder', ['currentPage' => 'dashboard']);
    })->name('dashboard');

    // Route động cho các trang mục
    Route::get('/page/{page}', function (string $page) {
        return view('staff.placeholder', ['currentPage' => $page]);
    })->name('page');

    // Các routes tường minh cho từng mục
    Route::get('/orders', fn() => view('staff.placeholder', ['currentPage' => 'orders']))->name('orders.index');
    Route::get('/order-status', fn() => view('staff.placeholder', ['currentPage' => 'order-status']))->name('order-status.index');
    Route::get('/payments', fn() => view('staff.placeholder', ['currentPage' => 'payments']))->name('payments.index');
    Route::get('/support', fn() => view('staff.placeholder', ['currentPage' => 'support']))->name('support.index');
});
