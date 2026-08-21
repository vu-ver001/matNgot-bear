<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (Phân quyền Admin)
|--------------------------------------------------------------------------
| Phần của Khánh Vân: Quản lý Sản phẩm & Danh mục.
| Các mục khác để trống chờ bạn trong nhóm gắn route/controller vào.
*/

Route::prefix('admin')->name('admin.')->group(function () {
    
    // 1. Mặc định vào thẳng Quản lý Sản phẩm (Phần của Khánh Vân)
    Route::get('/', function () {
        return redirect()->route('admin.products.index');
    })->name('dashboard');

    Route::get('/dashboard', function () {
        return redirect()->route('admin.products.index');
    });

    // 2. PHẦN CỦA KHÁNH VÂN: Quản lý Sản phẩm (Trang riêng)
    Route::get('/products', function () {
        return view('admin.products.index', ['currentPage' => 'products']);
    })->name('products.index');

    // 3. PHẦN CỦA KHÁNH VÂN: Quản lý Danh mục (Trang riêng)
    Route::get('/categories', function () {
        return view('admin.categories.index', ['currentPage' => 'categories']);
    })->name('categories.index');

    // 4. Các mục của bạn trong nhóm (Chờ gắn route/controller)
    Route::get('/page/{page}', function (string $page) {
        return view('admin.placeholder', ['currentPage' => $page]);
    })->name('page');

    Route::get('/vouchers', fn() => view('admin.placeholder', ['currentPage' => 'vouchers']))->name('vouchers.index');
    Route::get('/orders', fn() => view('admin.placeholder', ['currentPage' => 'orders']))->name('orders.index');
    Route::get('/payments', fn() => view('admin.placeholder', ['currentPage' => 'payments']))->name('payments.index');
    Route::get('/customers', fn() => view('admin.placeholder', ['currentPage' => 'customers']))->name('customers.index');
    Route::get('/staff', fn() => view('admin.placeholder', ['currentPage' => 'staff']))->name('staff.index');
    Route::get('/reviews', fn() => view('admin.placeholder', ['currentPage' => 'reviews']))->name('reviews.index');
    Route::get('/support', fn() => view('admin.placeholder', ['currentPage' => 'support']))->name('support.index');
});
