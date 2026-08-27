<?php

use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\WishlistKT\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->name('customer.')->middleware(['auth', 'role:CUSTOMER'])->group(function () {

    // 1. Cart routes (hỗ trợ cả customer.cart và customer.cart.index)
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::get('/cart-index', [CartController::class, 'index'])->name('cart.index');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/log-uncheck', [CartController::class, 'logUncheck'])->name('cart.log_uncheck');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart-clear', [CartController::class, 'clear'])->name('cart.clear');

    // 2. Checkout routes (hỗ trợ cả customer.checkout và customer.checkout.index)
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::get('/checkout-index', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::match(['GET', 'POST'], '/checkout/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('checkout.calculate_shipping');

    // 3. Payment Gateway / QR routes
    Route::get('/payment/qr/{order}', [PaymentController::class, 'showQR'])->name('payment.qr');
    Route::get('/payment/vnpay/redirect/{order}', [PaymentController::class, 'redirectToVnpay'])->name('payment.vnpay.redirect');
    Route::get('/payment/vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payment.vnpay.return');
    Route::post('/payment/confirm/{order}', [PaymentController::class, 'confirmPayment'])->name('payment.confirm');

    // 4. Wishlist (Kim Tuyến)
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::delete('/', [WishlistController::class, 'clear'])->name('clear');
        Route::delete('/{product}', [WishlistController::class, 'destroy'])->name('destroy');
    });

    // 5. Orders (Anh Vũ)
    Route::middleware(['auth'])->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });

    // 6. Profile
    Route::get('/profile', function () {
        return redirect()->route('profile.edit');
    })->name('profile');
});

// Shortcut alias routes tiện lợi ngoài root
Route::get('/wishlist', fn() => redirect()->route('customer.wishlist.index'));
Route::get('/cart', fn() => redirect()->route('customer.cart'));
Route::get('/my-orders', fn() => redirect()->route('customer.orders.index'));
Route::get('/checkout', fn() => redirect()->route('customer.checkout.index'));
