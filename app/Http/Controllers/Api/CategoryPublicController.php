<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryPublicController extends Controller
{
    /**
     * Lấy danh sách danh mục đang kích hoạt (is_active = true)
     * kèm số lượng sản phẩm đang mở bán (status = 'ACTIVE').
     */
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => function ($query) {
                $query->where('status', 'ACTIVE');
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }
}
