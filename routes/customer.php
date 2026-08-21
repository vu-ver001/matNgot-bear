<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Routes (Khách hàng)
|--------------------------------------------------------------------------
| Các trang do bạn trong nhóm phụ trách: Giỏ hàng, Wishlist, Đơn hàng, Profile.
| Được tạo khung sẵn (Placeholder) để bạn nhóm dễ dàng cắm view/controller vào.
*/

Route::prefix('customer')->name('customer.')->group(function () {
    
    // 1. Danh sách Yêu Thích (Wishlist)
    Route::get('/wishlist', function () {
        return view('customer.placeholder', [
            'pageTitle' => 'Danh Sách Yêu Thích (Wishlist)',
            'pageIcon'  => 'fa-solid fa-heart',
            'pageDesc'  => 'Trang lưu trữ các sản phẩm gấu bông bạn yêu thích để dễ dàng mua sau.',
            'routeCode' => "Route::get('/customer/wishlist', [WishlistController::class, 'index'])->name('customer.wishlist');",
        ]);
    })->name('wishlist');

    // 2. Giỏ Hàng (Cart)
    Route::get('/cart', function () {
        return view('customer.placeholder', [
            'pageTitle' => 'Giỏ Hàng Của Bạn',
            'pageIcon'  => 'fa-solid fa-bag-shopping',
            'pageDesc'  => 'Trang quản lý các sản phẩm gấu bông đã thêm vào giỏ và tiến hành thanh toán đặt hàng.',
            'routeCode' => "Route::get('/customer/cart', [CartController::class, 'index'])->name('customer.cart');",
        ]);
    })->name('cart');

    // 3. Đơn Hàng Của Tôi (My Orders)
    Route::get('/orders', function () {
        return view('customer.placeholder', [
            'pageTitle' => 'Đơn Hàng Của Tôi',
            'pageIcon'  => 'fa-solid fa-clipboard-list',
            'pageDesc'  => 'Trang theo dõi lịch sử và tiến độ giao nhận các đơn hàng bạn đã đặt mua.',
            'routeCode' => "Route::get('/customer/orders', [OrderController::class, 'myOrders'])->name('customer.orders');",
        ]);
    })->name('orders');

    // 4. Hồ sơ khách hàng
    Route::get('/profile', function () {
        return redirect()->route('profile.edit');
    })->name('profile');
});

// Shortcut alias routes tiện lợi
Route::get('/wishlist', fn() => redirect()->route('customer.wishlist'));
Route::get('/cart', fn() => redirect()->route('customer.cart'));
Route::get('/my-orders', fn() => redirect()->route('customer.orders'));
