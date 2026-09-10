<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
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

        $query = Product::with(['category', 'images', 'variants']);

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

        // Lọc theo tình trạng tồn kho
        if ($request->filled('stock_status')) {
            $stockStatus = $request->input('stock_status');
            if ($stockStatus === 'in_stock') {
                $query->where('stock_quantity', '>', 5);
            } elseif ($stockStatus === 'low_stock') {
                $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5);
            } elseif ($stockStatus === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($stockStatus === 'warning') {
                // Một sản phẩm cha được tính là "cần cảnh báo" nếu chứa ít nhất 1 phân loại con (size/màu) có số lượng tồn kho <= 5
                $query->where(function ($q) {
                    $q->whereHas('variants', function ($vq) {
                        $vq->where('stock_quantity', '<=', 5);
                    })->orWhere(function ($pq) {
                        $pq->doesntHave('variants')->where('stock_quantity', '<=', 5);
                    });
                });
            }
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
        $product->load([
            'category', 
            'images' => fn($q) => $q->orderBy('sort_order', 'asc'),
            'variants' => fn($q) => $q->orderBy('id', 'asc')
        ]);
        $categories = Category::all();

        return view('admin.products.edit', [
            'product'     => $product,
            'categories'  => $categories,
            'currentPage' => 'products',
        ]);
    }

    /**
     * Tạo mới sản phẩm kèm danh sách ảnh và các sản phẩm con (biến thể).
     */
    public function store(ProductRequest $request): JsonResponse|RedirectResponse
    {
        $product = DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $parentData = array_diff_key($validated, array_flip([
                'images', 'image_files', 'primary_index', 
                'variants', 'variant_images'
            ]));

            $variantsInput = $request->input('variants', []);
            if (is_string($variantsInput)) {
                $variantsInput = json_decode($variantsInput, true) ?: [];
            }

            // Tính toán giá trị mặc định từ biến thể nếu có
            $defaultPrice = null;
            $defaultSalePrice = null;
            $defaultStock = 0;
            $defaultSize = null;
            $defaultColor = null;

            if (!empty($variantsInput)) {
                $prices = array_column($variantsInput, 'price');
                $defaultPrice = !empty($prices) ? min(array_filter($prices, fn($p) => is_numeric($p))) : 0;
                
                $stocks = array_column($variantsInput, 'stock_quantity');
                $defaultStock = array_sum(array_map('intval', $stocks));

                $firstVariant = $variantsInput[0] ?? [];
                $defaultSize = $firstVariant['size'] ?? null;
                $defaultColor = $firstVariant['color'] ?? null;
                $defaultSalePrice = !empty($firstVariant['sale_price']) ? $firstVariant['sale_price'] : null;

                foreach ($variantsInput as $v) {
                    if (!empty($v['is_default'])) {
                        $defaultPrice = $v['price'] ?? $defaultPrice;
                        $defaultSalePrice = !empty($v['sale_price']) ? $v['sale_price'] : $defaultSalePrice;
                        $defaultSize = $v['size'] ?? $defaultSize;
                        $defaultColor = $v['color'] ?? $defaultColor;
                        break;
                    }
                }
            }

            if (!isset($parentData['price']) || $parentData['price'] === null) {
                $parentData['price'] = $defaultPrice ?? 0;
            }
            if (!isset($parentData['sale_price']) || $parentData['sale_price'] === null) {
                $parentData['sale_price'] = $defaultSalePrice;
            }
            if (!isset($parentData['stock_quantity']) || $parentData['stock_quantity'] === null) {
                $parentData['stock_quantity'] = $defaultStock;
            }
            if (!isset($parentData['size']) || empty($parentData['size'])) {
                $parentData['size'] = $defaultSize;
            }
            if (!isset($parentData['color']) || empty($parentData['color'])) {
                $parentData['color'] = $defaultColor;
            }

            // Tạo sản phẩm cha
            $product = Product::create($parentData);

            // Xử lý các file ảnh cha được tải lên từ máy tính (tối đa 6 ảnh)
            $primaryImageUrl = null;
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
                    $isPrimary = ($index === $primaryIndex);

                    if ($isPrimary) {
                        $primaryImageUrl = $imageUrl;
                    }

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_url'  => $imageUrl,
                        'is_primary' => $isPrimary,
                        'sort_order' => $index,
                    ]);
                }
            } elseif ($request->has('images')) {
                $images = array_slice($request->input('images'), 0, 6);
                $this->syncImages($product, $images);
            }

            // Đảm bảo luôn có 1 ảnh đại diện nếu có ảnh
            if ($product->images()->count() > 0 && !$product->images()->where('is_primary', true)->exists()) {
                $product->images()->first()?->update(['is_primary' => true]);
            }
            if (!$primaryImageUrl) {
                $primaryImageUrl = $product->images()->where('is_primary', true)->value('image_url');
            }

            // Xử lý tạo các sản phẩm con (biến thể)
            if (!empty($variantsInput)) {
                $hasDefaultVariant = false;
                $variantUploadPath = public_path('uploads/products/variants');
                if (!file_exists($variantUploadPath)) {
                    mkdir($variantUploadPath, 0755, true);
                }

                foreach ($variantsInput as $vIndex => $vData) {
                    $variantImgUrl = $vData['image_url'] ?? null;

                    // Nếu có upload file ảnh riêng cho biến thể này
                    if ($request->hasFile("variant_images.{$vIndex}")) {
                        $varFile = $request->file("variant_images.{$vIndex}");
                        $vFilename = time() . '_var_' . uniqid() . '.' . $varFile->getClientOriginalExtension();
                        $varFile->move($variantUploadPath, $vFilename);
                        $variantImgUrl = asset('uploads/products/variants/' . $vFilename);
                    }

                    if (empty($variantImgUrl)) {
                        $variantImgUrl = $primaryImageUrl;
                    }

                    $isDef = !empty($vData['is_default']) || (!$hasDefaultVariant && $vIndex === 0);
                    if ($isDef) {
                        $hasDefaultVariant = true;
                    }

                    // Tự sinh SKU nếu chưa có
                    $sku = !empty($vData['sku']) ? $vData['sku'] : ('PRD' . $product->id . '-' . strtoupper(substr(md5(($vData['size'] ?? '') . ($vData['color'] ?? '') . $vIndex), 0, 6)));

                    ProductVariant::create([
                        'product_id'     => $product->id,
                        'sku'            => $sku,
                        'size'           => $vData['size'] ?? null,
                        'color'          => $vData['color'] ?? null,
                        'price'          => $vData['price'] ?? $product->price,
                        'sale_price'     => !empty($vData['sale_price']) ? $vData['sale_price'] : null,
                        'sale_start_at'  => !empty($vData['sale_start_at']) ? $vData['sale_start_at'] : null,
                        'sale_end_at'    => !empty($vData['sale_end_at']) ? $vData['sale_end_at'] : null,
                        'stock_quantity' => (int) ($vData['stock_quantity'] ?? 0),
                        'image_url'      => $variantImgUrl,
                        'is_default'     => $isDef,
                        'status'         => $vData['status'] ?? 'ACTIVE',
                    ]);
                }
            }

            return $product;
        });

        $product->load(['category', 'images', 'variants']);

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
     * Chi tiết sản phẩm kèm category, images và variants.
     */
    public function show(Product $product): JsonResponse
    {
        $product->load([
            'category', 
            'images' => fn($q) => $q->orderBy('sort_order', 'asc'),
            'variants' => fn($q) => $q->orderBy('id', 'asc')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết sản phẩm thành công.',
            'data'    => $product,
        ]);
    }

    /**
     * Cập nhật thông tin sản phẩm, danh sách ảnh và các sản phẩm con (biến thể).
     */
    public function update(ProductRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            $validated = $request->validated();
            $parentData = array_diff_key($validated, array_flip([
                'images', 'image_files', 'kept_image_ids', 
                'primary_type', 'primary_id', 'primary_index',
                'variants', 'variant_images'
            ]));

            // Cập nhật các ảnh cha
            $keptIds = $request->input('kept_image_ids', []);
            $product->images()->whereNotIn('id', $keptIds)->delete();

            $primaryType = $request->input('primary_type', 'existing');
            $primaryId = $request->input('primary_id');
            $primaryIndex = (int) $request->input('primary_index', 0);

            $product->images()->update(['is_primary' => false]);

            if ($primaryType === 'existing' && $primaryId) {
                $product->images()->where('id', $primaryId)->update(['is_primary' => true]);
            }

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

            if ($product->images()->count() > 0 && !$product->images()->where('is_primary', true)->exists()) {
                $product->images()->first()?->update(['is_primary' => true]);
            }

            $primaryImageUrl = $product->images()->where('is_primary', true)->value('image_url');

            // Xử lý biến thể (sản phẩm con)
            $variantsInput = $request->input('variants', []);
            if (is_string($variantsInput)) {
                $variantsInput = json_decode($variantsInput, true) ?: [];
            }

            if (!empty($variantsInput)) {
                $submittedIds = array_filter(array_map(fn($v) => !empty($v['id']) ? (int)$v['id'] : null, $variantsInput));
                
                // Xóa các biến thể không còn trong danh sách gửi lên
                $product->variants()->whereNotIn('id', $submittedIds)->delete();

                $hasDefault = false;
                $variantUploadPath = public_path('uploads/products/variants');
                if (!file_exists($variantUploadPath)) {
                    mkdir($variantUploadPath, 0755, true);
                }

                foreach ($variantsInput as $vIndex => $vData) {
                    $varId = !empty($vData['id']) ? (int)$vData['id'] : null;
                    $variantImgUrl = $vData['image_url'] ?? null;

                    // Nếu có file upload cho biến thể này
                    if ($request->hasFile("variant_images.{$vIndex}")) {
                        $varFile = $request->file("variant_images.{$vIndex}");
                        $vFilename = time() . '_var_' . uniqid() . '.' . $varFile->getClientOriginalExtension();
                        $varFile->move($variantUploadPath, $vFilename);
                        $variantImgUrl = asset('uploads/products/variants/' . $vFilename);
                    }

                    if (empty($variantImgUrl)) {
                        $variantImgUrl = $primaryImageUrl;
                    }

                    $isDef = !empty($vData['is_default']);
                    if ($isDef) {
                        $hasDefault = true;
                    }

                    $vFields = [
                        'sku'            => !empty($vData['sku']) ? $vData['sku'] : ('PRD' . $product->id . '-' . strtoupper(substr(md5(($vData['size'] ?? '') . ($vData['color'] ?? '') . $vIndex), 0, 6))),
                        'size'           => $vData['size'] ?? null,
                        'color'          => $vData['color'] ?? null,
                        'price'          => $vData['price'] ?? $product->price,
                        'sale_price'     => !empty($vData['sale_price']) ? $vData['sale_price'] : null,
                        'sale_start_at'  => !empty($vData['sale_start_at']) ? $vData['sale_start_at'] : null,
                        'sale_end_at'    => !empty($vData['sale_end_at']) ? $vData['sale_end_at'] : null,
                        'stock_quantity' => (int) ($vData['stock_quantity'] ?? 0),
                        'image_url'      => $variantImgUrl,
                        'is_default'     => $isDef,
                        'status'         => $vData['status'] ?? 'ACTIVE',
                    ];

                    if ($varId) {
                        $product->variants()->where('id', $varId)->update($vFields);
                    } else {
                        $vFields['product_id'] = $product->id;
                        ProductVariant::create($vFields);
                    }
                }

                // Nếu chưa có biến thể nào được set default, set biến thể đầu tiên
                if (!$hasDefault) {
                    $product->variants()->first()?->update(['is_default' => true]);
                }

                // Cập nhật lại tổng tồn kho và khoảng giá lên sản phẩm cha
                $allVariants = $product->variants()->get();
                $prices = $allVariants->pluck('price')->filter(fn($p) => is_numeric($p) && $p > 0)->all();
                $defaultVar = $allVariants->firstWhere('is_default', true) ?? $allVariants->first();

                $parentData['price'] = !empty($prices) ? min($prices) : ($defaultVar ? $defaultVar->price : $product->price);
                $parentData['sale_price'] = $defaultVar ? $defaultVar->sale_price : null;
                $parentData['stock_quantity'] = (int) $allVariants->sum('stock_quantity');
                $parentData['size'] = $defaultVar ? $defaultVar->size : $product->size;
                $parentData['color'] = $defaultVar ? $defaultVar->color : $product->color;
            }

            // Cập nhật sản phẩm cha
            $product->update($parentData);
        });

        $product->load(['category', 'images', 'variants']);

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
     * Chuyển đổi trạng thái kinh doanh của sản phẩm (ACTIVE <-> INACTIVE).
     */
    public function toggleStatus(Product $product): JsonResponse
    {
        $newStatus = $product->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $product->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => $newStatus === 'ACTIVE' ? 'Đã kích hoạt sản phẩm mở bán trở lại!' : 'Đã chuyển sản phẩm sang ngừng kinh doanh!',
            'status'  => $newStatus,
            'data'    => $product,
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
     * Danh sách sản phẩm con (biến thể) có phân trang, lọc và tìm kiếm.
     */
    public function variantsList(Request $request): JsonResponse
    {
        $query = ProductVariant::with(['product' => function($q) {
            $q->select('id', 'name', 'category_id', 'status');
        }, 'product.category:id,name']);

        // Tìm kiếm theo tên cha, SKU con, kích thước, màu sắc, ID cha
        if ($request->filled('search')) {
            $kw = trim($request->input('search'));
            $query->where(function($q) use ($kw) {
                $q->where('sku', 'like', "%{$kw}%")
                  ->orWhere('size', 'like', "%{$kw}%")
                  ->orWhere('color', 'like', "%{$kw}%")
                  ->orWhere('product_id', $kw)
                  ->orWhereHas('product', function($pq) use ($kw) {
                      $pq->where('name', 'like', "%{$kw}%")
                         ->orWhere('id', $kw);
                  });
            });
        }

        // Lọc theo danh mục
        if ($request->filled('category_id')) {
            $catId = $request->input('category_id');
            $query->whereHas('product', fn($q) => $q->where('category_id', $catId));
        }

        // Lọc theo trạng thái con
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Lọc theo tồn kho con
        if ($request->filled('stock_status')) {
            $stockStatus = $request->input('stock_status');
            if ($stockStatus === 'in_stock') {
                $query->where('stock_quantity', '>', 5);
            } elseif ($stockStatus === 'low_stock') {
                $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5);
            } elseif ($stockStatus === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($stockStatus === 'warning') {
                $query->where('stock_quantity', '<=', 5);
            }
        }

        // Sắp xếp
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_asc'   => $query->orderByRaw('COALESCE(sale_price, price) ASC'),
            'price_desc'  => $query->orderByRaw('COALESCE(sale_price, price) DESC'),
            'stock_asc'   => $query->orderBy('stock_quantity', 'asc'),
            'best_seller' => $query->orderByDesc('id'),
            default       => $query->orderByDesc('id'),
        };

        $perPage = (int) $request->input('per_page', 10);
        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách phân loại con thành công.',
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
     * Thống kê KPI riêng cho sản phẩm cha.
     * Một sản phẩm cha được tính là "cần cảnh báo" nếu nó chứa ít nhất 1 phân loại con (size/màu) có số lượng tồn kho <= 5.
     */
    public function productsStats(): JsonResponse
    {
        $total = Product::count();
        $active = Product::where('status', 'ACTIVE')->count();
        $inactive = Product::where('status', 'INACTIVE')->count();
        // Cảnh báo tồn kho theo logic yêu cầu của người dùng
        $warning = Product::where(function ($q) {
            $q->whereHas('variants', function ($vq) {
                $vq->where('stock_quantity', '<=', 5);
            })->orWhere(function ($pq) {
                $pq->doesntHave('variants')->where('stock_quantity', '<=', 5);
            });
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total'    => $total,
                'active'   => $active,
                'inactive' => $inactive,
                'warning'  => $warning,
            ]
        ]);
    }

    /**
     * Thống kê KPI riêng cho sản phẩm con (biến thể).
     */
    public function variantsStats(): JsonResponse
    {
        $total = ProductVariant::count();
        $active = ProductVariant::where('status', 'ACTIVE')->count();
        $inactive = ProductVariant::where('status', 'INACTIVE')->count();
        $warning = ProductVariant::where('stock_quantity', '<=', 5)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total'    => $total,
                'active'   => $active,
                'inactive' => $inactive,
                'warning'  => $warning,
            ]
        ]);
    }

    /**
     * Bật / tắt trạng thái kinh doanh của riêng 1 biến thể con.
     */
    public function toggleVariantStatus(ProductVariant $variant): JsonResponse
    {
        $newStatus = ($variant->status === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
        $variant->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "Đã chuyển trạng thái phân loại ({$variant->size} - {$variant->color}) sang " . ($newStatus === 'ACTIVE' ? 'Đang kinh doanh' : 'Ngừng kinh doanh') . ".",
            'data'    => $variant,
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

