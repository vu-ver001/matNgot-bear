<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\OrderDetail;
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
            return view('admin.products.index', [
                'currentPage' => 'products',
            ]);
        }

        $query = Product::query()->with(['category', 'images', 'variants']);

        $query->withExists(['orderDetails as has_been_ordered'])
              ->withExists(['orderDetails as has_pending_orders' => function ($q) {
                  $q->whereHas('order', fn ($oq) => $oq->whereIn('order_status', ['PENDING', 'CONFIRMED', 'PREPARING', 'SHIPPING']));
              }]);

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
            $statusVal = $request->input('status');
            if ($statusVal === 'TRASHED') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $statusVal);
            }
        }

        // Lọc theo tình trạng tồn kho: Lọc ra sản phẩm cha có ít nhất 1 sản phẩm con thỏa mãn điều kiện
        if ($request->filled('stock_status')) {
            $stockStatus = $request->input('stock_status');
            if ($stockStatus === 'in_stock') {
                // Có ít nhất 1 sản phẩm con còn hàng dồi dào (> 5)
                $query->where(function ($q) {
                    $q->whereHas('variants', function ($vq) {
                        $vq->where('stock_quantity', '>', 5);
                    })->orWhere(function ($pq) {
                        $pq->doesntHave('variants')->where('stock_quantity', '>', 5);
                    });
                });
            } elseif ($stockStatus === 'low_stock') {
                // Có ít nhất 1 sản phẩm con sắp hết hàng (1 - 5)
                $query->where(function ($q) {
                    $q->whereHas('variants', function ($vq) {
                        $vq->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5);
                    })->orWhere(function ($pq) {
                        $pq->doesntHave('variants')->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5);
                    });
                });
            } elseif ($stockStatus === 'out_of_stock') {
                // Có ít nhất 1 sản phẩm con đã hết hàng (<= 0)
                $query->where(function ($q) {
                    $q->whereHas('variants', function ($vq) {
                        $vq->where('stock_quantity', '<=', 0);
                    })->orWhere(function ($pq) {
                        $pq->doesntHave('variants')->where('stock_quantity', '<=', 0);
                    });
                });
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
        // Chỉ lấy các danh mục đang kinh doanh (is_active = true)
        $categories = Category::where('is_active', true)->orderBy('name', 'asc')->get();
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
        // Chỉ lấy các danh mục đang kinh doanh (is_active = true)
        $categories = Category::where('is_active', true)->orderBy('name', 'asc')->get();

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
        try {
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

            // Tính toán giá trị từ biến thể: Giá bán của sản phẩm cha luôn lấy giá thấp nhất của sản phẩm con
            $defaultPrice = 0;
            $defaultSalePrice = null;
            $defaultStock = 0;
            $defaultSize = null;
            $defaultColor = null;

            if (!empty($variantsInput)) {
                $numericPrices = array_filter(
                    array_map(fn($v) => isset($v['price']) && is_numeric($v['price']) ? (float)$v['price'] : null, $variantsInput),
                    fn($p) => $p !== null && $p >= 0
                );
                $lowestPrice = !empty($numericPrices) ? min($numericPrices) : 0;
                $defaultStock = array_sum(array_map(fn($v) => (int)($v['stock_quantity'] ?? 0), $variantsInput));

                $firstVariant = $variantsInput[0] ?? [];
                $defaultSize = $firstVariant['size'] ?? null;
                $defaultColor = $firstVariant['color'] ?? null;

                // Giá khuyến mãi thấp nhất hợp lệ
                $salePrices = array_filter(
                    array_map(fn($v) => isset($v['sale_price']) && is_numeric($v['sale_price']) ? (float)$v['sale_price'] : null, $variantsInput),
                    fn($sp) => $sp !== null && $sp >= 0 && $sp < $lowestPrice
                );
                $lowestSalePrice = !empty($salePrices) ? min($salePrices) : null;

                $parentData['price'] = $lowestPrice;
                $parentData['sale_price'] = $lowestSalePrice;
                $parentData['stock_quantity'] = $defaultStock;
                $parentData['size'] = $defaultSize;
                $parentData['color'] = !empty($defaultColor) ? mb_convert_case(trim($defaultColor), MB_CASE_TITLE, "UTF-8") : null;
            } else {
                if (!isset($parentData['price']) || $parentData['price'] === null) {
                    $parentData['price'] = 0;
                }
            }

            // Tạo sản phẩm cha
            $product = Product::create($parentData);

            // Xử lý các file ảnh Bộ ảnh sản phẩm chính được tải lên từ máy tính (tối đa 9 ảnh)
            $primaryImageUrl = null;
            if ($request->hasFile('image_files')) {
                $files = array_slice($request->file('image_files'), 0, 9);
                $primaryIndex = (int) $request->input('primary_index', 0);

                foreach ($files as $index => $file) {
                    $mime = $file->getMimeType() ?: 'image/jpeg';
                    $imageUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
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
                $images = array_slice($request->input('images'), 0, 9);
                $this->syncImages($product, $images);
            }

            // Nếu không có ảnh cha nào được chỉ định từ file, lấy ảnh đầu tiên làm primary
            if (!$primaryImageUrl && $product->images()->count() > 0) {
                $primaryImageUrl = $product->images()->where('is_primary', true)->value('image_url')
                    ?? $product->images()->first()?->image_url;
            }

            // Xử lý các biến thể (sản phẩm con)
            $hasDefaultVariant = false;
            $usedSkusInBatch = [];

            if (!empty($variantsInput)) {
                // Bản đồ ảnh theo màu sắc để tự động kế thừa ảnh giữa các kích thước cùng màu
                $colorImageMap = [];

                // Bước 1: Lưu file tải lên dưới dạng base64 hoặc lưu URL ảnh hợp lệ cho từng dòng và từng nhóm màu
                foreach ($variantsInput as $vIndex => $vData) {
                    $cKey = mb_strtolower(trim($vData['color'] ?? ''));
                    if ($request->hasFile("variant_images.{$vIndex}")) {
                        $varFile = $request->file("variant_images.{$vIndex}");
                        $mime = $varFile->getMimeType() ?: 'image/jpeg';
                        $savedUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($varFile->getRealPath()));
                        $colorImageMap[$vIndex] = $savedUrl;
                        if ($cKey !== '' && !isset($colorImageMap[$cKey])) {
                            $colorImageMap[$cKey] = $savedUrl;
                        }
                    } elseif (!empty($vData['image_url']) && !str_contains($vData['image_url'], 'placehold.co')) {
                        $colorImageMap[$vIndex] = $vData['image_url'];
                        if ($cKey !== '' && !isset($colorImageMap[$cKey])) {
                            $colorImageMap[$cKey] = $vData['image_url'];
                        }
                    }
                }

                foreach ($variantsInput as $vIndex => $vData) {
                    $cKey = mb_strtolower(trim($vData['color'] ?? ''));
                    $rawColor = trim($vData['color'] ?? '');
                    $formattedColor = $rawColor !== '' ? mb_convert_case($rawColor, MB_CASE_TITLE, "UTF-8") : null;

                    $variantImgUrl = $colorImageMap[$vIndex] 
                                    ?? ($cKey !== '' ? ($colorImageMap[$cKey] ?? null) : null) 
                                    ?? (!empty($vData['image_url']) && !str_contains($vData['image_url'], 'placehold.co') ? $vData['image_url'] : null);

                    // Tự sinh SKU duy nhất, chống xung đột trùng lặp tuyệt đối
                    $sku = $this->generateUniqueSku($vData['sku'] ?? null, $product->id, $vIndex, null, $usedSkusInBatch);
                    $hasValidSale = (isset($vData['sale_price']) && $vData['sale_price'] !== '' && $vData['sale_price'] !== null && (float)$vData['sale_price'] >= 0);

                    ProductVariant::create([
                        'product_id'     => $product->id,
                        'sku'            => $sku,
                        'size'           => $vData['size'] ?? null,
                        'color'          => $formattedColor,
                        'price'          => $vData['price'] ?? $product->price,
                        'sale_price'     => $hasValidSale ? $vData['sale_price'] : null,
                        'sale_start_at'  => ($hasValidSale && !empty($vData['sale_start_at'])) ? $vData['sale_start_at'] : null,
                        'sale_end_at'    => ($hasValidSale && !empty($vData['sale_end_at'])) ? $vData['sale_end_at'] : null,
                        'stock_quantity' => (int) ($vData['stock_quantity'] ?? 0),
                        'image_url'      => $variantImgUrl,
                        'status'         => $vData['status'] ?? 'ACTIVE',
                    ]);
                }

                // Đồng bộ chính xác giá bán của sản phẩm cha lấy giá bán thấp nhất của sản phẩm con
                $product->syncLowestPriceFromVariants();
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
        } catch (\Throwable $e) {
            \Log::error('Lỗi tạo sản phẩm: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi lưu sản phẩm: ' . $e->getMessage(),
                ], 422);
            }
            return back()->withInput()->with('error', 'Lỗi lưu sản phẩm: ' . $e->getMessage());
        }
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
        try {
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
                $remainingSlots = max(0, 9 - $existingCount);
                $files = array_slice($request->file('image_files'), 0, $remainingSlots);

                foreach ($files as $index => $file) {
                    $mime = $file->getMimeType() ?: 'image/jpeg';
                    $imageUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));

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

                $usedSkusInBatch = [];

                // Bản đồ ảnh theo màu sắc để tự động kế thừa ảnh giữa các kích thước cùng màu
                $colorImageMap = [];

                // Bước 1: Lưu file tải lên dưới dạng base64 hoặc lấy URL ảnh hợp lệ cho từng dòng và từng nhóm màu
                foreach ($variantsInput as $vIndex => $vData) {
                    $cKey = mb_strtolower(trim($vData['color'] ?? ''));
                    if ($request->hasFile("variant_images.{$vIndex}")) {
                        $varFile = $request->file("variant_images.{$vIndex}");
                        $mime = $varFile->getMimeType() ?: 'image/jpeg';
                        $savedUrl = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($varFile->getRealPath()));
                        $colorImageMap[$vIndex] = $savedUrl;
                        if ($cKey !== '' && !isset($colorImageMap[$cKey])) {
                            $colorImageMap[$cKey] = $savedUrl;
                        }
                    } elseif (!empty($vData['image_url']) && !str_contains($vData['image_url'], 'placehold.co')) {
                        $colorImageMap[$vIndex] = $vData['image_url'];
                        if ($cKey !== '' && !isset($colorImageMap[$cKey])) {
                            $colorImageMap[$cKey] = $vData['image_url'];
                        }
                    }
                }

                foreach ($variantsInput as $vIndex => $vData) {
                    $varId = !empty($vData['id']) ? (int)$vData['id'] : null;
                    $cKey = mb_strtolower(trim($vData['color'] ?? ''));
                    $rawColor = trim($vData['color'] ?? '');
                    $formattedColor = $rawColor !== '' ? mb_convert_case($rawColor, MB_CASE_TITLE, "UTF-8") : null;

                    $variantImgUrl = $colorImageMap[$vIndex] 
                                    ?? ($cKey !== '' ? ($colorImageMap[$cKey] ?? null) : null) 
                                    ?? (!empty($vData['image_url']) && !str_contains($vData['image_url'], 'placehold.co') ? $vData['image_url'] : null);

                    $sku = $this->generateUniqueSku($vData['sku'] ?? null, $product->id, $vIndex, $varId, $usedSkusInBatch);

                    $hasValidSale = (isset($vData['sale_price']) && $vData['sale_price'] !== '' && $vData['sale_price'] !== null && (float)$vData['sale_price'] >= 0);

                    $vFields = [
                        'sku'            => $sku,
                        'size'           => $vData['size'] ?? null,
                        'color'          => $formattedColor,
                        'price'          => $vData['price'] ?? $product->price,
                        'sale_price'     => $hasValidSale ? $vData['sale_price'] : null,
                        'sale_start_at'  => ($hasValidSale && !empty($vData['sale_start_at'])) ? $vData['sale_start_at'] : null,
                        'sale_end_at'    => ($hasValidSale && !empty($vData['sale_end_at'])) ? $vData['sale_end_at'] : null,
                        'stock_quantity' => (int) ($vData['stock_quantity'] ?? 0),
                        'image_url'      => $variantImgUrl,
                        'status'         => $vData['status'] ?? 'ACTIVE',
                    ];

                    if ($varId) {
                        $product->variants()->where('id', $varId)->update($vFields);
                    } else {
                        $vFields['product_id'] = $product->id;
                        ProductVariant::create($vFields);
                    }
                }

                // Tính lại giá cha từ variants (dùng is_on_sale thực tế)
                $allVariants = $product->variants()->get();
                $prices = $allVariants->pluck('price')->filter(fn($p) => is_numeric($p) && (float)$p >= 0)->map(fn($p) => (float)$p)->values()->all();
                $minPrice = !empty($prices) ? min($prices) : (float)$product->price;

                // Chỉ lấy sale_price từ variant ĐANG THỰC SỰ SALE (is_on_sale = true)
                $activeSalePrices = $allVariants
                    ->filter(fn($v) => $v->is_on_sale && $v->sale_price !== null && (float)$v->sale_price >= 0 && (float)$v->sale_price < $minPrice)
                    ->pluck('sale_price')
                    ->map(fn($sp) => (float)$sp)
                    ->values()
                    ->all();
                $minSalePrice = !empty($activeSalePrices) ? min($activeSalePrices) : null;

                $defaultVar = $allVariants->first();

                $parentData['price'] = $minPrice;
                $parentData['sale_price'] = $minSalePrice;
                $parentData['stock_quantity'] = (int) $allVariants->sum('stock_quantity');
                $parentData['size'] = $defaultVar ? $defaultVar->size : $product->size;
                $parentData['color'] = $defaultVar && !empty($defaultVar->color) ? mb_convert_case(trim($defaultVar->color), MB_CASE_TITLE, "UTF-8") : $product->color;
            }

            // Cập nhật sản phẩm cha
            $product->update($parentData);

            // Ràng buộc: Nếu sản phẩm cha tạm dừng kinh doanh, tự động tắt toàn bộ sản phẩm con
            if ($product->status === 'INACTIVE') {
                $product->variants()->update(['status' => 'INACTIVE']);
            }

            // Sync cuối cùng để đảm bảo nhất quán (is_on_sale có thể thay đổi sau update)
            if (!empty($variantsInput)) {
                $product->syncLowestPriceFromVariants();
            }

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
        } catch (\Throwable $e) {
            \Log::error('Lỗi cập nhật sản phẩm: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi cập nhật sản phẩm: ' . $e->getMessage(),
                ], 422);
            }
            return back()->withInput()->with('error', 'Lỗi cập nhật sản phẩm: ' . $e->getMessage());
        }
    }


    /**
     * Xóa sản phẩm cha.
     * Ràng buộc:
     * 1. Chặn xóa nếu có sản phẩm con đang trong đơn hàng chưa hoàn tất (chờ xử lý, đang giao...).
     * 2. Xóa mềm toàn bộ: Chuyển trạng thái sản phẩm cha và toàn bộ sản phẩm con sang INACTIVE (ngừng kinh doanh), đánh dấu deleted_at và ẩn khỏi cửa hàng.
     */
    public function destroy(Product $product): JsonResponse|RedirectResponse
    {
        // 1. Chặn xóa nếu có sản phẩm con trong đơn hàng chưa hoàn tất (chờ xử lý, đang giao)
        if ($product->hasPendingOrders()) {
            $msg = 'Không thể xóa vì có sản phẩm con đang trong đơn hàng xử lý.';
            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }
            return back()->with('error', $msg);
        }

        $name = $product->name;
        $variantCount = $product->variants()->count();

        // 2. Thao tác xóa từ danh sách luôn là XÓA MỀM toàn bộ:
        // Đổi trạng thái sản phẩm cha và toàn bộ sản phẩm con sang INACTIVE, đánh dấu deleted_at
        $product->update(['status' => 'INACTIVE']);
        $product->variants()->update(['status' => 'INACTIVE']);
        $product->variants()->delete(); // Xóa mềm toàn bộ sản phẩm con (cập nhật deleted_at)
        $product->delete(); // Xóa mềm sản phẩm cha (cập nhật deleted_at)

        $msg = "Đã xóa mềm sản phẩm [{$name}] cùng toàn bộ {$variantCount} sản phẩm con thành công (chuyển sang ngừng kinh doanh).";
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'is_soft_deleted' => true,
            ]);
        }
        return redirect()->route('admin.products.index')->with('success', $msg);
    }

    /**
     * Xóa 1 sản phẩm con (biến thể) của sản phẩm cha.
     * Ràng buộc:
     * 1. Chặn xóa nếu sản phẩm cha có đơn hàng chưa hoàn tất.
     * 2. Chặn xóa nếu đây là sản phẩm con duy nhất còn lại của sản phẩm cha.
     * 3. Tự động chuyển cờ mặc định (is_default) nếu biến thể bị xóa là mặc định.
     * 4. Tự động đồng bộ lại khoảng giá thấp nhất và tồn kho của sản phẩm cha.
     */
    public function destroyVariant(ProductVariant $variant): JsonResponse
    {
        $product = $variant->product;

        // 1. Chặn xóa nếu có đơn hàng chưa hoàn tất
        if ($product && $product->hasPendingOrders()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa vì có sản phẩm con đang trong đơn hàng xử lý.',
            ], 422);
        }

        // 2. Chặn xóa nếu đây là sản phẩm con duy nhất còn lại
        if ($product && $product->variants()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa vì đây là phân loại con duy nhất còn lại của sản phẩm [' . $product->name . ']. Một sản phẩm cần có ít nhất 1 phân loại để hiển thị giá và đặt mua. Nếu không muốn bán sản phẩm này nữa, vui lòng tạm dừng kinh doanh hoặc xóa sản phẩm cha.',
            ], 422);
        }

        $variantName = ($variant->size ? $variant->size : '') . ($variant->color ? ' - ' . $variant->color : '');
        $variant->update(['status' => 'INACTIVE']);
        $variant->delete();

        // 4. Đồng bộ lại giá bán và tổng tồn kho sản phẩm cha
        if ($product) {
            $product->syncLowestPriceFromVariants();
        }

        return response()->json([
            'success' => true,
            'message' => "Đã xóa phân loại con [{$variantName}] thành công. Kho và giá của sản phẩm cha đã được tự động đồng bộ lại.",
        ]);
    }

    /**
     * Khôi phục sản phẩm từ thùng rác (Restore).
     * Hỗ trợ chọn lọc danh sách sản phẩm con (biến thể) cần khôi phục / kích hoạt lại.
     */
    public function restore(?Request $request = null, ?int $id = null): JsonResponse|RedirectResponse
    {
        $request = $request ?: request();
        $productId = $id ?? (int) $request->route('id');

        $product = Product::onlyTrashed()->with('variants')->findOrFail($productId);
        $product->status = Product::STATUS_ACTIVE;
        $product->restore();

        // Xử lý các sản phẩm con (biến thể) được chọn khôi phục
        if ($request->has('variant_ids')) {
            $variantIds = (array) $request->input('variant_ids', []);
            if (!empty($variantIds)) {
                // Kích hoạt lại các biến thể được chọn
                $product->variants()->whereIn('id', $variantIds)->update(['status' => 'ACTIVE']);
                // Các biến thể không được chọn sẽ được giữ ở trạng thái ngừng bán (INACTIVE)
                $product->variants()->whereNotIn('id', $variantIds)->update(['status' => 'INACTIVE']);
            } else {
                // Nếu người dùng cố ý bỏ chọn tất cả biến thể
                $product->variants()->update(['status' => 'INACTIVE']);
            }
        } else {
            // Mặc định khôi phục tất cả biến thể đang có
            $product->variants()->update(['status' => 'ACTIVE']);
        }

        // Đồng bộ lại giá bán và tồn kho từ các biến thể còn hoạt động
        $product->syncLowestPriceFromVariants();

        $activeVariantsCount = $product->variants()->where('status', 'ACTIVE')->count();
        if ($product->variants()->count() > 0) {
            $msg = "Đã khôi phục sản phẩm [{$product->name}] cùng {$activeVariantsCount} sản phẩm con thành công!";
        } else {
            $msg = "Đã khôi phục sản phẩm [{$product->name}] thành công!";
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'data'    => $product->fresh(['variants', 'images']),
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Xóa vĩnh viễn sản phẩm khỏi hệ thống (Force Delete).
     * Chỉ được phép nếu sản phẩm chưa từng phát sinh đơn hàng trong quá khứ.
     */
    public function forceDelete(int $id): JsonResponse|RedirectResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        if ($product->hasBeenOrdered()) {
            $msg = "Sản phẩm [{$product->name}] đã từng được đặt trong đơn hàng nên chỉ được phép xóa mềm, không thể xóa vĩnh viễn.";
            if (request()->wantsJson() || request()->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }
            return back()->with('error', $msg);
        }

        $name = $product->name;
        $product->images()->delete();
        $product->variants()->delete();
        $product->forceDelete();

        $msg = "Đã xóa vĩnh viễn sản phẩm [{$name}] khỏi hệ thống!";
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Chuyển đổi trạng thái kinh doanh của sản phẩm (ACTIVE <-> INACTIVE).
     * Ràng buộc: Khi sản phẩm cha tạm dừng kinh doanh (INACTIVE), tự động tắt toàn bộ sản phẩm con.
     */
    public function toggleStatus(Product $product): JsonResponse
    {
        $newStatus = $product->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $product->update(['status' => $newStatus]);

        if ($newStatus === 'INACTIVE') {
            $product->variants()->update(['status' => 'INACTIVE']);
        }

        return response()->json([
            'success' => true,
            'message' => $newStatus === 'ACTIVE' 
                ? 'Đã kích hoạt sản phẩm mở bán trở lại!' 
                : 'Đã chuyển sản phẩm sang ngừng kinh doanh và tự động tắt toàn bộ chi tiết sản phẩm con!',
            'status'  => $newStatus,
            'data'    => $product->fresh(['variants']),
        ]);
    }



    /**
     * Thêm 1 ảnh vào thư viện của sản phẩm.
     */
    public function addImage(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'image_url' => ['required', 'string'],
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
        }, 'product.category:id,name'])->whereHas('product');

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

        $trashed = Product::onlyTrashed()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total'    => $total,
                'active'   => $active,
                'inactive' => $inactive,
                'warning'  => $warning,
                'trashed'  => $trashed,
            ]
        ]);
    }

    /**
     * Thống kê KPI riêng cho sản phẩm con (biến thể).
     */
    public function variantsStats(): JsonResponse
    {
        $total = ProductVariant::whereHas('product')->count();
        $active = ProductVariant::whereHas('product')->where('status', 'ACTIVE')->count();
        $inactive = ProductVariant::whereHas('product')->where('status', 'INACTIVE')->count();
        $warning = ProductVariant::whereHas('product')->where('stock_quantity', '<=', 5)->count();

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
     * Chặn bật sản phẩm con nếu sản phẩm cha hiện đang tạm dừng kinh doanh.
     */
    public function toggleVariantStatus(ProductVariant $variant): JsonResponse
    {
        $newStatus = ($variant->status === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';

        if ($newStatus === 'ACTIVE' && $variant->product && $variant->product->status === 'INACTIVE') {
            return response()->json([
                'success' => false,
                'message' => 'Không thể bật phân loại này vì sản phẩm cha hiện đang tạm dừng kinh doanh. Vui lòng bật sản phẩm cha trước.',
            ], 422);
        }

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

    /**
     * Đảm bảo SKU của biến thể là duy nhất trong toàn hệ thống (kể cả các bản ghi đã xóa mềm).
     *
     * @param string|null $suggestedSku SKU gợi ý từ form
     * @param int $productId ID sản phẩm cha
     * @param int $vIndex Thứ tự biến thể trong mảng gửi lên
     * @param int|null $variantId ID biến thể đang cập nhật (nếu có)
     * @param array $usedInBatch Danh sách SKU đã dùng trong batch hiện tại
     * @return string
     */
    private function generateUniqueSku(?string $suggestedSku, int $productId, int $vIndex, ?int $variantId = null, array &$usedInBatch = []): string
    {
        $base = !empty($suggestedSku) ? trim($suggestedSku) : ('PRD' . $productId . '-V' . ($vIndex + 1));
        $candidate = $base;
        $counter = 1;

        while (
            in_array($candidate, $usedInBatch, true) ||
            ProductVariant::withTrashed()
                ->where('sku', $candidate)
                ->when($variantId, fn($q) => $q->where('id', '!=', $variantId))
                ->exists()
        ) {
            $candidate = $base . '-' . strtoupper(substr(uniqid(), -4));
            $counter++;
            if ($counter > 10) {
                $candidate = 'PRD' . $productId . '-V' . ($vIndex + 1) . '-' . time() . '-' . mt_rand(100, 999);
                break;
            }
        }

        $usedInBatch[] = $candidate;
        return $candidate;
    }
}

