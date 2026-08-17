<?php

use Illuminate\Support\Facades\Route;

Route::prefix('customer')->name('customer.')->middleware(['auth'])->group(function () {
    // Product browsing (Customer)
    // Route::get('/', [ProductController::class, 'index'])->name('products.index');
    // Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // Wishlist
    // Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    // Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Review
    // Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Chat
    // Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    // Route::post('/chat', [ChatController::class, 'send'])->name('chat.send');
});
