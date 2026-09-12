<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Danh sách tất cả danh mục (hỗ trợ phân trang hoặc lấy tất cả).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount('products');

        // Tìm kiếm theo tên
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        // Lọc theo trạng thái status (Khắc phục lỗi SQL column not found)
        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'ACTIVE';
            $query->where('is_active', $isActive);
        }

        $categories = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách danh mục thành công.',
            'data'    => $categories,
        ]);
    }

    /**
     * Ghim / Bỏ ghim danh mục lên Header (Tối đa 5 danh mục).
     */
    public function togglePin(Category $category): JsonResponse
    {
        if (!$category->is_pinned) {
            // Ràng buộc: Trạng thái Tạm ẩn thì không cho phép ghim lên Header
            if (!$category->is_active) {
                return response()->json([
                    'success'     => false,
                    'is_inactive' => true,
                    'message'     => "Danh mục \"{$category->name}\" hiện đang ở trạng thái Tạm ẩn nên không được phép ghim lên thanh Header. Vui lòng chuyển sang Kích hoạt trước khi ghim!",
                ], 422);
            }

            // Kiểm tra nếu Header đã đủ 5 danh mục
            $pinnedCount = Category::where('is_pinned', true)->count();
            if ($pinnedCount >= 5) {
                return response()->json([
                    'success' => false,
                    'message' => "Nếu bạn muốn thêm danh mục \"{$category->name}\" vào header thì hãy vui lòng xóa danh mục khác để thay thế",
                    'note'    => 'Tối đa để 5 danh mục ở header',
                ], 422);
            }

            $category->update(['is_pinned' => true]);

            return response()->json([
                'success' => true,
                'message' => "Đã ghim danh mục \"{$category->name}\" lên header thành công.",
                'data'    => $category,
            ]);
        }

        $category->update(['is_pinned' => false]);

        return response()->json([
            'success' => true,
            'message' => "Đã bỏ ghim danh mục \"{$category->name}\" khỏi header.",
            'data'    => $category,
        ]);
    }

    /**
     * Lấy cấu hình menu Header hiện tại và danh sách sản phẩm thuộc danh mục.
     */
    public function getHeaderMenu(Category $category): JsonResponse
    {
        $products = $category->products()
            ->where('status', 'ACTIVE')
            ->select('id', 'name', 'price', 'sale_price')
            ->orderBy('sold_count', 'desc')
            ->get();

        return response()->json([
            'success'  => true,
            'category' => [
                'id'                 => $category->id,
                'name'               => $category->name,
                'header_menu_config' => $category->header_menu_config ?? [],
            ],
            'products' => $products,
        ]);
    }

    /**
     * Lưu cấu hình menu Header (Tối đa 4 cột to, mỗi cột tối đa 3 sản phẩm).
     */
    public function saveHeaderMenu(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'columns'                       => ['nullable', 'array', 'max:4'],
            'columns.*.title'               => ['required', 'string', 'max:100'],
            'columns.*.items'               => ['nullable', 'array', 'max:3'],
            'columns.*.items.*.product_id'  => ['nullable', 'integer'],
            'columns.*.items.*.custom_name' => ['nullable', 'string', 'max:150'],
        ], [
            'columns.max'              => 'Giới hạn tối đa là 4 cột menu to trên Header.',
            'columns.*.title.required' => 'Vui lòng nhập tên cho từng cột menu to.',
            'columns.*.items.max'      => 'Mỗi cột menu to chỉ được chứa tối đa 3 sản phẩm.',
        ]);

        $columns = $validated['columns'] ?? [];

        // Làm sạch và đồng bộ tên sản phẩm nếu admin chọn từ product_id
        $sanitizedColumns = [];
        foreach (array_slice($columns, 0, 4) as $col) {
            $colTitle = trim($col['title'] ?? '');
            if (empty($colTitle)) continue;

            $items = [];
            foreach (array_slice($col['items'] ?? [], 0, 3) as $item) {
                $pId = !empty($item['product_id']) ? (int) $item['product_id'] : null;
                $customName = trim($item['custom_name'] ?? '');

                if ($pId) {
                    $prod = \App\Models\Product::find($pId);
                    if ($prod) {
                        $items[] = [
                            'product_id' => $prod->id,
                            'name'       => !empty($customName) ? $customName : $prod->name,
                            'url'        => route('products.show', $prod->id),
                        ];
                    }
                } elseif (!empty($customName)) {
                    $items[] = [
                        'product_id' => null,
                        'name'       => $customName,
                        'url'        => route('products.index', ['category_id' => $category->id, 'sort' => 'best_seller']) . '#catalog-layout',
                    ];
                }
            }

            $sanitizedColumns[] = [
                'title' => $colTitle,
                'items' => $items,
            ];
        }

        $category->update([
            'header_menu_config' => $sanitizedColumns,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Đã lưu cấu hình Menu Header cho danh mục \"{$category->name}\" thành công.",
            'data'    => $sanitizedColumns,
        ]);
    }

    /**
     * Tạo danh mục mới.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($request->has('status')) {
            $data['is_active'] = $request->input('status') === 'ACTIVE';
        }

        $category = Category::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Tạo danh mục thành công.',
            'data'    => $category,
        ], 201);
    }

    /**
     * Xem chi tiết danh mục.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết danh mục thành công.',
            'data'    => $category,
        ]);
    }

    /**
     * Cập nhật danh mục.
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $data = $request->validated();
        if ($request->has('status')) {
            $data['is_active'] = $request->input('status') === 'ACTIVE';
        }
        if (isset($data['is_active']) && !$data['is_active']) {
            $data['is_pinned'] = false;
        }

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật danh mục thành công.',
            'data'    => $category,
        ]);
    }

    /**
     * Xóa danh mục (kiểm tra nếu có sản phẩm con).
     */
    public function destroy(Category $category): JsonResponse
    {
        // Kiểm tra nếu danh mục đang có sản phẩm
        if ($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa danh mục đang có sản phẩm!',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa danh mục thành công.',
        ]);
    }
}
