<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VoucherController extends Controller
{
    /**
     * Display a listing of vouchers with filters and statistics.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $now = Carbon::now();

        // Luôn chỉ hiển thị voucher chưa bị xóa mềm (SoftDeletes)
        $query = Voucher::with(['categories', 'products']);

        $query->withCount([
            'orders',
            'shippingOrders',
            'orders as active_orders_count' => fn($q) => $q->whereNotIn('order_status', ['COMPLETED', 'CANCELLED']),
            'shippingOrders as active_shipping_orders_count' => fn($q) => $q->whereNotIn('order_status', ['COMPLETED', 'CANCELLED']),
        ]);

        // Search by voucher code
        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . trim($request->input('search')) . '%');
        }

        // Filter by real status
        if ($request->filled('status')) {
            if ($status === 'RUNNING') {
                $query->where('status', 'ACTIVE')
                      ->where('start_date', '<=', $now)
                      ->where('end_date', '>=', $now)
                      ->whereColumn('used_count', '<', 'usage_limit');
            } elseif ($status === 'UPCOMING') {
                $query->where('status', 'ACTIVE')
                      ->where('start_date', '>', $now);
            } elseif ($status === 'EXPIRED') {
                $query->where('end_date', '<', $now);
            } elseif ($status === 'OUT_OF_STOCK') {
                $query->whereColumn('used_count', '>=', 'usage_limit');
            } elseif ($status === 'INACTIVE') {
                $query->where('status', 'INACTIVE');
            } elseif ($status === 'ACTIVE') {
                $query->where('status', 'ACTIVE');
            } elseif ($status === 'TRASHED') {
                $query->onlyTrashed();
            }
        }

        // Filter by voucher type (ORDER, SHIPPING)
        if ($request->filled('voucher_type')) {
            $query->where('voucher_type', $request->input('voucher_type'));
        }

        // Filter by discount type
        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->input('discount_type'));
        }

        // Statistics
        $stats = [
            'total' => Voucher::count(),
            'running' => Voucher::where('status', 'ACTIVE')
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->whereColumn('used_count', '<', 'usage_limit')
                ->count(),
            'upcoming' => Voucher::where('status', 'ACTIVE')
                ->where('start_date', '>', $now)
                ->count(),
            'expired' => Voucher::where('end_date', '<', $now)
                ->orWhereColumn('used_count', '>=', 'usage_limit')
                ->count(),
            'inactive' => Voucher::where('status', 'INACTIVE')->count(),
            'order_vouchers' => Voucher::where('voucher_type', 'ORDER')->count(),
            'shipping_vouchers' => Voucher::where('voucher_type', 'SHIPPING')->count(),
        ];

        $vouchers = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.vouchers.index', compact('vouchers', 'stats'));
    }

    /**
     * Show the form for creating a new voucher.
     */
    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('status', 'ACTIVE')
            ->with(['category', 'variants', 'images' => fn($q) => $q->orderBy('is_primary', 'desc')])
            ->orderBy('name')
            ->get();

        return view('admin.vouchers.create', compact('categories', 'products'));
    }

    /**
     * Store a newly created voucher in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Sanitize currency inputs (strip dots / commas from formatting)
        $cleanInputs = [];
        foreach (['discount_value', 'min_order_value', 'max_discount_value'] as $field) {
            if ($request->has($field) && is_string($request->$field)) {
                $cleanInputs[$field] = str_replace(['.', ','], '', $request->$field);
            }
        }
        if (!empty($cleanInputs)) {
            $request->merge($cleanInputs);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('vouchers', 'code')->whereNull('deleted_at'), 'regex:/^[A-Z0-9_\-]+$/i'],
            'voucher_type' => 'required|in:ORDER,SHIPPING',
            'apply_scope' => 'required|in:ALL,CATEGORY,PRODUCT',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'variant_ids' => 'nullable|array',
            'variant_ids.*' => 'exists:product_variants,id',
            'discount_type' => 'required|in:PERCENTAGE,FIXED',
            'discount_value' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->discount_type === 'PERCENTAGE' && $value > 100) {
                        $fail('Mức giảm theo phần trăm không được vượt quá 100%.');
                    }
                },
            ],
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_value' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => 'required|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1|lte:usage_limit',
            'status' => 'nullable|in:ACTIVE,INACTIVE',
        ], [
            'code.required' => 'Vui lòng nhập mã voucher.',
            'code.unique' => 'Mã voucher này đã tồn tại trong hệ thống.',
            'code.regex' => 'Mã voucher chỉ được chứa chữ cái, chữ số, gạch nối (-) hoặc gạch dưới (_).',
            'voucher_type.required' => 'Vui lòng chọn loại voucher (Mã đơn hàng hoặc Mã vận chuyển).',
            'apply_scope.required' => 'Vui lòng chọn phạm vi áp dụng.',
            'discount_value.required' => 'Vui lòng nhập giá trị giảm.',
            'discount_value.min' => 'Giá trị giảm phải lớn hơn 0.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.after' => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu voucher.',
            'usage_limit.required' => 'Vui lòng nhập số lượt sử dụng tối đa.',
            'usage_limit.min' => 'Số lượt sử dụng tối đa phải từ 1 trở lên.',
            'usage_limit_per_user.min' => 'Số lượt sử dụng cho mỗi khách hàng phải từ 1 trở lên.',
            'usage_limit_per_user.lte' => 'Số lượt dùng của mỗi khách hàng không được vượt quá tổng số lượt dùng của voucher (:value lượt).',
        ]);

        // Custom validation for scope
        if ($validated['apply_scope'] === 'CATEGORY' && empty($request->input('category_ids'))) {
            return back()->withInput()->withErrors(['category_ids' => 'Vui lòng chọn ít nhất 1 danh mục áp dụng.']);
        }

        if ($validated['apply_scope'] === 'PRODUCT' && empty($request->input('product_ids')) && empty($request->input('variant_ids'))) {
            return back()->withInput()->withErrors(['product_ids' => 'Vui lòng chọn ít nhất 1 sản phẩm áp dụng.']);
        }

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['min_order_value'] = $validated['min_order_value'] ?? 0;
        $validated['status'] = $validated['status'] ?? 'ACTIVE';
        $validated['usage_limit_per_user'] = !empty($validated['usage_limit_per_user']) ? (int) $validated['usage_limit_per_user'] : null;
        
        // If FIXED, clear max_discount_value
        if ($validated['discount_type'] === 'FIXED') {
            $validated['max_discount_value'] = null;
        }

        $voucher = Voucher::create($validated);

        // Sync relationships
        if ($validated['apply_scope'] === 'CATEGORY') {
            $voucher->categories()->sync($request->input('category_ids', []));
        } elseif ($validated['apply_scope'] === 'PRODUCT') {
            $productIds = $request->input('product_ids', []);
            $variantIds = $request->input('variant_ids', []);
            if (!empty($variantIds)) {
                $variantProductIds = \App\Models\ProductVariant::whereIn('id', $variantIds)->pluck('product_id')->toArray();
                $productIds = array_unique(array_merge($productIds, $variantProductIds));
            }
            $voucher->products()->sync($productIds);
            $voucher->productVariants()->sync($variantIds);
        }

        return redirect()->route('admin.vouchers.index')->with('success', "Đã tạo voucher [{$validated['code']}] thành công!");
    }

    /**
     * Show the form for editing the specified voucher.
     */
    public function edit(Voucher $voucher): View
    {
        $voucher->load(['categories', 'products', 'productVariants']);
        $categories = Category::where('is_active', true)->withCount('products')->orderBy('name')->get();
        $products = Product::where('status', 'ACTIVE')
            ->with(['category', 'variants', 'images' => fn($q) => $q->orderBy('is_primary', 'desc')])
            ->orderBy('name')
            ->get();

        return view('admin.vouchers.edit', compact('voucher', 'categories', 'products'));
    }

    /**
     * Update the specified voucher in storage.
     */
    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        // Sanitize currency inputs (strip dots / commas from formatting)
        $cleanInputs = [];
        foreach (['discount_value', 'min_order_value', 'max_discount_value'] as $field) {
            if ($request->has($field) && is_string($request->$field)) {
                $cleanInputs[$field] = str_replace(['.', ','], '', $request->$field);
            }
        }
        if (!empty($cleanInputs)) {
            $request->merge($cleanInputs);
        }

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_\-]+$/i',
                Rule::unique('vouchers', 'code')->ignore($voucher->id)->whereNull('deleted_at'),
            ],
            'voucher_type' => 'required|in:ORDER,SHIPPING',
            'apply_scope' => 'required|in:ALL,CATEGORY,PRODUCT',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'variant_ids' => 'nullable|array',
            'variant_ids.*' => 'exists:product_variants,id',
            'discount_type' => 'required|in:PERCENTAGE,FIXED',
            'discount_value' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->discount_type === 'PERCENTAGE' && $value > 100) {
                        $fail('Mức giảm theo phần trăm không được vượt quá 100%.');
                    }
                },
            ],
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_value' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => "required|integer|min:{$voucher->used_count}",
            'usage_limit_per_user' => 'nullable|integer|min:1|lte:usage_limit',
            'status' => 'nullable|in:ACTIVE,INACTIVE',
        ], [
            'code.required' => 'Vui lòng nhập mã voucher.',
            'code.unique' => 'Mã voucher này đã tồn tại trong hệ thống.',
            'code.regex' => 'Mã voucher chỉ được chứa chữ cái, chữ số, gạch nối (-) hoặc gạch dưới (_).',
            'discount_value.required' => 'Vui lòng nhập giá trị giảm.',
            'discount_value.min' => 'Giá trị giảm phải lớn hơn 0.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'end_date.after' => 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu voucher.',
            'usage_limit.min' => "Số lượt sử dụng tối đa không thể nhỏ hơn số lượt đã dùng ({$voucher->used_count} lượt).",
            'usage_limit_per_user.min' => 'Số lượt sử dụng cho mỗi khách hàng phải từ 1 trở lên.',
            'usage_limit_per_user.lte' => 'Số lượt dùng của mỗi khách hàng không được vượt quá tổng số lượt dùng của voucher (:value lượt).',
        ]);

        // Custom validation for scope
        if ($validated['apply_scope'] === 'CATEGORY' && empty($request->input('category_ids'))) {
            return back()->withInput()->withErrors(['category_ids' => 'Vui lòng chọn ít nhất 1 danh mục áp dụng.']);
        }

        if ($validated['apply_scope'] === 'PRODUCT' && empty($request->input('product_ids')) && empty($request->input('variant_ids'))) {
            return back()->withInput()->withErrors(['product_ids' => 'Vui lòng chọn ít nhất 1 sản phẩm áp dụng.']);
        }

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['min_order_value'] = $validated['min_order_value'] ?? 0;
        $validated['status'] = $validated['status'] ?? $voucher->status;
        $validated['usage_limit_per_user'] = !empty($validated['usage_limit_per_user']) ? (int) $validated['usage_limit_per_user'] : null;

        if ($validated['discount_type'] === 'FIXED') {
            $validated['max_discount_value'] = null;
        }

        $voucher->update($validated);

        // Sync relationships
        if ($validated['apply_scope'] === 'CATEGORY') {
            $voucher->categories()->sync($request->input('category_ids', []));
            $voucher->products()->detach();
            $voucher->productVariants()->detach();
        } elseif ($validated['apply_scope'] === 'PRODUCT') {
            $productIds = $request->input('product_ids', []);
            $variantIds = $request->input('variant_ids', []);
            if (!empty($variantIds)) {
                $variantProductIds = \App\Models\ProductVariant::whereIn('id', $variantIds)->pluck('product_id')->toArray();
                $productIds = array_unique(array_merge($productIds, $variantProductIds));
            }
            $voucher->products()->sync($productIds);
            $voucher->productVariants()->sync($variantIds);
            $voucher->categories()->detach();
        } else {
            $voucher->categories()->detach();
            $voucher->products()->detach();
            $voucher->productVariants()->detach();
        }

        return redirect()->route('admin.vouchers.index')->with('success', "Đã cập nhật voucher [{$voucher->code}] thành công!");
    }

    /**
     * Toggle status (ACTIVE <-> INACTIVE) of the voucher.
     */
    public function toggle(Request $request, Voucher $voucher): RedirectResponse|JsonResponse
    {
        $newStatus = $voucher->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $voucher->update(['status' => $newStatus]);

        $statusText = $newStatus === 'ACTIVE' ? 'kích hoạt sang Đang áp dụng' : 'chuyển sang Vô hiệu hóa';

        if ($request->wantsJson()) {
            $now = Carbon::now();
            $isExpired = $voucher->end_date && $voucher->end_date->isPast();
            $isOutOfStock = $voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit;
            $isUpcoming = $voucher->start_date && $voucher->start_date->isFuture();
            $isRunning = $newStatus === 'ACTIVE' && !$isExpired && !$isOutOfStock && !$isUpcoming;

            $stats = [
                'total' => Voucher::count(),
                'running' => Voucher::where('status', 'ACTIVE')
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now)
                    ->whereColumn('used_count', '<', 'usage_limit')
                    ->count(),
                'upcoming' => Voucher::where('status', 'ACTIVE')
                    ->where('start_date', '>', $now)
                    ->count(),
                'expired' => Voucher::where('end_date', '<', $now)
                    ->orWhereColumn('used_count', '>=', 'usage_limit')
                    ->count(),
                'inactive' => Voucher::where('status', 'INACTIVE')->count(),
            ];

            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'is_expired' => $isExpired,
                'is_out_of_stock' => $isOutOfStock,
                'is_upcoming' => $isUpcoming,
                'is_running' => $isRunning,
                'used_count' => (int) $voucher->used_count,
                'code' => $voucher->code,
                'message' => "Đã {$statusText} voucher [{$voucher->code}] thành công!",
                'stats' => $stats,
            ]);
        }

        return back()->with('success', "Đã {$statusText} voucher [{$voucher->code}] thành công!");
    }

    /**
     * Xóa voucher: Tất cả đều là XÓA MỀM (Soft Delete), không xóa vĩnh viễn
     * để bảo toàn lịch sử đơn hàng và đối soát tài chính của người dùng.
     */
    public function destroy(Voucher $voucher): RedirectResponse
    {
        $now = Carbon::now();
        $isExpired = $voucher->end_date && $voucher->end_date->isPast();
        $isOutOfStock = $voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit;
        $isUpcoming = $voucher->start_date && $voucher->start_date->isFuture();
        $isRunning = $voucher->status === 'ACTIVE' && !$isExpired && !$isOutOfStock && !$isUpcoming;

        // Nếu voucher đang diễn ra VÀ đã có lượt dùng -> Yêu cầu chuyển sang vô hiệu hóa trước để tránh ảnh hưởng khách hàng
        if ($isRunning && $voucher->used_count > 0) {
            return back()->with('error', "Không thể xóa voucher [{$voucher->code}] khi đang diễn ra và đã có lượt sử dụng ({$voucher->used_count} lượt). Vui lòng chuyển trạng thái voucher sang 'Vô hiệu hóa' trước khi xóa!");
        }

        $activeOrdersCount = $voucher->getActiveOrdersCount();

        // Nếu đang có đơn hàng chưa hoàn tất -> Chặn không cho xóa
        if ($activeOrdersCount > 0) {
            return back()->with('error', "Không thể xóa voucher [{$voucher->code}] vì đang có {$activeOrdersCount} đơn hàng chưa hoàn tất đang áp dụng mã này!");
        }

        // Xóa mềm voucher (cột deleted_at trong CSDL) để bảo toàn dữ liệu lịch sử đơn hàng của khách hàng
        $code = $voucher->code;
        $voucher->delete();

        return redirect()->route('admin.vouchers.index')->with('success', "Đã xóa voucher [{$code}] thành công (xóa mềm để bảo toàn lịch sử dữ liệu đơn hàng)!");
    }

    /**
     * Gia hạn thêm thời gian sử dụng hoặc lượt dùng cho voucher.
     */
    public function restore(Request $request, int $id): RedirectResponse
    {
        $voucher = Voucher::withTrashed()->findOrFail($id);

        $rules = [
            'end_date' => 'nullable|date',
            'usage_limit' => "nullable|integer|min:{$voucher->used_count}",
        ];

        if ($request->filled('end_date')) {
            $rules['end_date'] .= '|after:now';
            if ($voucher->start_date && $voucher->start_date->isFuture()) {
                $rules['end_date'] .= '|after:' . $voucher->start_date->toDateTimeString();
            }
        }

        $validated = $request->validate($rules, [
            'end_date.after' => 'Thời gian kết thúc gia hạn phải ở tương lai (sau thời điểm hiện tại). Không được chọn thời gian trong quá khứ hoặc hiện tại.',
            'usage_limit.min' => "Tổng số lượt dùng mới không thể nhỏ hơn số lượt đã sử dụng ({$voucher->used_count} lượt).",
        ]);

        $updates = [];

        if ($request->filled('end_date')) {
            $updates['end_date'] = Carbon::parse($validated['end_date']);
        }

        if ($request->filled('usage_limit')) {
            $updates['usage_limit'] = (int) $validated['usage_limit'];
        }

        // Tự động kích hoạt lại trạng thái ACTIVE nếu đang INACTIVE khi gia hạn
        if ($voucher->status === 'INACTIVE' && (!empty($updates['end_date']) || !empty($updates['usage_limit']))) {
            $updates['status'] = 'ACTIVE';
        }

        if (!empty($updates)) {
            $voucher->update($updates);
        }

        $code = $voucher->code;
        if ($voucher->trashed()) {
            $voucher->restore();
        }

        $details = [];
        if (!empty($updates['end_date'])) {
            $details[] = "Gia hạn đến: " . $updates['end_date']->format('d/m/Y H:i');
        }
        if (!empty($updates['usage_limit'])) {
            $details[] = "Lượt dùng mới: {$updates['usage_limit']}";
        }

        $msg = "Đã gia hạn voucher [{$code}] thành công!";
        if (!empty($details)) {
            $msg = "Đã gia hạn voucher [{$code}] thành công (" . implode(', ', $details) . ")!";
        }

        return back()->with('success', $msg);
    }

    /**
     * Permanently remove the specified voucher from storage.
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $voucher = Voucher::withTrashed()->findOrFail($id);
        $totalOrdersCount = $voucher->getTotalOrdersCount();

        if ($totalOrdersCount > 0) {
            return back()->with('error', "Không thể xóa vĩnh viễn voucher [{$voucher->code}] vì đã có {$totalOrdersCount} đơn hàng từng áp dụng mã này trong hệ thống. Chỉ voucher chưa từng có đơn hàng nào sử dụng mới được phép xóa vĩnh viễn!");
        }

        $code = $voucher->code;

        // Detach pivot relations
        $voucher->categories()->detach();
        $voucher->products()->detach();
        $voucher->productVariants()->detach();
        $voucher->forceDelete();

        return back()->with('success', "Đã xóa vĩnh viễn voucher [{$code}] khỏi hệ thống!");
    }
}

