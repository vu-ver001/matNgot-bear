<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Hiển thị Trang chủ (Home Page).
     */
    public function home(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn($q) => $q->where('status', 'ACTIVE')])
            ->orderBy('name')
            ->get();

        $featuredProducts = Product::query()
            ->where('status', 'ACTIVE')
            ->with([
                'category:id,name',
                'images' => fn($q) => $q->orderByDesc('is_primary')->orderBy('sort_order', 'asc'),
            ])
            ->orderByDesc('sold_count')
            ->take(8)
            ->get();

        $newArrivals = Product::query()
            ->where('status', 'ACTIVE')
            ->with([
                'category:id,name',
                'images' => fn($q) => $q->orderByDesc('is_primary')->orderBy('sort_order', 'asc'),
            ])
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        return view('home', compact('categories', 'featuredProducts', 'newArrivals'));
    }

    /**
     * Hiển thị Trang Danh sách Sản phẩm (Shop / Catalog Page).
     */
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn($q) => $q->where('status', 'ACTIVE')])
            ->orderBy('name')
            ->get();

        $selectedCategory = $request->filled('category_id') 
            ? Category::find($request->input('category_id')) 
            : null;

        return view('shop', compact('categories', 'selectedCategory'));
    }

    /**
     * Hiển thị Trang Chi tiết Sản phẩm (Product Detail Page).
     */
    public function show(int|string $id): View
    {
        $product = Product::query()
            ->where('id', $id)
            ->where('status', 'ACTIVE')
            ->with([
                'category',
                'images' => fn($q) => $q->orderBy('sort_order', 'asc'),
            ])
            ->withAvg(['reviews as avg_rating' => fn($q) => $q->where('is_hidden', false)], 'rating')
            ->withCount(['reviews' => fn($q) => $q->where('is_hidden', false)])
            ->firstOrFail();

        // Lấy các sản phẩm liên quan cùng danh mục
        $relatedProducts = Product::query()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'ACTIVE')
            ->with([
                'category:id,name',
                'images' => fn($q) => $q->orderByDesc('is_primary')->orderBy('sort_order', 'asc'),
            ])
            ->take(4)
            ->get();

        return view('product-detail', compact('product', 'relatedProducts'));
    }
}
