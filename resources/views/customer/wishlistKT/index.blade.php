{{-- Giao diện: Trang danh sách sản phẩm yêu thích (WishlistKT) --}}
@extends('layouts.customer')

@section('title', 'Danh sách yêu thích - Mật Ngọt Bear')

@section('content')
@php
    $wishlistToastMessage = session('error') ?? session('success');
    $wishlistToastIsError = session()->has('error');
@endphp

<div class="py-8 bg-[#FAF6EE] min-h-[calc(100vh-140px)] pb-24 font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div
            class="wishlist-page"
            data-wishlist-root
            data-total-items="{{ $wishlist->total() }}"
        >
            {{-- [HERO BANNER] Tiêu đề và hình ảnh không gian yêu thích --}}
            <header
                class="wishlist-hero"
                style="--wishlist-hero-image: url('{{ asset('images/wishlist/banner-watercolor-small.png') }}')"
            >
                <div class="wishlist-hero-copy">
                    <span class="wishlist-kicker">
                        <span aria-hidden="true">♥</span>
                        Bộ sưu tập riêng của bạn
                    </span>
                    <h1>Những bé gấu bạn đã thương</h1>
                    <p>Lưu lại những sản phẩm khiến bạn mỉm cười và quay lại bất cứ khi nào bạn muốn.</p>
                </div>
            </header>

            {{-- [KHU VỰC SẢN PHẨM] Thanh công cụ sắp xếp, lưới thẻ sản phẩm và phân trang --}}
            <section class="wishlist-products" aria-labelledby="wishlist-products-title">
                <div class="wishlist-toolbar">
                    <div>
                        <h2 id="wishlist-products-title">Sản phẩm yêu thích</h2>
                        <p><strong data-wishlist-total>{{ $wishlist->total() }}</strong> sản phẩm trong bộ sưu tập</p>
                    </div>

                    {{-- Các nút thao tác: Chọn sắp xếp theo giá / mới nhất, nút Xóa tất cả --}}
                    <div class="wishlist-toolbar-actions">
                        @if ($wishlist->isNotEmpty())
                            <form method="GET" action="{{ route('customer.wishlist.index') }}" data-wishlist-sort-control>
                                <span class="wishlist-select-wrap">
                                    <select id="wishlist-sort" name="sort" aria-label="Sắp xếp sản phẩm" onchange="this.form.submit()">
                                        <option value="latest" @selected(request('sort', 'latest') === 'latest')>Mới lưu gần đây</option>
                                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá thấp đến cao</option>
                                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá cao đến thấp</option>
                                    </select>
                                    <svg viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="m5 7 5 5 5-5" />
                                    </svg>
                                </span>
                            </form>

                            <form method="POST" action="{{ route('customer.wishlist.clear') }}" data-wishlist-clear-control>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="wishlist-clear-button">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5" />
                                    </svg>
                                    Xóa tất cả
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- [LƯỚI SẢN PHẨM] Hiển thị các thẻ gấu bông đã yêu thích --}}
                <div
                    @class(['wishlist-grid', 'hidden' => $wishlist->isEmpty()])
                    data-wishlist-grid
                >
                    @foreach ($wishlist as $item)
                        @include('customer.wishlistKT.partials.product-card', ['item' => $item])
                    @endforeach
                </div>

                {{-- [TRẠNG THÁI TRỐNG] Hiển thị khi danh sách chưa có sản phẩm nào --}}
                <div @class(['hidden' => $wishlist->isNotEmpty()]) data-wishlist-empty>
                    @include('customer.wishlistKT.partials.empty-state')
                </div>

                {{-- Phân trang danh sách yêu thích --}}
                @if ($wishlist->hasPages())
                    <div class="wishlist-pagination" data-wishlist-pagination>
                        {{ $wishlist->withQueryString()->links() }}
                    </div>
                @endif
            </section>

            {{-- Thông báo Toast nổi phản hồi thao tác xóa/xóa tất cả --}}
            <div
                class="account-toast"
                data-wishlist-toast
                data-initial-message="{{ $wishlistToastMessage }}"
                data-initial-error="{{ $wishlistToastIsError ? 'true' : 'false' }}"
                role="status"
                aria-live="polite"
            >
                <span aria-hidden="true">✓</span>
                <p></p>
                <button type="button" data-wishlist-toast-close aria-label="Đóng thông báo">×</button>
            </div>
        </div>
    </div>
</div>
@endsection
