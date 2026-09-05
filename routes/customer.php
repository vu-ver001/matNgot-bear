<?php

use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\WishlistKT\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('customer')->name('customer.')->group(function () {
    // Auth Protected Routes (Giỏ hàng, Thanh toán, Đơn hàng)
    Route::middleware(['auth'])->group(function () {
        // 1. Cart routes (Chuẩn hóa /customer/cart)
        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::get('/cart-index', [CartController::class, 'index'])->name('cart.index');
        Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
        Route::post('/cart/add', [CartController::class, 'store'])->name('cart.store');
        Route::post('/cart/log-uncheck', [CartController::class, 'logUncheck'])->name('cart.log_uncheck');
        Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
        Route::delete('/cart-clear', [CartController::class, 'clear'])->name('cart.clear');

        // 2. Checkout routes
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
        Route::get('/checkout-index', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
        Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
        Route::match(['GET', 'POST'], '/checkout/calculate-shipping', [CheckoutController::class, 'calculateShipping'])->name('checkout.calculate_shipping');

        // 3. Payment Gateway / QR routes
        Route::get('/payment/qr/{order}', [PaymentController::class, 'showQR'])->name('payment.qr');
        Route::get('/payment/status/{order}', [PaymentController::class, 'checkStatus'])->name('payment.status');
        Route::post('/payment/simulate/{order}', [PaymentController::class, 'simulatePayment'])->name('payment.simulate');
        Route::get('/payment/vnpay/redirect/{order}', [PaymentController::class, 'redirectToVnpay'])->name('payment.vnpay.redirect');
        Route::get('/payment/momo/redirect/{order}', [PaymentController::class, 'redirectToMomo'])->name('payment.momo.redirect');
        Route::post('/payment/confirm/{order}', [PaymentController::class, 'confirmPayment'])->name('payment.confirm');
        Route::post('/payment/retry/{order}', [PaymentController::class, 'retryPayment'])->name('payment.retry');

        // 4. Wishlist (Kim Tuyến)
        Route::prefix('wishlist')->name('wishlist.')->middleware(['role:CUSTOMER'])->group(function () {
            Route::get('/', [WishlistController::class, 'index'])->name('index');
            Route::delete('/', [WishlistController::class, 'clear'])->name('clear');
            Route::delete('/{product}', [WishlistController::class, 'destroy'])->name('destroy');
        });

        // 5. Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::patch('/orders/{order}/shipping-address', [OrderController::class, 'updateShippingAddress'])->name('orders.update_shipping_address');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
        Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder'])->name('orders.reorder');
    });

    // 5. Profile
    Route::get('/profile', function () {
        return redirect()->route('profile.edit');
    })->name('profile');
});

// ==========================================
// PUBLIC PAYMENT RETURN & IPN WEBHOOKS (VNPay & MoMo & Banking)
// ==========================================
// VNPay Return & IPN
Route::get('/payment/vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payment.vnpay.return');
Route::match(['GET', 'POST'], '/payment/vnpay/ipn', [PaymentController::class, 'vnpayIpn'])->name('payment.vnpay.ipn');

// MoMo Return & IPN
Route::get('/payment/momo/return', [PaymentController::class, 'momoReturn'])->name('payment.momo.return');
Route::post('/payment/momo/ipn', [PaymentController::class, 'momoIpn'])->name('payment.momo.ipn');

// Unified Payment Result Page
Route::get('/payment/result/{order}', [PaymentController::class, 'paymentResult'])->name('payment.result');

// Public Webhook listener for SePAY, Casso & Banking auto-payment
Route::post('/api/payment/webhook', [PaymentController::class, 'handleWebhook'])->name('payment.webhook');
Route::post('/webhook/payment', [PaymentController::class, 'handleWebhook']);
Route::post('/webhook/sepay', [PaymentController::class, 'handleWebhook']);
// Shortcut alias routes tiện lợi ngoài root
Route::get('/wishlist', fn() => redirect()->route('customer.wishlist.index'));
Route::get('/cart', fn() => redirect()->route('customer.cart'));
Route::get('/my-orders', fn() => redirect()->route('customer.orders.index'));
Route::get('/checkout', fn() => redirect()->route('customer.checkout.index'));
