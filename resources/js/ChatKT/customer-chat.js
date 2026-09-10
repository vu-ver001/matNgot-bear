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
    const faqItems = document.querySelectorAll('[data-faq-question]');

    let lastMessageId = 0;
    const existingMsgEls = chatBody ? chatBody.querySelectorAll('[data-msg-id]') : [];
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

    // Xử lý bấm vào câu hỏi thường gặp (FAQ) -> tự động điền vào ô chat và focus
    faqItems.forEach((item) => {
        item.addEventListener('click', () => {
            const question = item.dataset.faqQuestion;
            if (chatInput && question) {
                chatInput.value = question;
                chatInput.focus();
            }
        });
    });

    // Tạo HTML cho tin nhắn mới
    const renderMessageHtml = (msg) => {
        const isShop = !msg.is_self;
        const rowClass = isShop ? 'is-shop' : 'is-customer';
        const avatarHtml = isShop
            ? `<img src="/images/auth/bear-hero.png" alt="Mật Ngọt Bear" class="customer-chat-msg-row__avatar">`
            : '';

        const checkIconHtml = !isShop
            ? `<span class="customer-chat-check-icon" title="${msg.is_read ? 'Đã xem' : 'Đã gửi'}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                    <path d="M18 6 7 17l-5-5"/>
                    <path d="m22 10-7.5 7.5L13 16"/>
                </svg>
               </span>`
            : '';

        const dateStr = msg.sent_at || '';

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
                                <i class="fa-solid fa-box"></i> Đơn hàng #${escapeHtml(code)}
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

        return `
            <div class="customer-chat-msg-row ${rowClass}" data-msg-id="${msg.id}">
                ${avatarHtml}
                <div class="customer-chat-msg-wrap">
                    <div class="customer-chat-bubble">
                        ${formatBubbleContent(msg.content)}
                    </div>
                    <div class="customer-chat-meta">
                        <span>${dateStr}</span>
                        ${checkIconHtml}
                    </div>
                </div>
            </div>
        `;
    };

    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/\n/g, '<br>');
    };

    // Gửi tin nhắn qua AJAX
    chatForm?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const content = chatInput?.value?.trim();
        if (!content) return;

        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await fetch(chatForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ content }),
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

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = renderMessageHtml(newMsg).trim();
                const node = tempDiv.firstElementChild;
                chatBody.appendChild(node);

                chatInput.value = '';
                scrollToBottom(true);
            }
        } catch (err) {
            console.error('Lỗi gửi tin nhắn:', err);
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            chatInput?.focus();
        }
    });

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
                if (json.success && Array.isArray(json.data) && json.data.length > 0) {
                    let hasNew = false;
                    json.data.forEach((msg) => {
                        if (msg.id > lastMessageId) {
                            lastMessageId = Math.max(lastMessageId, msg.id);
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = renderMessageHtml(msg).trim();
                            chatBody.appendChild(tempDiv.firstElementChild);
                            hasNew = true;
                        }
                    });

                    if (hasNew) {
                        scrollToBottom(true);
                    }
                }
            } catch (e) {
                // Polling im lặng không làm gián đoạn người dùng
            }
        }, 4000);
    }
});
