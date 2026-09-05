@extends('layouts.admin-dashboard')
@section('page-title', 'Quản Lý Đánh Giá')
@section('content')

@if (session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-green-600"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

<!-- 1. Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Tổng đánh giá</div>
            <div class="stat-value">{{ $stats['total'] ?? $reviews->total() }}</div>
            <div class="stat-subtext text-[#8E8076]">Từ khách hàng đã mua</div>
        </div>
        <div class="stat-icon brown"><i class="fa-solid fa-comments"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Điểm trung bình</div>
            <div class="stat-value text-amber-600 flex items-center gap-1">
                {{ $stats['avg_rating'] ?? 5.0 }} <span class="text-lg">★</span>
            </div>
            <div class="stat-subtext text-amber-700">Chất lượng sản phẩm</div>
        </div>
        <div class="stat-icon honey"><i class="fa-solid fa-star"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đang hiển thị</div>
            <div class="stat-value text-emerald-700">{{ $stats['visible'] ?? 0 }}</div>
            <div class="stat-subtext text-emerald-600">Công khai trên web</div>
        </div>
        <div class="stat-icon green"><i class="fa-solid fa-eye"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đang ẩn</div>
            <div class="stat-value text-rose-700">{{ $stats['hidden'] ?? 0 }}</div>
            <div class="stat-subtext text-rose-600">Đã tạm khóa hiển thị</div>
        </div>
        <div class="stat-icon red"><i class="fa-solid fa-eye-slash"></i></div>
    </div>
</div>

<!-- 2. Main Panel -->
<div class="panel-card">
    <div class="panel-header">
        <div>
            <div class="panel-title">
                <i class="fa-solid fa-star-half-stroke"></i>
                Danh Sách Đánh Giá & Phản Hồi
            </div>
            <div class="panel-subtitle">Kiểm duyệt nhận xét, số sao và phản hồi từ người mua hàng</div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="nav-pills">
        <a href="{{ route('admin.reviews.index', request()->except('is_hidden', 'page')) }}"
           class="nav-pill {{ !request()->has('is_hidden') ? 'active' : '' }}">
            <span>Tất cả</span>
            <span class="nav-pill-count">{{ $stats['total'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.reviews.index', array_merge(request()->except('page'), ['is_hidden' => '0'])) }}"
           class="nav-pill {{ request('is_hidden') === '0' ? 'active' : '' }}">
            <span><i class="fa-solid fa-eye text-xs"></i> Đang hiển thị</span>
            <span class="nav-pill-count">{{ $stats['visible'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.reviews.index', array_merge(request()->except('page'), ['is_hidden' => '1'])) }}"
           class="nav-pill {{ request('is_hidden') === '1' ? 'active' : '' }}">
            <span><i class="fa-solid fa-eye-slash text-xs"></i> Đã ẩn</span>
            <span class="nav-pill-count">{{ $stats['hidden'] ?? 0 }}</span>
        </a>
    </div>

    <!-- Toolbar Filters -->
    <form method="GET" action="{{ route('admin.reviews.index') }}" class="toolbar-grid">
        @if (request()->has('is_hidden'))
            <input type="hidden" name="is_hidden" value="{{ request('is_hidden') }}">
        @endif

        <div style="min-width: 180px;">
            <select name="rating" class="select-control">
                <option value="">-- Số sao: Tất cả --</option>
                @foreach ([5 => '★★★★★ (5 sao)', 4 => '★★★★☆ (4 sao)', 3 => '★★★☆☆ (3 sao)', 2 => '★★☆☆☆ (2 sao)', 1 => '★☆☆☆☆ (1 sao)'] as $ratingVal => $ratingLabel)
                    <option value="{{ $ratingVal }}" @selected(request('rating') == $ratingVal)>{{ $ratingLabel }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-filter text-xs"></i> Lọc
            </button>
            @if (request()->hasAny(['is_hidden', 'rating']))
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline" title="Xóa bộ lọc">
                    <i class="fa-solid fa-rotate-left text-xs"></i> Đặt lại
                </a>
            @endif
        </div>
    </form>

    <!-- Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Sản Phẩm</th>
                    <th>Khách Hàng</th>
                    <th>Đánh Giá</th>
                    <th>Nội Dung Nhận Xét</th>
                    <th>Đơn Hàng</th>
                    <th>Trạng Thái</th>
                    <th>Thời Gian</th>
                    <th class="text-right">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td>
                            <div class="font-bold text-[#4E342E] max-w-[200px] line-clamp-2">
                                {{ $review->product?->name ?? '—' }}
                            </div>
                            @if ($review->product)
                                <a href="{{ route('admin.products.index', ['search' => $review->product->name]) }}" class="text-[11px] text-[#8E8076] hover:text-[#B87309] hover:underline">
                                    Mã SP: #{{ $review->product_id }}
                                </a>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="avatar-circle" style="width: 32px; height: 32px; font-size: 11px;">
                                    {{ mb_strtoupper(mb_substr($review->user?->full_name ?? 'K', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-[#4E342E] text-xs">{{ $review->user?->full_name ?? '—' }}</div>
                                    <div class="text-[11px] text-[#8E8076]">{{ $review->user?->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-1 text-amber-500 font-bold text-sm">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $review->rating)
                                        <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                                    @else
                                        <i class="fa-regular fa-star text-gray-300 text-xs"></i>
                                    @endif
                                @endfor
                                <span class="text-xs text-[#795548] ml-1 font-extrabold">({{ $review->rating }}/5)</span>
                            </div>
                        </td>
                        <td>
                            @if ($review->comment)
                                <div class="p-2.5 bg-amber-50/60 border border-amber-100/80 rounded-xl text-xs text-[#4E342E] max-w-sm">
                                    {{ $review->comment }}
                                </div>
                            @else
                                <span class="text-xs text-[#8E8076] italic">Không có nhận xét chữ</span>
                            @endif
                        </td>
                        <td>
                            @if ($review->order)
                                <a href="{{ route('admin.orders.show', $review->order) }}" class="font-bold text-xs text-[#4E342E] hover:text-amber-700 hover:underline">
                                    #{{ $review->order->order_code }}
                                </a>
                            @else
                                <span class="text-xs text-[#8E8076]">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($review->is_hidden)
                                <span class="badge-pastel red">
                                    <i class="fa-solid fa-eye-slash text-[8px]"></i> Đã ẩn
                                </span>
                            @else
                                <span class="badge-pastel green">
                                    <i class="fa-solid fa-eye text-[8px]"></i> Hiển thị
                                </span>
                            @endif
                        </td>
                        <td class="text-xs text-[#8E8076] whitespace-nowrap">
                            {{ $review->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="btn btn-sm {{ $review->is_hidden ? 'btn-success' : 'btn-danger' }}"
                                        title="{{ $review->is_hidden ? 'Hiển thị công khai' : 'Tạm ẩn đánh giá' }}">
                                    <i class="fa-solid {{ $review->is_hidden ? 'fa-eye' : 'fa-eye-slash' }} text-xs"></i>
                                    {{ $review->is_hidden ? 'Hiện lại' : 'Ẩn đi' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-10 text-center text-[#8E8076]">
                            <i class="fa-solid fa-star-half-stroke text-3xl text-amber-300 mb-2 block"></i>
                            Không có đánh giá nào phù hợp với bộ lọc.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($reviews->hasPages())
        <div class="mt-4">
            {{ $reviews->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection