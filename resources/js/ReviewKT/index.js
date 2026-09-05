/**
 * QUẢN LÝ POPUP VIẾT ĐÁNH GIÁ SẢN PHẨM (REVIEWKT)
 * Mật Ngọt Bear - Chuẩn Shopee (1 Đơn hàng -> 1 Nút đánh giá -> Popup hiển thị tất cả sản phẩm trong đơn)
 * Author: Kim Tuyến
 */

/**
 * Hiển thị thông báo trong popup
 */
const showModalAlert = (modal, message, type = 'error') => {
    if (!modal) return;
    const alertBox = modal.querySelector('[data-review-alert]');
    if (!alertBox) return;

    alertBox.textContent = message;
    alertBox.className = `review-modal__alert is-${type}`;
    alertBox.hidden = false;
};

/**
 * Loại bỏ cụm từ tag ra khỏi nội dung nhận xét khi bỏ tích tag
 */
const removeTagFromText = (text, tag) => {
    if (!text || !tag) return text || '';
    const lowerTag = tag.toLowerCase().trim();
    const escapeRegex = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const escaped = escapeRegex(lowerTag);

    let updated = text;

    // 1. Loại bỏ các mẫu có chữ "Sản phẩm <tag>"
    updated = updated.replace(new RegExp(`,\\s*Sản phẩm\\s+${escaped}`, 'gi'), '');
    updated = updated.replace(new RegExp(`Sản phẩm\\s+${escaped}\\s*,\\s*`, 'gi'), '');
    updated = updated.replace(new RegExp(`Sản phẩm\\s+${escaped}`, 'gi'), '');

    // 2. Loại bỏ cụm tag đứng cạnh dấu phẩy
    updated = updated.replace(new RegExp(`,\\s*${escaped}`, 'gi'), '');
    updated = updated.replace(new RegExp(`${escaped}\\s*,\\s*`, 'gi'), '');

    // 3. Loại bỏ cụm tag đứng một mình
    updated = updated.replace(new RegExp(`\\b${escaped}\\b`, 'gi'), '');
    updated = updated.replace(new RegExp(escaped, 'gi'), '');

    // 4. Dọn dẹp khoảng trắng và dấu phẩy thừa
    updated = updated
        .replace(/^[\s,]+/, '')
        .replace(/[\s,]+$/, '')
        .replace(/\s*,\s*,+/g, ', ')
        .replace(/\s{2,}/g, ' ')
        .trim();

    // 5. Nếu chỉ còn lại chữ "Sản phẩm" hoặc dấu câu thì làm rỗng
    if (/^Sản phẩm[\s,.]*$/i.test(updated)) {
        updated = '';
    } else if (/^(mềm mại|đáng yêu|đóng gói đẹp|giao hàng nhanh)/i.test(updated)) {
        // Chuẩn hóa câu mở đầu nếu từ tag còn lại đứng đầu câu
        updated = `Sản phẩm ${updated}`;
    }

    return updated;
};

/**
 * Gán tương tác (sao, tags, textarea counter, upload preview) cho từng sản phẩm trong popup
 */
