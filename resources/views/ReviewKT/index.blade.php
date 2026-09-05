<x-customer-account-layout title="Đánh giá của tôi" :flush="true">
    <div class="my-reviews-page">
        {{-- HERO BANNER --}}
        <header class="my-reviews-hero">
            <div class="my-reviews-hero__copy">
                <span class="my-reviews-hero__kicker">
                    <span aria-hidden="true">⭐</span>
                    Trải nghiệm khách hàng
                </span>
                <h1 class="my-reviews-hero__title">Đánh giá của tôi</h1>
                <p class="my-reviews-hero__desc">
                    Xem lại những cảm nhận và đánh giá sản phẩm của bạn. Mỗi đánh giá chỉ được chỉnh sửa 1 lần duy nhất theo chuẩn Shopee.
                </p>
            </div>

            {{-- ĐI ĐẾN ĐƠN HÀNG ĐỂ ĐÁNH GIÁ (CHUẨN SHOPEE) --}}
            <div class="my-reviews-hero__action">
                <a
                    href="{{ route('customer.orders.index', ['order_status' => 'COMPLETED']) }}"
                    class="my-reviews-demo-btn"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                        <path d="M3 6h18" />
                        <path d="M16 10a4 4 0 0 1-8 0" />
                    </svg>
                    <span>Đánh giá đơn hàng đã mua</span>
                </a>
            </div>
        </header>

        {{-- DANH SÁCH ĐÁNH GIÁ --}}
        <section class="my-reviews-content">
            <div class="my-reviews-toolbar">
                <h2 class="my-reviews-toolbar__title">
                    Lịch sử đánh giá ({{ $reviews->total() }})
                </h2>
                <span class="my-reviews-toolbar__hint">
                    🐻 Mỗi sản phẩm trong đơn hoàn thành chỉ được sửa 1 lần
                </span>
            </div>

            @if ($reviews->isEmpty())
                <div class="my-reviews-empty">
                    <div class="my-reviews-empty__icon">🐻✨</div>
                    <h3>Bạn chưa có đánh giá nào</h3>
                    <p>Khi bạn mua hàng và đơn hàng được giao thành công, bạn có thể bấm "Đánh giá" trực tiếp tại đơn hàng đó!</p>
                    <a
                        href="{{ route('customer.orders.index', ['order_status' => 'COMPLETED']) }}"
                        class="my-reviews-demo-btn my-reviews-empty__btn"
                    >
                        Xem đơn hàng hoàn thành để đánh giá →
                    </a>
                </div>
            @else
                <div class="my-reviews-list">
                    @foreach ($reviews as $review)
                        @php
                            $prod = $review->product;
                            $img = $prod?->images?->firstWhere('is_primary', true)?->image_url
                                ?? $prod?->images?->first()?->image_url
                                ?? asset('images/customer/product-placeholder.png');
                        @endphp
                        <article class="my-review-card">
                            <div class="my-review-card__main">
                                <div class="my-review-card__image-wrap">
                                    <img src="{{ $img }}" alt="{{ $prod->name ?? 'Sản phẩm' }}" class="my-review-card__img">
                                </div>
                                <div class="my-review-card__body">
                                    <div class="my-review-card__header">
                                        <h4 class="my-review-card__prod-name">{{ $prod->name ?? 'Sản phẩm đã gỡ' }}</h4>
                                        <time class="my-review-card__date" datetime="{{ $review->created_at->toIso8601String() }}">
                                            {{ $review->created_at->format('d/m/Y H:i') }}
                                        </time>
                                    </div>

                                    @if ($review->order)
                                        <div class="my-review-card__order-meta">
                                            Mã đơn hàng: <strong>#{{ $review->order->order_code }}</strong>
                                        </div>
                                    @endif

                                    <div class="my-review-card__stars" aria-label="{{ $review->rating }} sao">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg
                                                class="my-review-card__star {{ $i <= $review->rating ? 'is-filled' : 'is-empty' }}"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                                aria-hidden="true"
                                            >
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                            </svg>
                                        @endfor
                                        <span class="my-review-card__rating-num">{{ $review->rating }}/5 sao</span>
                                    </div>

                                    <p class="my-review-card__comment">{{ $review->comment }}</p>

                                    <div class="my-review-card__footer">
                                        @if ($review->is_edited)
                                            <span class="my-review-card__badge-edited" title="Mỗi đánh giá chỉ được chỉnh sửa tối đa 1 lần">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" aria-hidden="true">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <path d="m9 12 2 2 4-4"/>
                                                </svg>
                                                Đã chỉnh sửa (1/1 lần)
                                            </span>
                                        @else
                                            <button
                                                type="button"
                                                class="my-review-card__edit-btn"
                                                data-open-review-modal
                                                data-review-id="{{ $review->id }}"
                                                data-product-id="{{ $review->product_id }}"
                                                data-order-id="{{ $review->order_id }}"
                                                data-order-code="{{ $review->order?->order_code ?? '' }}"
                                                data-product-name="{{ $prod->name ?? 'Sản phẩm' }}"
                                                data-product-image="{{ $img }}"
                                                data-rating="{{ $review->rating }}"
                                                data-comment="{{ $review->comment }}"
                                                data-is-edited="false"
                                            >
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" aria-hidden="true">
                                                    <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                                </svg>
                                                Chỉnh sửa đánh giá (còn 1 lần)
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="my-reviews-pagination">
                    {{ $reviews->links() }}
                </div>
            @endif
        </section>
    </div>
</x-customer-account-layout>
