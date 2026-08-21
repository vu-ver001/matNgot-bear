<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (Phân quyền Admin)
|--------------------------------------------------------------------------
| Phần của Khánh Vân: Quản lý Sản phẩm & Danh mục.
| Các mục khác có route đầy đủ và trang trống để bạn nhóm tự code sau.
*/

Route::prefix('admin')->name('admin.')->group(function () {
    
    // 1. Mặc định /admin chuyển vào Quản lý Sản phẩm
    Route::get('/', function () {
        return redirect()->route('admin.products.index');
    });

    // 2. PHẦN CỦA KHÁNH VÂN: Quản lý Sản phẩm & Danh mục (Trang riêng hoàn thiện)
    Route::get('/products', function () {
        return view('admin.products.index', ['currentPage' => 'products']);
    })->name('products.index');

    Route::get('/categories', function () {
        return view('admin.categories.index', ['currentPage' => 'categories']);
    })->name('categories.index');

    // 3. CÁC MỤC CỦA BẠN NHÓM (Click vào được, trang trống để bạn nhóm tự code vào sau)
    Route::get('/dashboard', fn() => view('admin.placeholder', ['currentPage' => 'dashboard']))->name('dashboard');
    Route::get('/vouchers', fn() => view('admin.placeholder', ['currentPage' => 'vouchers']))->name('vouchers.index');
    Route::get('/orders', fn() => view('admin.placeholder', ['currentPage' => 'orders']))->name('orders.index');
    Route::get('/payments', fn() => view('admin.placeholder', ['currentPage' => 'payments']))->name('payments.index');
    Route::get('/customers', fn() => view('admin.placeholder', ['currentPage' => 'customers']))->name('customers.index');
    Route::get('/staff', fn() => view('admin.placeholder', ['currentPage' => 'staff']))->name('staff.index');
    Route::get('/reviews', fn() => view('admin.placeholder', ['currentPage' => 'reviews']))->name('reviews.index');
    Route::get('/support', fn() => view('admin.placeholder', ['currentPage' => 'support']))->name('support.index');
});
