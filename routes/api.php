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

// Gợi ý tìm kiếm tức thì trên header (Live Search Suggestions)
Route::get('/products/search-suggestions', [ProductPublicController::class, 'suggestions'])->name('api.products.suggestions');

// Chi tiết sản phẩm
Route::get('/products/{id}', [ProductPublicController::class, 'show'])->name('api.products.show');


// ==========================================
// 2. ADMIN API (Quản trị viên)
// ==========================================
Route::prefix('admin')->name('api.admin.')->group(function () {
    // Category CRUD & Toggle Pin & Header Megamenu
    Route::patch('categories/{category}/toggle-pin', [AdminCategoryController::class, 'togglePin'])->name('categories.toggle-pin');
    Route::get('categories/{category}/header-menu', [AdminCategoryController::class, 'getHeaderMenu'])->name('categories.header-menu.get');
    Route::post('categories/{category}/header-menu', [AdminCategoryController::class, 'saveHeaderMenu'])->name('categories.header-menu.save');
    Route::apiResource('categories', AdminCategoryController::class);

    // Product CRUD & Image Management
    Route::patch('products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::post('products/{product}/images', [AdminProductController::class, 'addImage'])->name('products.images.add');
    Route::patch('products/{product}/images/{image}/primary', [AdminProductController::class, 'setPrimaryImage'])->name('products.images.primary');
    Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'deleteImage'])->name('products.images.delete');
    Route::get('products/stats', [AdminProductController::class, 'productsStats'])->name('products.stats');
    Route::apiResource('products', AdminProductController::class);

    // Product Variants API (Sản phẩm con)
    Route::get('product-variants/stats', [AdminProductController::class, 'variantsStats'])->name('variants.stats');
    Route::patch('product-variants/{variant}/toggle-status', [AdminProductController::class, 'toggleVariantStatus'])->name('variants.toggle-status');
    Route::get('product-variants', [AdminProductController::class, 'variantsList'])->name('variants.index');
});

