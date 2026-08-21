<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductPublicController extends Controller
{
    /**
     * Danh sách sản phẩm (Search, Filter, Sort, Phân trang) cho Khách hàng.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('status', 'ACTIVE')
            ->with([
                'category:id,name',
                'images' => function ($q) {
                    $q->orderByDesc('is_primary')->orderBy('sort_order', 'asc');
                },
            ]);

        // 1. Tìm kiếm từ khóa theo tên hoặc mô tả
        if ($request->filled('search')) {
            $keyword = trim($request->input('search'));
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // 2. Lọc theo danh mục
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // 3. Lọc theo khoảng giá bán thực tế COALESCE(sale_price, price)
        if ($request->filled('min_price')) {
            $query->whereRaw('COALESCE(sale_price, price) >= ?', [(float) $request->input('min_price')]);
        }
        if ($request->filled('max_price')) {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [(float) $request->input('max_price')]);
        }

        // 4. Lọc theo kích thước (size)
        if ($request->filled('size')) {
            $query->where('size', 'like', '%' . trim($request->input('size')) . '%');
        }

        // 5. Lọc theo màu sắc (color)
        if ($request->filled('color')) {
            $query->where('color', 'like', '%' . trim($request->input('color')) . '%');
        }

        // 6. Lọc theo chất liệu (material)
        if ($request->filled('material')) {
            $query->where('material', 'like', '%' . trim($request->input('material')) . '%');
        }

        // 7. Lọc còn hàng (in_stock)
        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        // 8. Sắp xếp (Sort)
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_asc'   => $query->orderByRaw('COALESCE(sale_price, price) ASC'),
            'price_desc'  => $query->orderByRaw('COALESCE(sale_price, price) DESC'),
            'best_seller' => $query->orderByDesc('sold_count'),
            default       => $query->orderByDesc('created_at'),
        };

        // Phân trang (mặc định 12 sản phẩm/trang)
        $perPage = (int) $request->input('per_page', 12);
        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * Chi tiết sản phẩm kèm category, images, avg_rating, reviews_count và is_in_stock.
     */
    public function show(int|string $id): JsonResponse
    {
        $product = Product::query()
            ->where('id', $id)
            ->where('status', 'ACTIVE')
            ->with([
                'category',
                'images' => function ($q) {
                    $q->orderBy('sort_order', 'asc');
                },
            ])
            ->withAvg(['reviews as avg_rating' => function ($q) {
                $q->where('is_hidden', false);
            }], 'rating')
            ->withCount(['reviews' => function ($q) {
                $q->where('is_hidden', false);
            }])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.',
            ], 404);
        }

        // Bổ sung các thuộc tính tính toán tiện lợi
        $productArray = $product->toArray();
        $productArray['avg_rating'] = $product->avg_rating ? round((float) $product->avg_rating, 1) : 0;
        $productArray['is_in_stock'] = $product->stock_quantity > 0;

        return response()->json([
            'success' => true,
            'data'    => $productArray,
        ]);
    }

    /**
     * Lấy 8 sản phẩm nổi bật / bán chạy nhất để hiển thị Trang chủ.
     */
    public function featured(): JsonResponse
    {
        $products = Product::query()
            ->where('status', 'ACTIVE')
            ->with([
                'category:id,name',
                'images' => function ($q) {
                    $q->orderByDesc('is_primary')->orderBy('sort_order', 'asc');
                },
            ])
            ->orderByDesc('sold_count')
            ->take(8)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }
}
