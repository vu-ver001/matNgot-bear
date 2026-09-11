/**
 * CUSTOMER CHAT JAVASCRIPT (MẬT NGỌT BEAR)
 * Author: Kim Tuyến
 */

document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.querySelector('[data-customer-chat]');
    if (!chatContainer) return;

    const chatBody = chatContainer.querySelector('[data-chat-body]');
    const chatForm = chatContainer.querySelector('[data-chat-form]');
    const chatInput = chatContainer.querySelector('[data-chat-input]');
    const submitBtn = chatContainer.querySelector('[data-chat-submit]');

    let lastMessageId = 0;
    const existingMsgEls = chatBody ? chatBody.querySelectorAll('[data-msg-id]:not([data-msg-id="0"])') : [];
    if (existingMsgEls.length > 0) {
        const lastEl = existingMsgEls[existingMsgEls.length - 1];
        lastMessageId = parseInt(lastEl.dataset.msgId, 10) || 0;
    }

    // Cuộn xuống cuối khung chat
    const scrollToBottom = (smooth = false) => {
        if (!chatBody) return;
        chatBody.scrollTo({
            top: chatBody.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto',
        });
    };

    scrollToBottom(false);

    // Dọn sạch order_id và from_order khỏi thanh địa chỉ URL bằng replaceState
    // để sau khi gợi ý đã hiển thị / gửi / bỏ qua, nếu F5 hoặc reload thì không bao giờ bị hiện lại gợi ý cũ
    const cleanOrderIdFromUrl = () => {
        if (window.history && window.history.replaceState) {
            try {
                const currentUrl = new URL(window.location.href);
                let changed = false;
                if (currentUrl.searchParams.has('order_id')) {
                    currentUrl.searchParams.delete('order_id');
                    changed = true;
                }
                if (currentUrl.searchParams.has('from_order')) {
                    currentUrl.searchParams.delete('from_order');
                    changed = true;
                }
                if (changed) {
                    const newSearch = currentUrl.searchParams.toString();
                    const newUrl = currentUrl.pathname + (newSearch ? '?' + newSearch : '');
                    window.history.replaceState({}, document.title, newUrl);
                }
            } catch (e) {
                console.warn('Không thể cập nhật URL:', e);
            }
        }
    };

    cleanOrderIdFromUrl();

    // Cập nhật nhãn trạng thái ca hỗ trợ trên Header (Chờ tiếp nhận / Đang hỗ trợ (Tên nhân viên) / Trực tuyến / Ngoại tuyến)
    const updateHeaderStatus = (statusObj) => {
        if (!statusObj) return;
        const badgeEl = chatContainer.querySelector('[data-support-status-badge]');
        if (badgeEl) {
            badgeEl.className = 'customer-chat-header__badge-online ' + (statusObj.class || '');
            badgeEl.textContent = statusObj.label || '';
        }
        const subtextEl = chatContainer.querySelector('[data-support-status-subtext]');
        if (subtextEl && statusObj.subtext) {
            subtextEl.textContent = statusObj.subtext;
        }
    };


    // Tạo HTML cho tin nhắn mới
    const renderMessageHtml = (msg, options = {}) => {
        const isShop = !msg.is_self;
        const rowClass = isShop ? 'is-shop' : 'is-customer';
        const groupPos = options.groupPos || 'pos-single';
        const showAvatar = options.showAvatar ?? true;
        const showTime = options.showTime ?? true;
        const timestampSec = options.timestamp || Math.floor(Date.now() / 1000);

        let avatarHtml = '';
        if (isShop) {
            avatarHtml = showAvatar
                ? `<img src="/images/auth/bear-hero.png" alt="Mật Ngọt Bear" class="customer-chat-msg-row__avatar" onerror="this.src='/images/customer/product-placeholder.png'">`
                : `<div class="customer-chat-msg-row__avatar is-spacer" aria-hidden="true"></div>`;
        }

        const dateStr = msg.sent_at || '';
        const fullDateTooltip = msg.date ? `${dateStr}, ${msg.date}` : dateStr;

        const formatBubbleContent = (content) => {
            if (content && content.includes('📦 [ĐƠN HÀNG #')) {
                const codeMatch = content.match(/#([A-Z0-9\-]+)/);
                const code = codeMatch ? codeMatch[1] : '';
                const lines = content.split('\n');
                let prodName = '';
                let total = '';
                let status = '';
                lines.forEach(line => {
                    if (line.includes('Sản phẩm:')) prodName = line.replace(/^[•\s\-\*]*Sản phẩm:\s*/, '').trim();
                    if (line.includes('Tổng tiền:')) total = line.replace(/^[•\s\-\*]*Tổng tiền:\s*/, '').trim();
                    if (line.includes('Trạng thái:')) status = line.replace(/^[•\s\-\*]*Trạng thái:\s*/, '').trim();
                });

                return `
                    <div class="chat-order-card">
                        <div class="chat-order-card__header">
                            <span class="chat-order-card__tag">
                                <i class="fa-solid fa-box"></i> #${escapeHtml(code)}
                            </span>
                            ${status ? `<span class="chat-order-card__status">${escapeHtml(status)}</span>` : ''}
                        </div>
                        <div class="chat-order-card__body">
                            <div class="chat-order-card__info">
                                ${prodName ? `<div class="chat-order-card__pname">${escapeHtml(prodName)}</div>` : ''}
                                ${total ? `<div class="chat-order-card__total">Tổng tiền: <strong>${escapeHtml(total)}</strong></div>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }
            return escapeHtml(content).replace(/\n/g, '<br>');
        };

        const images = Array.isArray(msg.image_urls) && msg.image_urls.length > 0
            ? msg.image_urls
            : (Array.isArray(msg.images) && msg.images.length > 0
                ? msg.images
                : (msg.image_url ? [msg.image_url] : []));

        let imgHtml = '';
        if (images.length > 0) {
            const galleryClass = images.length === 1 ? 'is-single' : (images.length === 2 ? 'is-double' : 'is-grid');
            imgHtml = `
                <div class="chat-msg-gallery ${galleryClass}">
                    ${images.map(url => `
                        <div class="chat-msg-image-wrap">
                            <img src="${escapeHtml(url)}" alt="Hình ảnh đính kèm" class="chat-msg-image" loading="lazy" onclick="window.open(this.src, '_blank')">
                        </div>
                    `).join('')}
                </div>
            `;
        }

        const textHtml = msg.content ? `
            <div class="chat-msg-text">${formatBubbleContent(msg.content)}</div>
        ` : '';

        const timeHtml = (showTime && dateStr) ? `
            <div class="chat-msg-time customer-chat-time">
                <span>${escapeHtml(dateStr)}</span>
            </div>
        ` : '';

        const isLastSelf = options.isLastSelf ?? false;
        const statusHtml = (!isShop && isLastSelf) ? `
            <div class="chat-msg-status" data-status="${msg.is_read ? 'seen' : 'sent'}">
                ${msg.is_read ? 'Đã xem' : 'Đã gửi'}
            </div>
        ` : '';

        let bubbleHtml = '';
        if (textHtml) {
            bubbleHtml = `
                <div class="customer-chat-bubble" title="${escapeHtml(fullDateTooltip)}">
                    ${textHtml}
                    ${timeHtml}
                </div>
            `;
        } else if (!imgHtml) {
            bubbleHtml = `
                <div class="customer-chat-bubble" title="${escapeHtml(fullDateTooltip)}">
                    ${timeHtml}
                </div>
            `;
        } else if (timeHtml) {
            bubbleHtml = `
                <div class="chat-msg-time customer-chat-time is-outside">
                    <span>${escapeHtml(dateStr)}</span>
                </div>
            `;
        }

        const hasGalleryClass = images.length > 0 ? 'has-gallery' : '';

        return `
            <div class="customer-chat-msg-row ${rowClass} ${groupPos} ${hasGalleryClass}" 
                 data-msg-id="${msg.id}" 
                 data-sender-id="${msg.sender_id || ''}" 
                 data-is-self="${msg.is_self ? '1' : '0'}" 
                 data-timestamp="${timestampSec}">
                ${avatarHtml}
                <div class="customer-chat-msg-wrap">
                    ${imgHtml}
                    ${bubbleHtml}
                    ${statusHtml}
                </div>
            </div>
        `;
    };

    // Hàm chèn tin nhắn mới theo chuẩn gom nhóm Messenger / Zalo
    const appendCustomerMessage = (msg) => {
        if (!chatBody) return null;

        const existingRows = chatBody.querySelectorAll('.customer-chat-msg-row');
        const lastRow = existingRows.length > 0 ? existingRows[existingRows.length - 1] : null;

        const isSelf = Boolean(msg.is_self);
        const newSenderId = String(msg.sender_id || '');
        const newTimeSec = msg.timestamp ? parseInt(msg.timestamp, 10) : Math.floor(Date.now() / 1000);

        let isConsecutive = false;

        if (lastRow && !lastRow.hasAttribute('data-greeting-prompt')) {
            const lastIsSelf = lastRow.dataset.isSelf === '1';
            const lastSenderId = String(lastRow.dataset.senderId || '');
            const lastTimeSec = parseInt(lastRow.dataset.timestamp, 10) || 0;
            const diffMinutes = lastTimeSec > 0 ? Math.abs(newTimeSec - lastTimeSec) / 60 : 0;

            const sameSender = (lastSenderId && newSenderId)
                ? (lastSenderId === newSenderId)
                : (lastIsSelf === isSelf);

            if (sameSender && diffMinutes <= 10) {
                isConsecutive = true;
                if (lastRow.classList.contains('pos-single')) {
                    lastRow.classList.remove('pos-single');
                    lastRow.classList.add('pos-first');
                } else if (lastRow.classList.contains('pos-last')) {
                    lastRow.classList.remove('pos-last');
                    lastRow.classList.add('pos-middle');
                }

                // Xóa avatar tin nhắn trước đó (chuyển thành spacer để thẳng hàng)
                const lastAvatarImg = lastRow.querySelector('.customer-chat-msg-row__avatar:not(.is-spacer)');
                if (lastAvatarImg) {
                    const spacer = document.createElement('div');
                    spacer.className = 'customer-chat-msg-row__avatar is-spacer';
                    spacer.setAttribute('aria-hidden', 'true');
                    lastAvatarImg.replaceWith(spacer);
                }

                // Xóa giờ của tin nhắn trước đó (chỉ hiện ở tin cuối của cụm)
                const lastTimeEl = lastRow.querySelector('.chat-msg-time, .customer-chat-time');
                if (lastTimeEl) {
                    lastTimeEl.remove();
                }
            }
        }

        // Trạng thái "Đã gửi" / "Đã xem" CHỈ hiển thị ở tin nhắn cuối cùng của đoạn chat nếu tin nhắn đó do chính mình gửi
        const oldStatusEls = chatBody.querySelectorAll('.chat-msg-status');
        oldStatusEls.forEach(el => el.remove());

        const groupPos = isConsecutive ? 'pos-last' : 'pos-single';
        const showAvatar = true;
        const showTime = true;

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = renderMessageHtml(msg, { 
            groupPos, 
            showAvatar, 
            showTime, 
            timestamp: newTimeSec,
            isLastSelf: isSelf 
        }).trim();
        const newRow = tempDiv.firstElementChild;
        chatBody.appendChild(newRow);
        return newRow;
    };

    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML.replace(/\n/g, '<br>');
    };

    // Quản lý đính kèm nhiều ảnh và xem trước (Preview)
    const btnImage = chatForm?.querySelector('[data-chat-btn-image]');
    const fileInput = chatForm?.querySelector('[data-chat-file-input]');
    const previewContainer = chatForm?.querySelector('[data-chat-image-preview]');

    let selectedFiles = [];

    const renderPreviews = () => {
        if (!previewContainer) return;
        if (selectedFiles.length === 0) {
            previewContainer.innerHTML = '';
            previewContainer.style.display = 'none';
            return;
        }

        previewContainer.innerHTML = selectedFiles.map((file, idx) => {
            const objectUrl = URL.createObjectURL(file);
            return `
                <div class="chat-image-preview-item" data-index="${idx}">
                    <img src="${objectUrl}" alt="Xem trước" class="chat-image-preview-thumb">
                    <button type="button" class="chat-image-preview-close" data-index="${idx}" title="Hủy ảnh">&times;</button>
                </div>
            `;
        }).join('');
        previewContainer.style.display = 'flex';
    };

    btnImage?.addEventListener('click', () => {
        fileInput?.click();
    });

    fileInput?.addEventListener('change', () => {
        const files = Array.from(fileInput.files || []);
        if (!files.length) return;

        for (const file of files) {
            if (!file.type.startsWith('image/')) {
                alert(`Tệp "${file.name}" không phải là hình ảnh hợp lệ.`);
                continue;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert(`Hình ảnh "${file.name}" vượt quá dung lượng tối đa 5MB.`);
                continue;
            }

            if (selectedFiles.length >= 10) {
                alert('Bạn chỉ có thể gửi tối đa 10 hình ảnh mỗi lần.');
                break;
            }

            selectedFiles.push(file);
        }

        fileInput.value = '';
        renderPreviews();
        chatInput?.focus();
    });

    previewContainer?.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.chat-image-preview-close');
        if (!removeBtn) return;
        const index = parseInt(removeBtn.dataset.index, 10);
        if (!isNaN(index) && index >= 0 && index < selectedFiles.length) {
            selectedFiles.splice(index, 1);
            renderPreviews();
        }
    });

    // Gửi tin nhắn qua AJAX (Hỗ trợ Text & Nhiều file ảnh)
    chatForm?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const content = chatInput?.value?.trim();
        if (!content && selectedFiles.length === 0) return;

        if (submitBtn) submitBtn.disabled = true;

        try {
            const formData = new FormData();
            if (content) formData.append('content', content);
            selectedFiles.forEach((file) => {
                formData.append('images[]', file);
            });

            const response = await fetch(chatForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: formData,
            });

            const result = await response.json();

            if (!response.ok) {
                const errorMsg = result.errors
                    ? Object.values(result.errors).flat()[0]
                    : result.message || 'Không thể gửi tin nhắn.';
                alert(errorMsg);
                return;
            }

            if (result.success && result.data) {
                const newMsg = result.data;
                lastMessageId = Math.max(lastMessageId, newMsg.id);

                appendCustomerMessage(newMsg);

                chatInput.value = '';
                selectedFiles = [];
                renderPreviews();
                if (fileInput) fileInput.value = '';

                cleanOrderIdFromUrl();
                updateHeaderStatus(result.header_status);
                const currentSugg = chatContainer.querySelector('[data-order-suggestion]');
                if (currentSugg) {
                    currentSugg.remove();
                }

                scrollToBottom(true);

                // Hiển thị câu trả lời tự động từ Shop nếu có (FAQ auto-reply)
                if (result.reply) {
                    setTimeout(() => {
                        lastMessageId = Math.max(lastMessageId, result.reply.id);
                        appendCustomerMessage(result.reply);
                        scrollToBottom(true);
                    }, 350);
                }
            }
        } catch (err) {
            console.error('Lỗi gửi tin nhắn:', err);
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            chatInput?.focus();
        }
    });

    // =========================================================
    // XỬ LÝ GỢI Ý GỬI ĐƠN HÀNG KIỂU SHOPEE (ORDER SUGGESTION)
    // =========================================================
    const orderSuggestion = chatContainer.querySelector('[data-order-suggestion]');
    if (orderSuggestion) {
        const btnSendSuggestedOrder = orderSuggestion.querySelector('[data-send-suggested-order]');
        const btnDismissSuggestedOrder = orderSuggestion.querySelector('[data-dismiss-suggested-order]');

        btnDismissSuggestedOrder?.addEventListener('click', () => {
            cleanOrderIdFromUrl();
            orderSuggestion.style.transition = 'all 0.25s ease';
            orderSuggestion.style.opacity = '0';
            orderSuggestion.style.transform = 'translateY(10px)';
            setTimeout(() => {
                orderSuggestion.remove();
                chatInput?.focus();
            }, 250);
        });

        btnSendSuggestedOrder?.addEventListener('click', async () => {
            if (!chatForm) return;

            const orderId = btnSendSuggestedOrder.dataset.orderId || null;
            const orderCode = btnSendSuggestedOrder.dataset.orderCode || '';
            const orderTotal = btnSendSuggestedOrder.dataset.orderTotal || '';
            const orderStatus = btnSendSuggestedOrder.dataset.orderStatus || '';
            const productName = btnSendSuggestedOrder.dataset.productName || '';

            const content = `📦 [ĐƠN HÀNG #${orderCode}]\n• Sản phẩm: ${productName}\n• Tổng tiền: ${orderTotal}\n• Trạng thái: ${orderStatus}\n• Mã đơn hàng: #${orderCode}`;

            btnSendSuggestedOrder.disabled = true;
            btnSendSuggestedOrder.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Đang gửi...</span>';

            try {
                const res = await fetch(chatForm.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || chatForm.querySelector('input[name="_token"]')?.value || '',
                    },
                    body: JSON.stringify({ content, order_id: orderId }),
                });

                const result = await res.json();

                if (!res.ok) {
                    alert(result.message || 'Không thể gửi đơn hàng.');
                    btnSendSuggestedOrder.disabled = false;
                    btnSendSuggestedOrder.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Gửi đơn này</span>';
                    return;
                }

                if (result.success && result.data) {
                    cleanOrderIdFromUrl();
                    updateHeaderStatus(result.header_status);
                    const newMsg = result.data;
                    lastMessageId = Math.max(lastMessageId, newMsg.id);

                    appendCustomerMessage(newMsg);

                    orderSuggestion.style.transition = 'all 0.25s ease';
                    orderSuggestion.style.opacity = '0';
                    orderSuggestion.style.transform = 'translateY(10px)';
                    setTimeout(() => {
                        orderSuggestion.remove();
                        chatInput?.focus();
                    }, 250);

                    scrollToBottom(true);
                }
            } catch (err) {
                console.error('Lỗi gửi đơn hàng:', err);
                btnSendSuggestedOrder.disabled = false;
                btnSendSuggestedOrder.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Gửi đơn này</span>';
            }
        });
    }

    // =========================================================
    // XỬ LÝ CÂU HỎI THƯỜNG GẶP (FAQ) TẠI SIDEBAR & TỰ ĐỘNG TRẢ LỜI
    // =========================================================
    const faqCard = document.querySelector('[data-faq-card]');
    const faqToggleBtn = document.querySelector('[data-faq-toggle-btn]');
    const faqExtraContainer = document.querySelector('[data-faq-extra]');
    const faqListWrap = document.querySelector('[data-faq-list-wrap]');
    const faqToggleText = document.querySelector('[data-faq-toggle-text]');

    if (faqToggleBtn && faqExtraContainer) {
        faqToggleBtn.addEventListener('click', () => {
            const cardEl = faqCard || faqToggleBtn.closest('.customer-chat-card-faq');
            const isHidden = faqExtraContainer.style.display === 'none' || !faqExtraContainer.style.display;
            if (isHidden) {
                faqExtraContainer.style.display = 'block';
                cardEl?.classList.add('is-expanded');
                faqListWrap?.classList.add('is-scrollable');
                faqToggleBtn.setAttribute('aria-expanded', 'true');
                if (faqToggleText) faqToggleText.textContent = 'Thu gọn';
            } else {
                faqExtraContainer.style.display = 'none';
                cardEl?.classList.remove('is-expanded');
                faqListWrap?.classList.remove('is-scrollable');
                if (faqListWrap) faqListWrap.scrollTop = 0;
                faqToggleBtn.setAttribute('aria-expanded', 'false');
                if (faqToggleText) faqToggleText.textContent = 'Xem tất cả câu hỏi';
            }
        });
    }

    // Gửi câu hỏi FAQ và nhận phản hồi tự động từ hệ thống
    let isSendingFaq = false;
    const sendFaqQuestion = async (question) => {
        if (!chatForm || !question || !question.trim() || isSendingFaq) return;

        isSendingFaq = true;
        if (submitBtn) submitBtn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('content', question.trim());

            const response = await fetch(chatForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || chatForm.querySelector('input[name="_token"]')?.value || '',
                },
                body: formData,
            });

            const result = await response.json();

            if (!response.ok) {
                const errorMsg = result.errors
                    ? Object.values(result.errors).flat()[0]
                    : result.message || 'Không thể gửi câu hỏi.';
                alert(errorMsg);
                return;
            }

            if (result.success && result.data) {
                const customerMsg = result.data;
                lastMessageId = Math.max(lastMessageId, customerMsg.id);
                appendCustomerMessage(customerMsg);

                cleanOrderIdFromUrl();
                updateHeaderStatus(result.header_status);
                const currentSugg = chatContainer.querySelector('[data-order-suggestion]');
                if (currentSugg) {
                    currentSugg.remove();
                }
                scrollToBottom(true);

                // Hiển thị câu trả lời tự động từ Mật Ngọt Bear nếu có
                if (result.reply) {
                    setTimeout(() => {
                        lastMessageId = Math.max(lastMessageId, result.reply.id);
                        appendCustomerMessage(result.reply);
                        scrollToBottom(true);
                    }, 350);
                }
            }
        } catch (err) {
            console.error('Lỗi khi gửi câu hỏi FAQ:', err);
        } finally {
            isSendingFaq = false;
            if (submitBtn) submitBtn.disabled = false;
            chatInput?.focus();
        }
    };

    // Bắt sự kiện bấm vào bất kỳ câu hỏi FAQ nào
    document.addEventListener('click', (e) => {
        const faqTarget = e.target.closest('[data-faq-question]');
        if (faqTarget) {
            e.preventDefault();
            const question = faqTarget.dataset.faqQuestion;
            if (question) {
                sendFaqQuestion(question);
            }
        }
    });

    // Hiển thị lời chào từ Shop khi cuộc trò chuyện đã kết thúc
    const appendGreetingPromptIfNeeded = () => {
        if (!chatBody) return;
        const lastRow = chatBody.querySelector('.customer-chat-msg-row:last-child');
        if (lastRow && lastRow.hasAttribute('data-greeting-prompt')) {
            return;
        }

        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const timeStr = `${hours}:${minutes}`;

        const promptDiv = document.createElement('div');
        promptDiv.className = 'customer-chat-msg-row is-shop is-greeting-prompt';
        promptDiv.setAttribute('data-greeting-prompt', '1');
        promptDiv.innerHTML = `
            <img
                src="/images/auth/bear-hero.png"
                alt="Mật Ngọt Bear"
                class="customer-chat-msg-row__avatar"
                onerror="this.src='/images/customer/product-placeholder.png'"
            >
            <div class="customer-chat-msg-wrap">
                <div class="customer-chat-bubble">
                    <div class="chat-msg-text">
                        Xin chào bạn! 👋<br>
                        Mật Ngọt Bear rất vui được hỗ trợ bạn. Bạn cần tư vấn về sản phẩm, đơn hàng hay vấn đề nào khác ạ?
                    </div>
                    <div class="chat-msg-time">
                        <span>${timeStr}</span>
                    </div>
                </div>
            </div>
        `;
        chatBody.appendChild(promptDiv);
        scrollToBottom(true);
    };

    // Polling nhận tin nhắn mới định kỳ mỗi 4 giây
    const pollUrl = chatContainer.dataset.pollUrl;
    if (pollUrl) {
        setInterval(async () => {
            try {
                const url = new URL(pollUrl, window.location.origin);
                url.searchParams.set('after_id', lastMessageId);

                const res = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!res.ok) return;

                const json = await res.json();
                if (json.success) {
                    if (Array.isArray(json.data) && json.data.length > 0) {
                        let hasNew = false;
                        json.data.forEach((msg) => {
                            if (msg.id > lastMessageId) {
                                lastMessageId = Math.max(lastMessageId, msg.id);
                                appendCustomerMessage(msg);
                                hasNew = true;
                            }
                        });

                        if (hasNew) {
                            scrollToBottom(true);
                        }
                    }

                    // Cập nhật nhãn trạng thái ca hỗ trợ trên Header
                    if (json.header_status) {
                        updateHeaderStatus(json.header_status);
                    }

                    // Hiển thị lời chào từ Shop khi cuộc trò chuyện đã kết thúc
                    if (json.should_show_greeting) {
                        appendGreetingPromptIfNeeded();
                    }

                    // Cập nhật trạng thái "Đã xem" nếu tin nhắn cuối cùng của cả đoạn chat do chính mình gửi
                    if (json.is_last_msg_self && json.last_msg_seen) {
                        const lastStatusEl = chatBody.querySelector('.chat-msg-status');
                        if (lastStatusEl) {
                            lastStatusEl.setAttribute('data-status', 'seen');
                            lastStatusEl.textContent = 'Đã xem';
                        }
                    } else if (json.is_last_msg_self === false) {
                        const oldStatusEls = chatBody.querySelectorAll('.chat-msg-status');
                        oldStatusEls.forEach(el => el.remove());
                    }
                }
            } catch (e) {
                // Polling im lặng không làm gián đoạn người dùng
            }
        }, 4000);
    }
});
