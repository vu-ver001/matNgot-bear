<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Staff Routes (Phân quyền Nhân viên)
|--------------------------------------------------------------------------
| Các mục nghiệp vụ của Staff có route đầy đủ và trang trống để bạn nhóm tự code sau.
*/

Route::prefix('staff')->name('staff.')->group(function () {
    
    // Trang chủ Staff & các mục nghiệp vụ (Trang trống chờ bạn nhóm)
    Route::get('/', fn() => view('staff.placeholder', ['currentPage' => 'dashboard']))->name('dashboard');
    Route::get('/orders', fn() => view('staff.placeholder', ['currentPage' => 'orders']))->name('orders.index');
    Route::get('/order-status', fn() => view('staff.placeholder', ['currentPage' => 'order-status']))->name('order-status.index');
    Route::get('/payments', fn() => view('staff.placeholder', ['currentPage' => 'payments']))->name('payments.index');
    Route::get('/support', fn() => view('staff.placeholder', ['currentPage' => 'support']))->name('support.index');
});
