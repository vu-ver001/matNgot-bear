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