const bindProductItem = (itemEl) => {
    const ratingInput = itemEl.querySelector('[data-item-rating]');
    const starButtons = [...itemEl.querySelectorAll('[data-star-index]')];
    const tagButtons = [...itemEl.querySelectorAll('[data-review-tag]')];
    const textarea = itemEl.querySelector('[data-item-comment]');
    const counter = itemEl.querySelector('[data-item-counter]');
    const uploadBox = itemEl.querySelector('[data-item-upload-box]');
    const fileInput = itemEl.querySelector('[data-item-file-input]');
    const previewsContainer = itemEl.querySelector('[data-item-previews]');

    let itemRating = parseInt(ratingInput?.value, 10) || 5;
    let hoverRating = 0;

    const renderItemStars = (stars) => {
        starButtons.forEach((btn) => {
            const index = parseInt(btn.dataset.starIndex, 10);
            const isActive = index <= stars;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-checked', index === itemRating ? 'true' : 'false');
        });
    };

    renderItemStars(itemRating);

    // Xử lý sao đánh giá
    starButtons.forEach((btn) => {
        const index = parseInt(btn.dataset.starIndex, 10);

        btn.addEventListener('mouseenter', () => {
            if (btn.disabled) return;
            hoverRating = index;
            renderItemStars(hoverRating);
        });

        btn.addEventListener('mouseleave', () => {
            if (btn.disabled) return;
            hoverRating = 0;
            renderItemStars(itemRating);
        });

        btn.addEventListener('click', () => {
            if (btn.disabled) return;
            itemRating = index;
            if (ratingInput) ratingInput.value = itemRating;
            renderItemStars(itemRating);
        });
    });

    // Xử lý click tag cảm nhận nhanh (chọn -> thêm từ; bỏ chọn -> xóa từ tương ứng)
    tagButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            if (btn.disabled) return;
            const isSelected = btn.classList.toggle('is-selected');
            const tagText = btn.dataset.reviewTag;

            if (textarea) {
                const currentText = textarea.value.trim();
                if (isSelected) {
                    // Thêm từ vào ô đánh giá
                    if (currentText.length === 0) {
                        textarea.value = `Sản phẩm ${tagText.toLowerCase()}`;
                    } else if (!currentText.toLowerCase().includes(tagText.toLowerCase())) {
                        textarea.value = `${currentText}, ${tagText.toLowerCase()}`;
                    }
                } else {
                    // Bỏ tích -> xóa từ tương ứng ra khỏi ô đánh giá
                    textarea.value = removeTagFromText(textarea.value, tagText);
                }

                if (counter) counter.textContent = textarea.value.length;
            }
        });
    });

    // Xử lý gõ textarea & đếm ký tự (tự động bỏ chọn tag nếu người dùng xóa từ trong ô)
    if (textarea && counter) {
        textarea.addEventListener('input', () => {
            counter.textContent = textarea.value.length;
            const currentVal = textarea.value.toLowerCase();
            tagButtons.forEach((btn) => {
                const tagText = btn.dataset.reviewTag?.toLowerCase();
                if (tagText && !currentVal.includes(tagText)) {
                    btn.classList.remove('is-selected');
                }
            });
        });
    }

    // Quản lý tải ảnh (GIỚI HẠN TỐI ĐA 5 ẢNH)
    let currentFiles = [];

    const updateFileInputAndPreviews = () => {
        // Đồng bộ DataTransfer để form input mang đúng các file chưa bị xóa
        try {
            const dt = new DataTransfer();
            currentFiles.forEach((file) => dt.items.add(file));
            fileInput.files = dt.files;
        } catch (e) {
            // bỏ qua nếu trình duyệt không hỗ trợ DataTransfer constructor
        }

        // Render danh sách ảnh xem trước
        if (currentFiles.length === 0) {
            previewsContainer.hidden = true;
            previewsContainer.innerHTML = '';
        } else {
            previewsContainer.hidden = false;
            previewsContainer.innerHTML = '';

            currentFiles.forEach((file, idx) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'review-modal__preview-item';
                previewItem.innerHTML = `
                    <img src="" alt="Ảnh preview ${idx + 1}">
                    <button type="button" class="review-modal__preview-remove" title="Xóa ảnh">&times;</button>
                `;

                const imgEl = previewItem.querySelector('img');
                const reader = new FileReader();
                reader.onload = (event) => {
                    imgEl.src = event.target.result;
                };
                reader.readAsDataURL(file);

                previewItem.querySelector('.review-modal__preview-remove')?.addEventListener('click', (e) => {
                    e.stopPropagation();
                    currentFiles.splice(idx, 1);
                    updateFileInputAndPreviews();
                });

                previewsContainer.appendChild(previewItem);
            });
        }

        // Cập nhật trạng thái ô upload khi đạt giới hạn 5 ảnh
        if (uploadBox) {
            const copyEl = uploadBox.querySelector('.review-modal__upload-copy');
            if (currentFiles.length >= 5) {
                uploadBox.classList.add('is-max-files');
                fileInput.disabled = true;
                if (copyEl) {
                    copyEl.innerHTML = `
                        <strong style="color:#d97736;">Đã tải đủ 5/5 ảnh</strong>
                        <small>Bấm ✕ trên ảnh để xóa nếu muốn đổi ảnh khác</small>
                    `;
                }
            } else {
                uploadBox.classList.remove('is-max-files');
                fileInput.disabled = false;
                if (copyEl) {
                    const countSuffix = currentFiles.length > 0 ? ` (${currentFiles.length}/5)` : '';
                    copyEl.innerHTML = `
                        <strong>Tải ảnh lên${countSuffix}</strong>
                        <small>Tối đa 5 ảnh (PNG, JPG ≤ 5MB)</small>
                    `;
                }
            }
        }
    };

    if (fileInput && previewsContainer) {
        fileInput.addEventListener('change', () => {
            const newlySelected = [...(fileInput.files || [])].filter((f) => f.type.startsWith('image/'));
            fileInput.value = ''; // Reset để có thể click chọn tiếp các ảnh sau
            if (newlySelected.length === 0) return;

            const maxAllowed = 5;
            const remaining = maxAllowed - currentFiles.length;
            const modal = itemEl.closest('[data-review-modal]');

            if (remaining <= 0) {
                if (modal) showModalAlert(modal, 'Bạn đã tải lên tối đa 5 ảnh cho sản phẩm này.', 'error');
                return;
            }

            if (newlySelected.length > remaining) {
                if (modal) {
                    showModalAlert(modal, `Chỉ được tải thêm tối đa ${remaining} ảnh nữa (giới hạn 5 ảnh/sản phẩm).`, 'error');
                }
            }

            const filesToAdd = newlySelected.slice(0, remaining);
            currentFiles = currentFiles.concat(filesToAdd);
            updateFileInputAndPreviews();
        });
    }
};

