<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Danh sách sản phẩm kèm category và images (Hỗ trợ cả Web View và JSON API).
     */
    public function index(Request $request): View|JsonResponse
    {
        if (!$request->wantsJson() && !$request->is('api/*')) {
            return view('admin.products.index', ['currentPage' => 'products']);
        }

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
     * Hiển thị trang Thêm mới sản phẩm (Trang riêng biệt, không popup).
     */
    public function create(): View
    {
        $categories = Category::all();
        return view('admin.products.create', [
            'categories'  => $categories,
            'currentPage' => 'products',
        ]);
    }

    /**
     * Hiển thị trang Chỉnh sửa sản phẩm (Trang riêng biệt).
     */
    public function edit(Product $product): View
    {
        $product->load(['category', 'images']);
        $categories = Category::all();

        return view('admin.products.edit', [
            'product'     => $product,
            'categories'  => $categories,
            'currentPage' => 'products',
        ]);
    }

    /**
     * Tạo mới sản phẩm kèm danh sách ảnh tải lên từ máy tính (Tối đa 6 ảnh).
     */
    public function store(ProductRequest $request): JsonResponse|RedirectResponse
    {
        $product = DB::transaction(function () use ($request) {
            // Tạo sản phẩm
            $product = Product::create($request->safe()->except(['images', 'image_files', 'primary_index']));

            // Xử lý các file ảnh được tải lên từ máy tính (tối đa 6 ảnh)
            if ($request->hasFile('image_files')) {
                $files = array_slice($request->file('image_files'), 0, 6);
                $primaryIndex = (int) $request->input('primary_index', 0);
                $uploadPath = public_path('uploads/products');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                foreach ($files as $index => $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $filename);
                    $imageUrl = asset('uploads/products/' . $filename);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url'  => $imageUrl,
                        'is_primary' => ($index === $primaryIndex),
                        'sort_order' => $index,
                    ]);
                }
            } elseif ($request->has('images')) {
                // Fallback nếu gửi mảng URL từ API
                $images = array_slice($request->input('images'), 0, 6);
                $this->syncImages($product, $images);
            }

            // Đảm bảo luôn có 1 ảnh đại diện nếu có ảnh
            if ($product->images()->count() > 0 && !$product->images()->where('is_primary', true)->exists()) {
                $product->images()->first()?->update(['is_primary' => true]);
            }

            return $product;
        });

        $product->load(['category', 'images']);

        if (!$request->wantsJson() && !$request->is('api/*')) {
            return redirect()->route('admin.products.index')->with('success', 'Thêm sản phẩm mới "' . $product->name . '" thành công!');
        }

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
     * Cập nhật thông tin sản phẩm và danh sách ảnh tải lên từ máy tính.
     */
    public function update(ProductRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            // Cập nhật thông tin sản phẩm
            $product->update($request->safe()->except(['images', 'image_files', 'kept_image_ids', 'primary_type', 'primary_id', 'primary_index']));

            $keptIds = $request->input('kept_image_ids', []);
            // Xóa các ảnh cũ không nằm trong danh sách giữ lại
            $product->images()->whereNotIn('id', $keptIds)->delete();

            $primaryType = $request->input('primary_type', 'existing');
            $primaryId = $request->input('primary_id');
            $primaryIndex = (int) $request->input('primary_index', 0);

            // Bỏ cờ chính của tất cả ảnh hiện có trước khi gán lại
            $product->images()->update(['is_primary' => false]);

            if ($primaryType === 'existing' && $primaryId) {
                $product->images()->where('id', $primaryId)->update(['is_primary' => true]);
            }

            // Tải lên các file ảnh mới nếu có
            if ($request->hasFile('image_files')) {
                $existingCount = $product->images()->count();
                $remainingSlots = max(0, 6 - $existingCount);
                $files = array_slice($request->file('image_files'), 0, $remainingSlots);

                $uploadPath = public_path('uploads/products');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                foreach ($files as $index => $file) {
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $filename);
                    $imageUrl = asset('uploads/products/' . $filename);

                    $isPrimary = ($primaryType === 'new' && $index === $primaryIndex);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url'  => $imageUrl,
                        'is_primary' => $isPrimary,
                        'sort_order' => $existingCount + $index,
                    ]);
                }
            }

            // Đảm bảo có đúng 1 ảnh chính nếu sản phẩm có ảnh
            if ($product->images()->count() > 0 && !$product->images()->where('is_primary', true)->exists()) {
                $product->images()->first()?->update(['is_primary' => true]);
            }
        });

        $product->load(['category', 'images']);

        if (!$request->wantsJson() && !$request->is('api/*')) {
            return redirect()->route('admin.products.index')->with('success', 'Cập nhật sản phẩm #' . $product->id . ' thành công!');
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công.',
            'data'    => $product,
        ]);
    }


    /**
     * Ngừng kinh doanh sản phẩm (cập nhật status thành INACTIVE thay vì xóa vĩnh viễn).
     */
    public function destroy(Product $product): JsonResponse|RedirectResponse
    {
        $product->update(['status' => 'INACTIVE']);

        if (!request()->wantsJson() && !request()->is('api/*')) {
            return redirect()->route('admin.products.index')->with('success', 'Đã ngừng kinh doanh sản phẩm #' . $product->id . '!');
        }

        return response()->json([
            'success' => true,
            'message' => 'Ngừng kinh doanh sản phẩm thành công.',
        ]);
    }


    /**
     * Thêm 1 ảnh vào thư viện của sản phẩm.
     */
    public function addImage(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'image_url' => ['required', 'string', 'max:500'],
        ]);

        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_url'  => $request->input('image_url'),
            'is_primary' => !$hasPrimary, // Nếu chưa có ảnh chính nào thì ảnh đầu tiên sẽ là ảnh chính
            'sort_order' => $product->images()->count(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm ảnh sản phẩm thành công.',
            'data'    => $image,
        ], 201);
    }

    /**
     * Đặt ảnh làm ảnh đại diện chính.
     */
    public function setPrimaryImage(Product $product, ProductImage $image): JsonResponse
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['success' => false, 'message' => 'Ảnh không thuộc sản phẩm này.'], 422);
        }

        // Bỏ cờ chính của tất cả ảnh thuộc sản phẩm
        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Đã đặt làm ảnh chính.',
        ]);
    }

    /**
     * Xóa ảnh khỏi sản phẩm.
     */
    public function deleteImage(Product $product, ProductImage $image): JsonResponse
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['success' => false, 'message' => 'Ảnh không thuộc sản phẩm này.'], 422);
        }

        $wasPrimary = $image->is_primary;
        $image->delete();

        // Nếu vừa xóa ảnh chính, tự động gán ảnh còn lại đầu tiên làm ảnh chính
        if ($wasPrimary) {
            $product->images()->first()?->update(['is_primary' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa ảnh thành công.',
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

