<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Danh sách sản phẩm kèm category và images.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'images']);

        // Tìm kiếm theo tên sản phẩm
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        // Lọc theo danh mục
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Lọc theo khoảng giá
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->input('price_max'));
        }

        // Sắp xếp
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_asc'   => $query->orderByRaw('COALESCE(sale_price, price) ASC'),
            'price_desc'  => $query->orderByRaw('COALESCE(sale_price, price) DESC'),
            'stock_asc'   => $query->orderBy('stock_quantity', 'asc'),
            'best_seller' => $query->orderByDesc('sold_count'),
            default       => $query->orderByDesc('created_at'),
        };

        $perPage = (int) $request->input('per_page', 8);
        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách sản phẩm thành công.',
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
     * Tạo mới sản phẩm kèm danh sách ảnh.
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = DB::transaction(function () use ($request) {
            // Tạo sản phẩm
            $product = Product::create($request->safe()->except('images'));

            // Xử lý danh sách ảnh
            if ($request->has('images')) {
                $this->syncImages($product, $request->input('images'));
            }

            return $product;
        });

        $product->load(['category', 'images']);

        return response()->json([
            'success' => true,
            'message' => 'Tạo sản phẩm thành công.',
            'data'    => $product,
        ], 201);
    }

    /**
     * Chi tiết sản phẩm kèm category và images.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'images']);

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết sản phẩm thành công.',
            'data'    => $product,
        ]);
    }

    /**
     * Cập nhật thông tin sản phẩm và danh sách ảnh.
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        DB::transaction(function () use ($request, $product) {
            // Cập nhật thông tin sản phẩm
            $product->update($request->safe()->except('images'));

            // Cập nhật danh sách ảnh (nếu có gửi lên)
            if ($request->has('images')) {
                // Xóa ảnh cũ và tạo lại
                $product->images()->delete();
                $this->syncImages($product, $request->input('images'));
            }
        });

        $product->load(['category', 'images']);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công.',
            'data'    => $product,
        ]);
    }

    /**
     * Ngừng kinh doanh sản phẩm (cập nhật status thành INACTIVE thay vì xóa vĩnh viễn).
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->update(['status' => 'INACTIVE']);

        return response()->json([
            'success' => true,
            'message' => 'Ngừng kinh doanh sản phẩm thành công.',
        ]);
    }

    /**
     * Đồng bộ danh sách ảnh cho sản phẩm.
     * Đảm bảo chỉ có đúng 1 ảnh is_primary = true.
     *
     * @param Product $product
     * @param array<int, array<string, mixed>> $images
     */
    private function syncImages(Product $product, array $images): void
    {
        $hasPrimary = false;

        foreach ($images as $index => $imageData) {
            $isPrimary = !empty($imageData['is_primary']);

            // Đảm bảo chỉ có 1 ảnh đại diện
            if ($isPrimary && $hasPrimary) {
                $isPrimary = false;
            }
            if ($isPrimary) {
                $hasPrimary = true;
            }

            ProductImage::create([
                'product_id' => $product->id,
                'image_url'  => $imageData['image_url'],
                'is_primary' => $isPrimary,
                'sort_order' => $imageData['sort_order'] ?? $index,
            ]);
        }

        // Nếu không có ảnh nào được chọn làm đại diện, set ảnh đầu tiên
        if (!$hasPrimary && count($images) > 0) {
            $product->images()->oldest('sort_order')->first()?->update(['is_primary' => true]);
        }
    }
}