/**
 * Đóng popup đánh giá
 */
export const closeReviewModal = () => {
    const modal = document.querySelector('[data-review-modal]');
    if (!modal) return;

    modal.hidden = true;
    document.body.classList.remove('has-review-modal');
};

/**
 * Mở popup đánh giá cho cả đơn hàng (Chuẩn Shopee)
 * Lấy tất cả sản phẩm trong đơn hàng và hiển thị trên cùng 1 popup
 */
export const openOrderReviewModal = async (orderId) => {
    const modal = document.querySelector('[data-review-modal]');
    if (!modal) return;

    const modalTitle = modal.querySelector('[data-review-modal-title]');
    const modalSubtitle = modal.querySelector('[data-review-modal-subtitle]');
    const orderIdInput = modal.querySelector('[data-review-order-id]');
    const productsContainer = modal.querySelector('[data-review-products-container]');
    const template = document.getElementById('review-product-item-template');
    const alertBox = modal.querySelector('[data-review-alert]');
    const submitBtn = modal.querySelector('[data-review-submit]');
    const submitLabel = modal.querySelector('[data-review-submit-label]');

    if (alertBox) {
        alertBox.hidden = true;
        alertBox.textContent = '';
        alertBox.className = 'review-modal__alert';
    }

    if (orderIdInput) {
        orderIdInput.value = orderId;
    }

    if (modalTitle) modalTitle.textContent = 'Đánh giá đơn hàng';
    if (modalSubtitle) modalSubtitle.textContent = 'Đang tải thông tin đơn hàng...';
    if (submitBtn) submitBtn.disabled = true;

    // Loading spinner
    if (productsContainer) {
        productsContainer.innerHTML = `
            <div class="review-modal__loading">
                <div class="review-modal__spinner"></div>
                <span>Đang tải thông tin sản phẩm trong đơn...</span>
            </div>
        `;
    }

    modal.hidden = false;
    document.body.classList.add('has-review-modal');

    try {
        const response = await fetch(`/customer/reviews/order/${encodeURIComponent(orderId)}`, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const res = await response.json();
        if (!response.ok || !res.success) {
            throw new Error(res.message || 'Không thể lấy thông tin đơn hàng.');
        }

        const data = res.data;
        const items = data.items || [];

        if (items.length === 0) {
            throw new Error('Đơn hàng không có sản phẩm nào.');
        }

        if (modalSubtitle) {
            modalSubtitle.textContent = `Đơn hàng #${data.order_code} • ${items.length} sản phẩm`;
        }

        if (productsContainer && template) {
            productsContainer.innerHTML = '';

            let allEdited = true;

            items.forEach((item, index) => {
                const itemHtml = template.innerHTML.replace(/__INDEX__/g, index);
                const tempWrap = document.createElement('div');
                tempWrap.innerHTML = itemHtml.trim();
                const itemNode = tempWrap.firstElementChild;

                const pIdInput = itemNode.querySelector('[data-item-product-id]');
                const rIdInput = itemNode.querySelector('[data-item-review-id]');
                const ratingInput = itemNode.querySelector('[data-item-rating]');
                const nameEl = itemNode.querySelector('[data-item-name]');
                const imgEl = itemNode.querySelector('[data-item-image]');
                const textarea = itemNode.querySelector('[data-item-comment]');
                const counter = itemNode.querySelector('[data-item-counter]');

                if (pIdInput) pIdInput.value = item.product_id;
                if (nameEl) nameEl.textContent = item.product_name;
                if (imgEl && item.product_image) {
                    imgEl.src = item.product_image;
                    imgEl.alt = item.product_name;
                }

                // Hiển thị mã đơn hàng trên card sản phẩm
                const orderBadge = itemNode.querySelector('[data-item-order-badge]');
                const orderCodeEl = itemNode.querySelector('[data-item-order-code]');
                if (orderBadge && orderCodeEl && data.order_code) {
                    orderCodeEl.textContent = `#${data.order_code}`;
                    orderBadge.hidden = false;
                }

                // Nếu đơn hàng có từ 2 sản phẩm trở lên, thêm badge đánh số thứ tự
                if (items.length > 1) {
                    const badge = document.createElement('div');
                    badge.className = 'review-modal__product-order-badge';
                    badge.innerHTML = `<span>🧸</span><span>Sản phẩm ${index + 1}/${items.length}</span>`;
                    itemNode.prepend(badge);
                }

                const existingReview = item.review;
                let isItemEdited = false;

                if (existingReview) {
                    if (rIdInput) rIdInput.value = existingReview.id;
                    if (ratingInput) ratingInput.value = existingReview.rating;
                    if (textarea) textarea.value = existingReview.comment;
                    if (counter) counter.textContent = (existingReview.comment || '').length;

                    isItemEdited = Boolean(existingReview.is_edited);

                    if (isItemEdited) {
                        itemNode.querySelectorAll('button, textarea, input:not([type="hidden"])').forEach((el) => {
                            el.disabled = true;
                        });
                        const editedNotice = document.createElement('div');
                        editedNotice.className = 'review-modal__alert is-error';
                        editedNotice.style.fontSize = '12px';
                        editedNotice.style.padding = '6px 12px';
                        editedNotice.textContent = 'Đánh giá này đã từng được chỉnh sửa (chỉ được sửa 1 lần duy nhất).';
                        itemNode.appendChild(editedNotice);
                    } else {
                        allEdited = false;
                    }
                } else {
                    allEdited = false;
                    if (rIdInput) rIdInput.value = '';
                    if (ratingInput) ratingInput.value = '5';
                    if (textarea) textarea.value = '';
                    if (counter) counter.textContent = '0';
                    itemNode.querySelectorAll('[data-review-tag]').forEach((btn) => btn.classList.remove('is-selected'));
                }

                bindProductItem(itemNode);
                productsContainer.appendChild(itemNode);
            });

            if (submitBtn) {
                submitBtn.disabled = allEdited;
                if (allEdited) {
                    showModalAlert(modal, 'Tất cả sản phẩm trong đơn này đã được chỉnh sửa đánh giá tối đa 1 lần.');
                }
            }

            if (submitLabel) {
                submitLabel.textContent = items.some((i) => i.review) ? 'Cập nhật đánh giá' : 'Gửi đánh giá';
            }
        }
    } catch (err) {
        if (productsContainer) {
            productsContainer.innerHTML = `
                <div class="review-modal__alert is-error" style="display:block;">
                    ${err.message || 'Lỗi khi tải thông tin đơn hàng.'}
                </div>
            `;
        }
    } finally {
        if (submitBtn && !submitBtn.disabled) {
            submitBtn.disabled = false;
        }
    }
};

/**
 * Mở modal đánh giá cho sản phẩm đơn lẻ (tương thích ngược)
 */
export const openReviewModal = (options = {}) => {
    const modal = document.querySelector('[data-review-modal]');
    if (!modal) return;

    const {
        review_id = '',
        product_id = '1',
        product_name = 'Gấu Teddy Mật Ong 45cm',
        product_image = '/images/auth/bear-hero.png',
        order_id = '',
        order_code = '',
        rating = 5,
        comment = '',
        selected_tags = [],
        is_edited = false,
    } = options;

    const modalTitle = modal.querySelector('[data-review-modal-title]');
    const modalSubtitle = modal.querySelector('[data-review-modal-subtitle]');
    const orderIdInput = modal.querySelector('[data-review-order-id]');
    const productsContainer = modal.querySelector('[data-review-products-container]');
    const template = document.getElementById('review-product-item-template');
    const alertBox = modal.querySelector('[data-review-alert]');
    const submitBtn = modal.querySelector('[data-review-submit]');
    const submitLabel = modal.querySelector('[data-review-submit-label]');

    if (alertBox) {
        alertBox.hidden = true;
        alertBox.textContent = '';
        alertBox.className = 'review-modal__alert';
    }

    const isEditMode = Boolean(review_id);
    if (orderIdInput) orderIdInput.value = order_id || '';

    if (modalTitle) {
        modalTitle.textContent = isEditMode ? 'Chỉnh sửa đánh giá' : 'Viết đánh giá';
    }

    if (modalSubtitle) {
        modalSubtitle.textContent = isEditMode
            ? (is_edited ? 'Đánh giá này đã được chỉnh sửa 1 lần và không thể sửa thêm.' : 'Lưu ý: Bạn chỉ được chỉnh sửa đánh giá 1 lần duy nhất.')
            : 'Chia sẻ cảm nhận của bạn về sản phẩm này';
    }

    if (submitLabel) {
        submitLabel.textContent = isEditMode ? 'Cập nhật đánh giá' : 'Gửi đánh giá';
    }

    if (productsContainer && template) {
        productsContainer.innerHTML = '';

        const itemHtml = template.innerHTML.replace(/__INDEX__/g, '0');
        const tempWrap = document.createElement('div');
        tempWrap.innerHTML = itemHtml.trim();
        const itemNode = tempWrap.firstElementChild;

        const pIdInput = itemNode.querySelector('[data-item-product-id]');
        const rIdInput = itemNode.querySelector('[data-item-review-id]');
        const ratingInput = itemNode.querySelector('[data-item-rating]');
        const nameEl = itemNode.querySelector('[data-item-name]');
        const imgEl = itemNode.querySelector('[data-item-image]');
        const textarea = itemNode.querySelector('[data-item-comment]');
        const counter = itemNode.querySelector('[data-item-counter]');

        if (pIdInput) pIdInput.value = product_id;
        if (rIdInput) rIdInput.value = review_id;
        if (ratingInput) ratingInput.value = rating;
        if (nameEl) nameEl.textContent = product_name;
        if (imgEl && product_image) {
            imgEl.src = product_image;
            imgEl.alt = product_name;
        }
        if (textarea) textarea.value = comment;
        if (counter) counter.textContent = comment.length;

        // Hiển thị mã đơn hàng nếu có
        const orderBadge = itemNode.querySelector('[data-item-order-badge]');
        const orderCodeEl = itemNode.querySelector('[data-item-order-code]');
        if (orderBadge && orderCodeEl && order_code) {
            orderCodeEl.textContent = `#${order_code}`;
            orderBadge.hidden = false;
        } else if (orderBadge) {
            orderBadge.hidden = true;
        }

        // Tags
        const tagsToSelect = Array.isArray(selected_tags)
            ? selected_tags
            : (typeof selected_tags === 'string' && selected_tags ? selected_tags.split(',').map((t) => t.trim()) : []);

        itemNode.querySelectorAll('[data-review-tag]').forEach((btn) => {
            btn.classList.toggle('is-selected', tagsToSelect.includes(btn.dataset.reviewTag));
        });

        if (is_edited) {
            itemNode.querySelectorAll('button, textarea, input:not([type="hidden"])').forEach((el) => {
                el.disabled = true;
            });
            if (submitBtn) submitBtn.disabled = true;
            showModalAlert(modal, 'Đánh giá này đã từng được chỉnh sửa tối đa 1 lần duy nhất.', 'error');
        } else if (submitBtn) {
            submitBtn.disabled = false;
        }

        bindProductItem(itemNode);
        productsContainer.appendChild(itemNode);
    }

    modal.hidden = false;
    document.body.classList.add('has-review-modal');
};

/**
 * Khởi tạo toàn bộ sự kiện cho popup đánh giá
 */
export const initReviewModal = () => {
    const modal = document.querySelector('[data-review-modal]');
    if (!modal) return;

    const form = modal.querySelector('[data-review-form]');
    const submitBtn = modal.querySelector('[data-review-submit]');
    const submitLabel = modal.querySelector('[data-review-submit-label]');
    const productsContainer = modal.querySelector('[data-review-products-container]');

    // Bind item mặc định nếu có sẵn trên DOM
    const initialItems = modal.querySelectorAll('[data-product-item]');
    initialItems.forEach((item) => bindProductItem(item));

    // Đóng modal khi bấm backdrop hoặc nút Đóng / Hủy
    modal.querySelectorAll('[data-review-close]').forEach((btn) => {
        btn.addEventListener('click', closeReviewModal);
    });

    // Phím Escape đóng modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.hidden) {
            closeReviewModal();
        }
    });

    // Submit biểu mẫu đánh giá
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Kiểm tra nhận xét từng sản phẩm
        const itemTextareas = [...modal.querySelectorAll('[data-item-comment]')];
        for (const ta of itemTextareas) {
            if (!ta.disabled && ta.value.trim().length === 0) {
                showModalAlert(modal, 'Vui lòng điền nội dung nhận xét cho tất cả các sản phẩm.');
                ta.focus();
                return;
            }
        }

        const formData = new FormData(form);
        const submitUrl = form.action;

        if (submitBtn) submitBtn.disabled = true;
        const originalLabel = submitLabel ? submitLabel.textContent : '';
        if (submitLabel) submitLabel.textContent = 'Đang gửi đánh giá...';

        const alertBox = modal.querySelector('[data-review-alert]');
        if (alertBox) alertBox.hidden = true;

        try {
            const response = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: formData,
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errorMsg = result.errors
                    ? Object.values(result.errors).flat()[0]
                    : result.message || 'Không thể lưu đánh giá. Vui lòng thử lại.';
                throw new Error(errorMsg);
            }

            const successMsg = result.message || 'Cảm ơn bạn đã gửi đánh giá!';
            showModalAlert(modal, successMsg, 'success');

            const orderIdVal = modal.querySelector('[data-review-order-id]')?.value;
            window.dispatchEvent(new CustomEvent('review:order_completed', { detail: { order_id: orderIdVal, data: result.data } }));

            if (window.toastManager?.success) {
                window.toastManager.success(successMsg);
            }

            window.setTimeout(() => {
                closeReviewModal();

                // Cập nhật trạng thái nút Đánh giá trên đơn hàng vừa xong
                if (orderIdVal) {
                    document.querySelectorAll(`[data-open-order-review-modal][data-order-id="${orderIdVal}"]`).forEach((btn) => {
                        btn.innerHTML = `
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="m9 12 2 2 4-4"/>
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                            </svg>
                            <span>Đã đánh giá</span>
                        `;
                        btn.classList.remove('bg-amber-600', 'hover:bg-amber-700');
                        btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                    });
                }
            }, 1200);

        } catch (error) {
            showModalAlert(modal, error.message);
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            if (submitLabel) submitLabel.textContent = originalLabel;
        }
    });

    // Lắng nghe click nút "Đánh giá" theo đơn hàng (Chuẩn Shopee)
    document.addEventListener('click', (e) => {
        const orderTrigger = e.target.closest('[data-open-order-review-modal]');
        if (orderTrigger) {
            e.preventDefault();
            const orderId = orderTrigger.dataset.orderId;
            if (orderId) {
                openOrderReviewModal(orderId);
            }
            return;
        }

        const singleTrigger = e.target.closest('[data-open-review-modal]');
        if (singleTrigger) {
            e.preventDefault();
            const rawTags = singleTrigger.dataset.selectedTags || (singleTrigger.dataset.selectedTag ? [singleTrigger.dataset.selectedTag] : []);
            const selectedTags = Array.isArray(rawTags) ? rawTags : rawTags.split(',').map((t) => t.trim());

            openReviewModal({
                review_id: singleTrigger.dataset.reviewId,
                product_id: singleTrigger.dataset.productId || '1',
                product_name: singleTrigger.dataset.productName || 'Gấu Teddy Mật Ong 45cm',
                product_image: singleTrigger.dataset.productImage || '',
                order_id: singleTrigger.dataset.orderId,
                order_code: singleTrigger.dataset.orderCode || '',
                rating: singleTrigger.dataset.rating ? parseInt(singleTrigger.dataset.rating, 10) : 5,
                comment: singleTrigger.dataset.comment || '',
                selected_tags: selectedTags,
                is_edited: singleTrigger.dataset.isEdited === 'true' || singleTrigger.dataset.isEdited === '1',
            });
        }
    });

    // Tự động mở popup nếu URL có ?popup=1 hoặc ?test=1
    try {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('popup') === '1' || urlParams.get('test') === '1' || urlParams.get('demo') === '1') {
            openReviewModal({
                product_id: 1,
                product_name: 'Gấu Teddy Mật Ong 45cm',
                product_image: '/images/auth/bear-hero.png',
                rating: 5,
                selected_tags: [],
                comment: '',
            });
        }
    } catch (e) {
        // Ignored
    }
};

// Đưa ra window để gọi từ bất kỳ view nào
window.openOrderReviewModal = openOrderReviewModal;
window.openReviewModal = openReviewModal;
window.closeReviewModal = closeReviewModal;

document.addEventListener('DOMContentLoaded', initReviewModal);
