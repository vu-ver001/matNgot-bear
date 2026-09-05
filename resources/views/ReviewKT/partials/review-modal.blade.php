<div
    id="review-modal"
    class="review-modal"
    data-review-modal
    role="dialog"
    aria-modal="true"
    aria-labelledby="review-modal-title"
    hidden
>
    <div class="review-modal__backdrop" data-review-close tabindex="-1"></div>

    <div class="review-modal__dialog" role="document">
        <header class="review-modal__header">
            <div class="review-modal__header-copy">
                <h2 id="review-modal-title" class="review-modal__title" data-review-modal-title>Viết đánh giá</h2>
                <p class="review-modal__subtitle" data-review-modal-subtitle>Chia sẻ cảm nhận của bạn về sản phẩm này</p>
            </div>
            <button
                type="button"
                class="review-modal__close-btn"
                data-review-close
                aria-label="Đóng popup đánh giá"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </header>

        <form
            id="review-form"
            class="review-modal__form"
            action="{{ route('customer.reviews.store') }}"
            method="POST"
            enctype="multipart/form-data"
            data-review-form
        >
            @csrf
            <input type="hidden" name="order_id" data-review-order-id value="">

            {{-- KHUNG CHỨA DANH SÁCH CÁC SẢN PHẨM TRONG ĐƠN HÀNG (1 HOẶC NHIỀU SP) --}}
            <div class="review-modal__products-container" data-review-products-container>
                <div class="review-modal__product-item" data-product-item>
                    <input type="hidden" name="items[0][product_id]" data-item-product-id value="">
                    <input type="hidden" name="items[0][review_id]" data-item-review-id value="">
                    <input type="hidden" name="items[0][rating]" data-item-rating value="5">

                    {{-- 1. THÔNG TIN SẢN PHẨM --}}
                    <div class="review-modal__product-card">
                        <div class="review-modal__product-image-wrap">
                            <img
                                src="{{ asset('images/auth/bear-hero.png') }}"
                                alt="Gấu Teddy Mật Ong 45cm"
                                class="review-modal__product-img"
                                data-item-image
                            >
                        </div>
                        <div class="review-modal__product-info">
                            <h3 class="review-modal__product-name" data-item-name>Gấu Teddy Mật Ong 45cm</h3>
                            <div class="review-modal__product-meta">
                                <span class="review-modal__status-text">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="m9 12 2 2 4-4" />
                                    </svg>
                                    <span>Đơn hàng đã hoàn thành</span>
                                </span>
                                <span class="review-modal__order-code-badge" data-item-order-badge hidden>
                                    Đơn: <strong data-item-order-code></strong>
                                </span>
                                <span class="review-modal__badge-purchased">Đã mua</span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. ĐÁNH GIÁ CỦA BẠN (RATING & TAGS) --}}
                    <div class="review-modal__section">
                        <label class="review-modal__section-title">Đánh giá của bạn</label>

                        <div class="review-modal__rating-row" role="radiogroup" aria-label="Đánh giá chất lượng từ 1 đến 5 sao">
                            @for ($i = 1; $i <= 5; $i++)
                                <button
                                    type="button"
                                    class="review-modal__star-btn is-active"
                                    data-star-index="{{ $i }}"
                                    role="radio"
                                    aria-checked="{{ $i === 5 ? 'true' : 'false' }}"
                                    aria-label="Đánh giá {{ $i }} sao"
                                >
                                    <svg class="review-modal__star-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                                    </svg>
                                </button>
                            @endfor
                        </div>

                        <div class="review-modal__tags-wrap" aria-label="Gợi ý cảm nhận nhanh">
                            <button type="button" class="review-modal__tag-btn" data-review-tag="Mềm mại">
                                <span class="review-modal__tag-text">Mềm mại</span>
                                <span class="review-modal__tag-check" aria-hidden="true">✓</span>
                            </button>
                            <button type="button" class="review-modal__tag-btn" data-review-tag="Đáng yêu">
                                <span class="review-modal__tag-text">Đáng yêu</span>
                                <span class="review-modal__tag-check" aria-hidden="true">✓</span>
                            </button>
                            <button type="button" class="review-modal__tag-btn" data-review-tag="Đóng gói đẹp">
                                <span class="review-modal__tag-text">Đóng gói đẹp</span>
                                <span class="review-modal__tag-check" aria-hidden="true">✓</span>
                            </button>
                            <button type="button" class="review-modal__tag-btn" data-review-tag="Giao hàng nhanh">
                                <span class="review-modal__tag-text">Giao hàng nhanh</span>
                                <span class="review-modal__tag-check" aria-hidden="true">✓</span>
                            </button>
                        </div>
                    </div>

                    {{-- 3. NHẬN XÉT (COMMENT) --}}
                    <div class="review-modal__section">
                        <label class="review-modal__section-title">Nhận xét</label>
                        <div class="review-modal__textarea-wrap">
                            <textarea
                                name="items[0][comment]"
                                class="review-modal__textarea"
                                placeholder="Chia sẻ cảm nhận chi tiết của bạn về sản phẩm này nhé..."
                                maxlength="1000"
                                rows="3"
                                required
                                data-item-comment
                            ></textarea>
                            <div class="review-modal__counter" aria-live="polite">
                                <span data-item-counter>0</span>/1000
                            </div>
                        </div>
                    </div>

                    {{-- 4. THÊM ẢNH (TÙY CHỌN - TỐI ĐA 5 ẢNH) --}}
                    <div class="review-modal__section">
                        <label class="review-modal__section-title">Thêm ảnh <span class="review-modal__optional">(tối đa 5 ảnh)</span></label>

                        <div class="review-modal__upload-box" data-item-upload-box tabindex="0" role="button" aria-label="Bấm để tải ảnh lên">
                            <input
                                type="file"
                                name="items[0][images][]"
                                class="review-modal__file-input"
                                accept="image/png,image/jpeg,image/webp"
                                multiple
                                data-item-file-input
                            >
                            <div class="review-modal__upload-prompt">
                                <div class="review-modal__upload-icon-circle">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242" />
                                        <path d="M12 12v9" />
                                        <path d="m8 16 4-4 4 4" />
                                    </svg>
                                </div>
                                <div class="review-modal__upload-copy">
                                    <strong>Tải ảnh lên</strong>
                                    <small>Tối đa 5 ảnh (PNG, JPG ≤ 5MB)</small>
                                </div>
                            </div>
                        </div>

                        <div class="review-modal__previews" data-item-previews hidden></div>
                    </div>
                </div>
            </div>

            {{-- 5. THÔNG BÁO LỖI / TRẠNG THÁI --}}
            <div class="review-modal__alert" data-review-alert hidden role="alert"></div>


            {{-- 7. CHÂN TRANG & NÚT BẤM (CHUNG CHO TOÀN BỘ ĐƠN HÀNG) --}}
            <div class="review-modal__footer">
                <button
                    type="button"
                    class="review-modal__cancel-btn"
                    data-review-close
                >
                    Hủy
                </button>
                <button
                    type="submit"
                    class="review-modal__submit-btn"
                    data-review-submit
                >
                    <span data-review-submit-label>Gửi đánh giá</span>
                </button>
            </div>
        </form>
    </div>

    {{-- TEMPLATE DÀNH CHO RENDER TỪNG SẢN PHẨM TRONG ĐƠN HÀNG (JS SẼ CLONE CHO MỖI SẢN PHẨM) --}}
    <template id="review-product-item-template">
        <div class="review-modal__product-item" data-product-item>
            <input type="hidden" name="items[__INDEX__][product_id]" data-item-product-id value="">
            <input type="hidden" name="items[__INDEX__][review_id]" data-item-review-id value="">
            <input type="hidden" name="items[__INDEX__][rating]" data-item-rating value="5">

            <div class="review-modal__product-card">
                <div class="review-modal__product-image-wrap">
                    <img src="" alt="" class="review-modal__product-img" data-item-image>
                </div>
                <div class="review-modal__product-info">
                    <h3 class="review-modal__product-name" data-item-name></h3>
                    <div class="review-modal__product-meta">
                        <span class="review-modal__status-text">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                            <span>Đơn hàng đã hoàn thành</span>
                        </span>
                        <span class="review-modal__order-code-badge" data-item-order-badge hidden>
                            Đơn: <strong data-item-order-code></strong>
                        </span>
                        <span class="review-modal__badge-purchased">Đã mua</span>
                    </div>
                </div>
            </div>

            <div class="review-modal__section">
                <label class="review-modal__section-title">Đánh giá của bạn</label>

                <div class="review-modal__rating-row" role="radiogroup" aria-label="Đánh giá chất lượng từ 1 đến 5 sao">
                    @for ($i = 1; $i <= 5; $i++)
                        <button
                            type="button"
                            class="review-modal__star-btn"
                            data-star-index="{{ $i }}"
                            role="radio"
                            aria-checked="{{ $i === 5 ? 'true' : 'false' }}"
                            aria-label="Đánh giá {{ $i }} sao"
                        >
                            <svg class="review-modal__star-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                            </svg>
                        </button>
                    @endfor
                </div>

                <div class="review-modal__tags-wrap" aria-label="Gợi ý cảm nhận nhanh">
                    <button type="button" class="review-modal__tag-btn" data-review-tag="Mềm mại">
                        <span class="review-modal__tag-text">Mềm mại</span>
                        <span class="review-modal__tag-check" aria-hidden="true">✓</span>
                    </button>
                    <button type="button" class="review-modal__tag-btn" data-review-tag="Đáng yêu">
                        <span class="review-modal__tag-text">Đáng yêu</span>
                        <span class="review-modal__tag-check" aria-hidden="true">✓</span>
                    </button>
                    <button type="button" class="review-modal__tag-btn" data-review-tag="Đóng gói đẹp">
                        <span class="review-modal__tag-text">Đóng gói đẹp</span>
                        <span class="review-modal__tag-check" aria-hidden="true">✓</span>
                    </button>
                    <button type="button" class="review-modal__tag-btn" data-review-tag="Giao hàng nhanh">
                        <span class="review-modal__tag-text">Giao hàng nhanh</span>
                        <span class="review-modal__tag-check" aria-hidden="true">✓</span>
                    </button>
                </div>
            </div>

            <div class="review-modal__section">
                <label class="review-modal__section-title">Nhận xét</label>
                <div class="review-modal__textarea-wrap">
                    <textarea
                        name="items[__INDEX__][comment]"
                        class="review-modal__textarea"
                        placeholder="Chia sẻ cảm nhận chi tiết của bạn về sản phẩm này nhé..."
                        maxlength="1000"
                        rows="3"
                        required
                        data-item-comment
                    ></textarea>
                    <div class="review-modal__counter" aria-live="polite">
                        <span data-item-counter>0</span>/1000
                    </div>
                </div>
            </div>

            <div class="review-modal__section">
                <label class="review-modal__section-title">Thêm ảnh <span class="review-modal__optional">(tối đa 5 ảnh)</span></label>

                <div class="review-modal__upload-box" data-item-upload-box tabindex="0" role="button" aria-label="Bấm để tải ảnh lên">
                    <input
                        type="file"
                        name="items[__INDEX__][images][]"
                        class="review-modal__file-input"
                        accept="image/png,image/jpeg,image/webp"
                        multiple
                        data-item-file-input
                    >
                    <div class="review-modal__upload-prompt">
                        <div class="review-modal__upload-icon-circle">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242" />
                                <path d="M12 12v9" />
                                <path d="m8 16 4-4 4 4" />
                            </svg>
                        </div>
                        <div class="review-modal__upload-copy">
                            <strong>Tải ảnh lên</strong>
                            <small>Tối đa 5 ảnh (PNG, JPG ≤ 5MB)</small>
                        </div>
                    </div>
                </div>

                <div class="review-modal__previews" data-item-previews hidden></div>
            </div>
        </div>
    </template>
</div>
