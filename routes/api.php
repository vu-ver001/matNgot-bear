<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\CategoryPublicController;
use App\Http\Controllers\Api\ProductPublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Các route API cho dự án Mật Ngọt Bear.
| Prefix tự động: /api
|
*/

// ==========================================
// 1. PUBLIC API (Khách hàng không cần đăng nhập)
// ==========================================

// Danh mục sản phẩm công khai
Route::get('/categories', [CategoryPublicController::class, 'index'])->name('api.categories.index');

// Sản phẩm nổi bật / bán chạy cho Trang chủ (đặt trước {id} để tránh trùng route)
Route::get('/products/featured', [ProductPublicController::class, 'featured'])->name('api.products.featured');

// Danh sách sản phẩm (tìm kiếm, lọc, sắp xếp, phân trang)
Route::get('/products', [ProductPublicController::class, 'index'])->name('api.products.index');

// Chi tiết sản phẩm
Route::get('/products/{id}', [ProductPublicController::class, 'show'])->name('api.products.show');


// ==========================================
// 2. ADMIN API (Quản trị viên)
// ==========================================
Route::prefix('admin')->name('api.admin.')->group(function () {
    // Category CRUD & Toggle Pin to Header
    Route::patch('categories/{category}/toggle-pin', [AdminCategoryController::class, 'togglePin'])->name('categories.toggle-pin');
    Route::apiResource('categories', AdminCategoryController::class);

    // Product CRUD & Image Management
    Route::post('products/{product}/images', [AdminProductController::class, 'addImage'])->name('products.images.add');
    Route::patch('products/{product}/images/{image}/primary', [AdminProductController::class, 'setPrimaryImage'])->name('products.images.primary');
    Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'deleteImage'])->name('products.images.delete');
    Route::apiResource('products', AdminProductController::class);
});

