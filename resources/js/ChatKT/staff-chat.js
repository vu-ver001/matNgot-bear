/**
 * STAFF & ADMIN SUPPORT CHAT JAVASCRIPT (MẬT NGỌT BEAR)
 * Author: Kim Tuyến
 */

document.addEventListener('DOMContentLoaded', () => {
    const supportWrapper = document.querySelector('[data-staff-support]');
    if (!supportWrapper) return;

    const chatStream = supportWrapper.querySelector('[data-staff-chat-stream]');
    const chatForm = supportWrapper.querySelector('[data-staff-chat-form]');
    const chatInput = supportWrapper.querySelector('[data-staff-chat-input]');
    const submitBtn = supportWrapper.querySelector('[data-staff-chat-submit]');

    const btnAccept = supportWrapper.querySelector('[data-btn-accept]');
    const btnHandover = supportWrapper.querySelector('[data-btn-handover]');
    const btnClose = supportWrapper.querySelector('[data-btn-close]');
    const btnReopen = supportWrapper.querySelector('[data-btn-reopen]');
    const btnTakeover = supportWrapper.querySelector('[data-btn-takeover]');
    const btnRevoke = supportWrapper.querySelector('[data-btn-revoke]');
    const btnOpenAssign = supportWrapper.querySelector('[data-btn-open-assign]');
    const assignModal = document.getElementById('assignStaffModal');
    const assignForm = document.getElementById('assignStaffForm');

    let currentCaseId = supportWrapper.dataset.currentCaseId ? parseInt(supportWrapper.dataset.currentCaseId, 10) : 0;
    let lastMessageId = 0;

    const existingMsgEls = chatStream ? chatStream.querySelectorAll('[data-msg-id]') : [];
    if (existingMsgEls.length > 0) {
        const lastEl = existingMsgEls[existingMsgEls.length - 1];
        lastMessageId = parseInt(lastEl.dataset.msgId, 10) || 0;
    }

    // Cuộn xuống cuối
    const scrollToBottom = (smooth = false) => {
        if (!chatStream) return;
        chatStream.scrollTo({
            top: chatStream.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto',
        });
    };

    scrollToBottom(false);

    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/\n/g, '<br>');
    };

    // Tạo HTML cho tin nhắn trong chat stream
    const renderStaffMessageHtml = (msg, options = {}) => {
        const isCustomer = Boolean(msg.is_customer);
        const rowClass = isCustomer ? 'is-customer' : 'is-staff';
        const groupPos = options.groupPos || 'pos-single';
        const showAvatar = options.showAvatar ?? true;
        const showTime = options.showTime ?? true;
        const timestampSec = options.timestamp || Math.floor(Date.now() / 1000);

        let avatarHtml = '';
        if (isCustomer) {
            if (showAvatar) {
                const rawName = (msg.sender_name || 'Khách hàng').trim();
                const initial = escapeHtml(rawName.charAt(0).toUpperCase() || 'K');
                const imgHtml = msg.sender_avatar
                    ? `<img src="${msg.sender_avatar}" alt="${escapeHtml(rawName)}" onerror="this.remove()">`
                    : '';
                avatarHtml = `<div class="staff-chat-msg-avatar">${imgHtml}<span>${initial}</span></div>`;
            } else {
                avatarHtml = `<div class="staff-chat-msg-avatar is-spacer" aria-hidden="true"></div>`;
            }
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

        let imgHtmlContent = '';
        if (images.length > 0) {
            const galleryClass = images.length === 1 ? 'is-single' : (images.length === 2 ? 'is-double' : 'is-grid');
            imgHtmlContent = `
                <div class="chat-msg-gallery ${galleryClass}">
                    ${images.map(url => `
                        <div class="chat-msg-image-wrap">
                            <img src="${escapeHtml(url)}" alt="Hình ảnh đính kèm" class="chat-msg-image" loading="lazy" onclick="window.open(this.src, '_blank')">
                        </div>
                    `).join('')}
                </div>
            `;
        }

        const textHtmlContent = msg.content ? `
            <div class="chat-msg-text">${formatBubbleContent(msg.content)}</div>
        ` : '';

        const timeHtmlContent = (showTime && dateStr) ? `
            <div class="staff-chat-time chat-msg-time">
                <span>${escapeHtml(dateStr)}</span>
            </div>
        ` : '';

        const isLastSelf = options.isLastSelf ?? false;
        const statusHtml = (!isCustomer && isLastSelf) ? `
            <div class="chat-msg-status staff-chat-status" data-status="${msg.is_read ? 'seen' : 'sent'}">
                ${msg.is_read ? 'Đã xem' : 'Đã gửi'}
            </div>
        ` : '';

        let bubbleHtmlContent = '';
        if (textHtmlContent) {
            bubbleHtmlContent = `
                <div class="staff-chat-bubble" title="${escapeHtml(fullDateTooltip)}">
                    ${textHtmlContent}
                    ${timeHtmlContent}
                </div>
            `;
        } else if (!imgHtmlContent) {
            bubbleHtmlContent = `
                <div class="staff-chat-bubble" title="${escapeHtml(fullDateTooltip)}">
                    ${timeHtmlContent}
                </div>
            `;
        } else if (timeHtmlContent) {
            bubbleHtmlContent = `
                <div class="staff-chat-time chat-msg-time is-outside">
                    <span>${escapeHtml(dateStr)}</span>
                </div>
            `;
        }

        const hasGalleryClass = images.length > 0 ? 'has-gallery' : '';

        return `
            <div class="staff-chat-msg-row ${rowClass} ${groupPos} ${hasGalleryClass}" 
                 data-msg-id="${msg.id}" 
                 data-sender-id="${msg.sender_id || ''}" 
                 data-is-customer="${isCustomer ? '1' : '0'}" 
                 data-timestamp="${timestampSec}">
                ${avatarHtml}
                <div class="staff-chat-msg-wrap">
                    ${imgHtmlContent}
                    ${bubbleHtmlContent}
                    ${statusHtml}
                </div>
            </div>
        `;
    };

    // Hàm chèn tin nhắn mới phía staff theo chuẩn gom nhóm Messenger / Zalo
    const appendStaffMessage = (msg) => {
        if (!chatStream) return null;

        const existingRows = chatStream.querySelectorAll('.staff-chat-msg-row');
        const lastRow = existingRows.length > 0 ? existingRows[existingRows.length - 1] : null;

        const isCustomer = Boolean(msg.is_customer);
        const newSenderId = String(msg.sender_id || '');
        const newTimeSec = msg.timestamp ? parseInt(msg.timestamp, 10) : Math.floor(Date.now() / 1000);

        let isConsecutive = false;

        if (lastRow) {
            const lastIsCustomer = lastRow.dataset.isCustomer === '1';
            const lastSenderId = String(lastRow.dataset.senderId || '');
            const lastTimeSec = parseInt(lastRow.dataset.timestamp, 10) || 0;
            const diffMinutes = lastTimeSec > 0 ? Math.abs(newTimeSec - lastTimeSec) / 60 : 0;

            const sameSender = (lastSenderId && newSenderId)
                ? (lastSenderId === newSenderId)
                : (lastIsCustomer === isCustomer);

            if (sameSender && diffMinutes <= 10) {
                isConsecutive = true;
                if (lastRow.classList.contains('pos-single')) {
                    lastRow.classList.remove('pos-single');
                    lastRow.classList.add('pos-first');
                } else if (lastRow.classList.contains('pos-last')) {
                    lastRow.classList.remove('pos-last');
                    lastRow.classList.add('pos-middle');
                }

                // Xóa avatar tin nhắn trước đó của khách (chuyển thành spacer để thẳng hàng)
                const lastAvatar = lastRow.querySelector('.staff-chat-msg-avatar:not(.is-spacer)');
                if (lastAvatar) {
                    const spacer = document.createElement('div');
                    spacer.className = 'staff-chat-msg-avatar is-spacer';
                    spacer.setAttribute('aria-hidden', 'true');
                    lastAvatar.replaceWith(spacer);
                }

                // Xóa giờ của tin nhắn trước đó (chỉ hiện ở tin cuối của cụm)
                const lastTimeEl = lastRow.querySelector('.staff-chat-time, .chat-msg-time');
                if (lastTimeEl) {
                    lastTimeEl.remove();
                }
            }
        }

        // Trạng thái "Đã gửi" / "Đã xem" CHỈ hiển thị ở tin nhắn cuối cùng của đoạn chat nếu tin nhắn đó do nhân viên / admin gửi
        const oldStatusEls = chatStream.querySelectorAll('.staff-chat-status, .chat-msg-status');
        oldStatusEls.forEach(el => el.remove());

        const groupPos = isConsecutive ? 'pos-last' : 'pos-single';
        const showAvatar = true;
        const showTime = true;

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = renderStaffMessageHtml(msg, { 
            groupPos, 
            showAvatar, 
            showTime, 
            timestamp: newTimeSec,
            isLastSelf: !isCustomer 
        }).trim();
        const newRow = tempDiv.firstElementChild;
        chatStream.appendChild(newRow);
        return newRow;
    };

    // Nhận xử lý case (Accept)
    btnAccept?.addEventListener('click', async () => {
        const actionUrl = btnAccept.dataset.acceptUrl;
        if (!actionUrl) return;

        btnAccept.disabled = true;
        try {
            const res = await fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            const data = await res.json();

            if (!res.ok) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Thông báo',
                        text: data.message || 'Cuộc hỗ trợ này đã được nhân viên khác tiếp nhận.',
                        confirmButtonColor: '#843c16',
                    }).then(() => window.location.reload());
                } else {
                    alert(data.message || 'Cuộc hỗ trợ này đã được nhân viên khác tiếp nhận.');
                    window.location.reload();
                }
                return;
            }

            window.location.reload();
        } catch (err) {
            console.error('Lỗi nhận case:', err);
            window.location.reload();
        } finally {
            btnAccept.disabled = false;
        }
    });

    // Bàn giao case (Handover)
    const allHandoverBtns = supportWrapper.querySelectorAll('[data-btn-handover]');
    allHandoverBtns.forEach((btn) => {
        btn.addEventListener('click', async () => {
            const actionUrl = btn.dataset.handoverUrl;
            if (!actionUrl) return;

            const confirmAction = async () => {
                btn.disabled = true;
                try {
                    const res = await fetch(actionUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        alert(data.message || 'Không thể bàn giao cuộc hỗ trợ.');
                        return;
                    }
                    window.location.reload();
                } catch (err) {
                    console.error('Lỗi bàn giao:', err);
                } finally {
                    btn.disabled = false;
                }
            };

            if (window.Swal) {
                Swal.fire({
                    title: 'Xác nhận bàn giao?',
                    text: 'Cuộc hỗ trợ sẽ quay lại hàng chờ để nhân viên khác tiếp quản.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#843c16',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Đồng ý bàn giao',
                    cancelButtonText: 'Hủy',
                }).then((res) => {
                    if (res.isConfirmed) confirmAction();
                });
            } else if (confirm('Bạn có chắc muốn bàn giao cuộc hỗ trợ này về hàng chờ?')) {
                confirmAction();
            }
        });
    });

    // Kết thúc hỗ trợ (Close)
    const allCloseBtns = supportWrapper.querySelectorAll('[data-btn-close]');
    allCloseBtns.forEach((btn) => {
        btn.addEventListener('click', async () => {
            const actionUrl = btn.dataset.closeUrl;
            if (!actionUrl) return;

            const confirmAction = async () => {
                btn.disabled = true;
                try {
                    const res = await fetch(actionUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        alert(data.message || 'Không thể kết thúc cuộc hỗ trợ.');
                        return;
                    }
                    window.location.reload();
                } catch (err) {
                    console.error('Lỗi kết thúc hỗ trợ:', err);
                } finally {
                    btn.disabled = false;
                }
            };

            if (window.Swal) {
                Swal.fire({
                    title: 'Kết thúc cuộc hỗ trợ?',
                    text: 'Sau khi kết thúc, khách hàng sẽ cần tạo yêu cầu mới nếu muốn hỏi thêm.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Đồng ý kết thúc',
                    cancelButtonText: 'Hủy',
                }).then((res) => {
                    if (res.isConfirmed) confirmAction();
                });
            } else if (confirm('Bạn có chắc muốn kết thúc cuộc hỗ trợ này?')) {
                confirmAction();
            }
        });
    });

    // Mở lại cuộc hỗ trợ (Reopen)
    btnReopen?.addEventListener('click', async () => {
        const actionUrl = btnReopen.dataset.reopenUrl;
        if (!actionUrl) return;

        btnReopen.disabled = true;
        try {
            const res = await fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
            const data = await res.json();
            if (!res.ok) {
                alert(data.message || 'Không thể mở lại cuộc hỗ trợ.');
                return;
            }
            window.location.reload();
        } catch (err) {
            console.error('Lỗi mở lại hỗ trợ:', err);
        } finally {
            btnReopen.disabled = false;
        }
    });

    // Tiếp quản case (Takeover - dành cho Admin)
    btnTakeover?.addEventListener('click', async () => {
        const actionUrl = btnTakeover.dataset.takeoverUrl;
        if (!actionUrl) return;

        const confirmAction = async () => {
            btnTakeover.disabled = true;
            try {
                const res = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Không thể tiếp quản cuộc hỗ trợ.');
                    return;
                }
                window.location.reload();
            } catch (err) {
                console.error('Lỗi tiếp quản:', err);
            } finally {
                btnTakeover.disabled = false;
            }
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Tiếp quản cuộc hỗ trợ?',
                text: 'Bạn sẽ trở thành người phụ trách chính và có thể trực tiếp chat với khách hàng.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Đồng ý tiếp quản',
                cancelButtonText: 'Hủy',
            }).then((res) => {
                if (res.isConfirmed) confirmAction();
            });
        } else if (confirm('Bạn có chắc muốn tiếp quản cuộc hỗ trợ này?')) {
            confirmAction();
        }
    });

    // Thu hồi case về WAITING (Revoke - dành cho Admin)
    btnRevoke?.addEventListener('click', async () => {
        const actionUrl = btnRevoke.dataset.revokeUrl;
        if (!actionUrl) return;

        const confirmAction = async () => {
            btnRevoke.disabled = true;
            try {
                const res = await fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });
                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Không thể thu hồi cuộc hỗ trợ.');
                    return;
                }
                window.location.reload();
            } catch (err) {
                console.error('Lỗi thu hồi:', err);
            } finally {
                btnRevoke.disabled = false;
            }
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Thu hồi cuộc hỗ trợ?',
                text: 'Cuộc hỗ trợ sẽ trở về trạng thái Chưa xử lý để nhân viên khác có thể tiếp nhận.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Đồng ý thu hồi',
                cancelButtonText: 'Hủy',
            }).then((res) => {
                if (res.isConfirmed) confirmAction();
            });
        } else if (confirm('Bạn có chắc muốn thu hồi cuộc hỗ trợ này về hàng chờ?')) {
            confirmAction();
        }
    });

    // Toggle dropdown thao tác phụ (More actions)
    const dropdownWrappers = supportWrapper.querySelectorAll('[data-staff-dropdown]');
    dropdownWrappers.forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-staff-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-staff-dropdown-menu]');
        if (!trigger || !menu) return;

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdown.classList.contains('is-open');
            dropdownWrappers.forEach(d => d.classList.remove('is-open'));
            if (!isOpen) {
                dropdown.classList.add('is-open');
            }
        });

        menu.addEventListener('click', () => {
            dropdown.classList.remove('is-open');
        });
    });

    document.addEventListener('click', (e) => {
        dropdownWrappers.forEach((dropdown) => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('is-open');
            }
        });
    });

    // Modal Chuyển nhân viên (Assign Modal - dành cho Admin)
    if (assignModal) {
        const openAssignBtns = supportWrapper.querySelectorAll('[data-btn-open-assign]');
        openAssignBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                assignModal.style.display = 'flex';
            });
        });

        const closeBtns = assignModal.querySelectorAll('[data-modal-close]');
        closeBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                assignModal.style.display = 'none';
            });
        });

        assignModal.addEventListener('click', (e) => {
            if (e.target === assignModal) {
                assignModal.style.display = 'none';
            }
        });

        assignForm?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = assignForm.querySelector('button[type="submit"]');
            const selectEl = assignForm.querySelector('select[name="staff_id"]');
            if (!selectEl || !selectEl.value) {
                alert('Vui lòng chọn nhân viên tiếp nhận.');
                return;
            }

            if (submitBtn) submitBtn.disabled = true;
            try {
                const res = await fetch(assignForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ staff_id: selectEl.value }),
                });

                const data = await res.json();
                if (!res.ok) {
                    alert(data.message || 'Không thể chuyển cuộc hỗ trợ.');
                    return;
                }

                window.location.reload();
            } catch (err) {
                console.error('Lỗi chuyển nhân viên:', err);
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

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

    // Tự động dọn sạch order_id trên thanh địa chỉ URL ngay khi trang load xong
    cleanOrderIdFromUrl();

    // Quản lý đính kèm nhiều ảnh và xem trước (Preview) cho Staff/Admin
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

    // Gửi tin nhắn phản hồi (Hỗ trợ Text & Nhiều file ảnh)
    chatForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        cleanOrderIdFromUrl();

        const content = chatInput?.value?.trim();
        if (!content && selectedFiles.length === 0) return;

        if (submitBtn) submitBtn.disabled = true;

        try {
            const formData = new FormData();
            if (content) formData.append('content', content);
            selectedFiles.forEach((file) => {
                formData.append('images[]', file);
            });

            const res = await fetch(chatForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: formData,
            });

            const result = await res.json();

            if (!res.ok) {
                const errorMsg = result.errors
                    ? Object.values(result.errors).flat()[0]
                    : result.message || 'Không thể gửi phản hồi.';
                alert(errorMsg);
                return;
            }

            if (result.success && result.data) {
                // Nếu là tin nhắn đầu tiên của phiên hỗ trợ mới, chuyển hướng về case_id để cập nhật cuộc trò chuyện vào danh sách bên trái
                if (result.data.is_first_message && result.data.case_id) {
                    const targetUrl = new URL(window.location.origin + window.location.pathname);
                    targetUrl.searchParams.set('tab', 'in_progress');
                    targetUrl.searchParams.set('case_id', result.data.case_id);
                    window.location.href = targetUrl.toString();
                    return;
                }

                // Nếu ca trước đó đã kết thúc, đang chờ xử lý, hoặc do staff khác xử lý (Admin nhắn xen vào tiếp quản), tự động reload để đồng bộ
                const isClosedNotice = supportWrapper.querySelector('.is-closed-notice');
                const isWaitingNotice = supportWrapper.querySelector('.is-waiting-notice');
                const isAssignedOther = supportWrapper.querySelector('.is-assigned-other');
                if (isClosedNotice || isWaitingNotice || isAssignedOther) {
                    window.location.reload();
                    return;
                }

                const newMsg = result.data;
                lastMessageId = Math.max(lastMessageId, newMsg.id);

                appendStaffMessage(newMsg);

                chatInput.value = '';
                selectedFiles = [];
                renderPreviews();
                if (fileInput) fileInput.value = '';

                scrollToBottom(true);
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
    const orderSuggestion = supportWrapper.querySelector('[data-order-suggestion]');
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
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
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

                    // Nếu là tin nhắn đầu tiên của phiên hỗ trợ mới, chuyển hướng về case_id để cập nhật cuộc trò chuyện vào danh sách bên trái
                    if (result.data.is_first_message && result.data.case_id) {
                        const targetUrl = new URL(window.location.origin + window.location.pathname);
                        targetUrl.searchParams.set('tab', 'in_progress');
                        targetUrl.searchParams.set('case_id', result.data.case_id);
                        window.location.href = targetUrl.toString();
                        return;
                    }

                    // Cập nhật "Mã đơn liên quan" ở Card 2
                    if (result.data.order_code) {
                        const relatedOrderVal = supportWrapper.querySelector('[data-related-order-code]');
                        if (relatedOrderVal) {
                            relatedOrderVal.textContent = '#' + result.data.order_code;
                            relatedOrderVal.setAttribute('data-tooltip', '#' + result.data.order_code);
                        }

                        // Cập nhật badge "Đơn liên quan" ở Card 3
                        const allOrderCards = supportWrapper.querySelectorAll('[data-order-card-code]');
                        allOrderCards.forEach(card => {
                            card.classList.remove('is-current');
                            const oldTag = card.querySelector('.staff-support-order-item-tag');
                            if (oldTag) oldTag.remove();

                            if (card.dataset.orderCardCode === result.data.order_code) {
                                card.classList.add('is-current');
                                const header = card.querySelector('.staff-support-order-item-header');
                                if (header && !header.querySelector('.staff-support-order-item-tag')) {
                                    const tag = document.createElement('span');
                                    tag.className = 'staff-support-order-item-tag';
                                    tag.setAttribute('data-tooltip', 'Đơn hàng liên quan đến cuộc hỗ trợ này');
                                    tag.textContent = 'Đơn liên quan';
                                    header.appendChild(tag);
                                }
                            }
                        });

                        // Cập nhật tag đơn liên quan trên danh sách case bên trái (nếu có item)
                        const currentCaseItem = supportWrapper.querySelector('.staff-support-case-item.is-active');
                        if (currentCaseItem) {
                            let orderTag = currentCaseItem.querySelector('.staff-support-case-order');
                            if (!orderTag) {
                                let bottomWrap = currentCaseItem.querySelector('.staff-support-case-bottom');
                                if (!bottomWrap) {
                                    bottomWrap = document.createElement('div');
                                    bottomWrap.className = 'staff-support-case-bottom';
                                    currentCaseItem.querySelector('.staff-support-case-info')?.appendChild(bottomWrap);
                                }
                                orderTag = document.createElement('span');
                                orderTag.className = 'staff-support-case-order';
                                bottomWrap.appendChild(orderTag);
                            }
                            orderTag.textContent = '#' + result.data.order_code;
                            orderTag.setAttribute('data-tooltip', 'Đơn hàng #' + result.data.order_code);
                        }
                    }

                    const newMsg = result.data;
                    lastMessageId = Math.max(lastMessageId, newMsg.id);

                    appendStaffMessage(newMsg);
                    scrollToBottom(true);

                    // Xóa bỏ các notice trạng thái cũ (waiting / closed / assigned-other) nếu có
                    // để khi nhân viên gõ tiếp tin nhắn tiếp theo, form không reload trang gây gián đoạn
                    const isClosedNotice = supportWrapper.querySelector('.is-closed-notice');
                    const isWaitingNotice = supportWrapper.querySelector('.is-waiting-notice');
                    const isAssignedOther = supportWrapper.querySelector('.is-assigned-other');
                    if (isClosedNotice) isClosedNotice.remove();
                    if (isWaitingNotice) isWaitingNotice.remove();
                    if (isAssignedOther) isAssignedOther.remove();

                    // Ẩn thanh gợi ý sau khi gửi thành công và focus ô chat để nhân viên nhắn tiếp
                    orderSuggestion.style.transition = 'all 0.25s ease';
                    orderSuggestion.style.opacity = '0';
                    orderSuggestion.style.transform = 'translateY(10px)';
                    setTimeout(() => {
                        orderSuggestion.remove();
                        chatInput?.focus();
                    }, 250);
                }
            } catch (err) {
                console.error('Lỗi gửi đơn hàng gợi ý:', err);
                btnSendSuggestedOrder.disabled = false;
                btnSendSuggestedOrder.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Gửi đơn này</span>';
            }
        });
    }

    // Polling tin nhắn mới của case
    const pollUrl = supportWrapper.dataset.pollUrl;
    if (pollUrl && currentCaseId > 0) {
        const pollCaseMessages = async () => {
            try {
                const url = new URL(pollUrl, window.location.origin);
                url.searchParams.set('after_id', lastMessageId);
                url.searchParams.set('is_hidden', document.hidden ? '1' : '0');

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
                                appendStaffMessage(msg);
                                hasNew = true;
                            }
                        });

                        if (hasNew) {
                            scrollToBottom(true);
                        }
                    }

                    // Cập nhật trạng thái "Đã xem" nếu tin nhắn cuối cùng của cả đoạn chat do staff/admin gửi
                    if (json.is_last_msg_self && json.last_msg_seen) {
                        const lastStatusEl = chatStream.querySelector('.staff-chat-msg-row:last-child .staff-chat-status, .staff-chat-msg-row:last-child .chat-msg-status');
                        if (lastStatusEl) {
                            lastStatusEl.setAttribute('data-status', 'seen');
                            lastStatusEl.textContent = 'Đã xem';
                        }
                    } else if (json.is_last_msg_self === false) {
                        const oldStatusEls = chatStream.querySelectorAll('.staff-chat-status, .chat-msg-status');
                        oldStatusEls.forEach(el => el.remove());
                    }
                }
            } catch (e) {
                // Polling im lặng
            }
        };

        setInterval(pollCaseMessages, 4000);

        // Khi người dùng chuyển lại tab hoặc focus vào cửa sổ chat, đồng bộ đã đọc
        window.addEventListener('focus', pollCaseMessages);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) pollCaseMessages();
        });
    }

    // =========================================================
    // ĐỒNG BỘ DANH SÁCH HỘI THOẠI REALTIME (BACKGROUND SYNC)
    // Tự động cập nhật danh sách và trạng thái Chưa đọc (chữ đậm, chấm xanh)
    // =========================================================
    const caseListEl = supportWrapper.querySelector('.staff-support-case-list');
    if (caseListEl) {
        setInterval(async () => {
            const searchInputEl = supportWrapper.querySelector('[data-staff-search-input]');
            // Nếu người dùng đang gõ tìm kiếm thì không cập nhật đè DOM
            if (searchInputEl && searchInputEl.value.trim()) return;

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('bg_sync', '1');

                const res = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) return;

                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const serverCaseList = doc.querySelector('.staff-support-case-list');
                if (serverCaseList && caseListEl.innerHTML !== serverCaseList.innerHTML) {
                    caseListEl.innerHTML = serverCaseList.innerHTML;
                }

                // Cập nhật số đếm badge các tab
                doc.querySelectorAll('.staff-support-tab-count').forEach((newBadge, idx) => {
                    const oldBadge = supportWrapper.querySelectorAll('.staff-support-tab-count')[idx];
                    if (oldBadge && newBadge && oldBadge.textContent !== newBadge.textContent) {
                        oldBadge.textContent = newBadge.textContent;
                    }
                });
            } catch (e) {
                // Đồng bộ ngầm im lặng
            }
        }, 5000);
    }

    // =========================================================
    // CHẶN NHÂN VIÊN KHÁC CLICK VÀO CASE ĐANG CÓ NGƯỜI XỬ LÝ
    // =========================================================
    document.addEventListener('click', (e) => {
        const lockedItem = e.target.closest('.staff-support-case-item.is-locked-other');
        if (!lockedItem) return;

        e.preventDefault();
        e.stopPropagation();

        const staffName = lockedItem.dataset.lockedBy || 'nhân viên khác';
        if (window.Swal) {
            Swal.fire({
                icon: 'warning',
                title: 'Đang có người xử lý',
                text: `Cuộc trò chuyện này đang do nhân viên "${staffName}" phụ trách. Bạn không có quyền truy cập.`,
                confirmButtonColor: '#843c16',
                confirmButtonText: 'Đã hiểu',
            });
        } else {
            alert(`Cuộc trò chuyện này đang do nhân viên "${staffName}" phụ trách. Bạn không có quyền truy cập.`);
        }
    });

    // ==========================================
    // TÌM KIẾM REALTIME (LIVE SEARCH - GÕ TỰ TÌM)
    // ==========================================
    const searchForm = supportWrapper.querySelector('[data-staff-search-form]');
    const searchInput = supportWrapper.querySelector('[data-staff-search-input]');
    const searchClearBtn = supportWrapper.querySelector('[data-search-clear]');
    const caseList = supportWrapper.querySelector('.staff-support-case-list');

    if (searchInput && caseList) {
        let debounceTimer = null;

        // Chuẩn hóa tiếng Việt không dấu để tìm kiếm thông minh
        const normalizeStr = (str) => {
            return (str || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .trim();
        };

        // 1. Lọc tức thì trên DOM ngay khi gõ từng ký tự (0ms lag)
        const filterDomItems = (keyword) => {
            const normalizedKeyword = normalizeStr(keyword);
            const caseItems = caseList.querySelectorAll('.staff-support-case-item');
            let visibleCount = 0;

            caseItems.forEach((item) => {
                const name = item.querySelector('.staff-support-case-name')?.textContent || '';
                const preview = item.querySelector('.staff-support-case-preview')?.textContent || '';
                const order = item.querySelector('.staff-support-case-order')?.textContent || '';
                const status = item.querySelector('.staff-support-status-badge')?.textContent || '';

                const combined = normalizeStr(`${name} ${preview} ${order} ${status}`);
                const isMatch = !normalizedKeyword || combined.includes(normalizedKeyword);

                item.style.display = isMatch ? '' : 'none';
                if (isMatch) visibleCount++;
            });

            // Hiển thị thông báo khi không có kết quả
            let noResultEl = caseList.querySelector('.staff-search-no-result');
            if (visibleCount === 0 && normalizedKeyword) {
                if (!noResultEl) {
                    noResultEl = document.createElement('div');
                    noResultEl.className = 'staff-search-no-result';
                    noResultEl.style.cssText = 'padding: 40px 20px; text-align: center; color: #a89488; font-size: 13px;';
                    noResultEl.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="32" height="32" style="margin: 0 auto 8px; color: #d5c4b8;">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" x2="16.65" y1="21" y2="16.65"/>
                        </svg>
                        <p style="margin: 0; font-weight: 500;">Không tìm thấy cuộc trò chuyện phù hợp</p>
                    `;
                    caseList.appendChild(noResultEl);
                }
                noResultEl.style.display = 'block';
            } else if (noResultEl) {
                noResultEl.style.display = 'none';
            }
        };

        // 2. Tìm kiếm sâu từ Server sau khi ngừng gõ (350ms debounce)
        const fetchServerSearch = async (keyword) => {
            if (!searchForm) return;
            const actionUrl = searchForm.action;
            const tabInput = searchForm.querySelector('input[name="tab"]');
            const tab = tabInput ? tabInput.value : 'all';

            const params = new URLSearchParams();
            params.set('tab', tab);
            if (keyword) params.set('q', keyword);

            const url = `${actionUrl}?${params.toString()}`;

            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) return;

                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const serverCaseList = doc.querySelector('.staff-support-case-list');
                if (serverCaseList) {
                    caseList.innerHTML = serverCaseList.innerHTML;
                }

                // Cập nhật số đếm badge các tab
                doc.querySelectorAll('.staff-support-tab-count').forEach((newBadge, idx) => {
                    const oldBadge = supportWrapper.querySelectorAll('.staff-support-tab-count')[idx];
                    if (oldBadge && newBadge) oldBadge.textContent = newBadge.textContent;
                });

                // Cập nhật URL trình duyệt không reload trang
                window.history.replaceState(null, '', url);
            } catch (err) {
                console.error('Lỗi tìm kiếm realtime:', err);
            }
        };

        // Lắng nghe sự kiện gõ từng ký tự
        searchInput.addEventListener('input', () => {
            const val = searchInput.value;
            if (searchClearBtn) {
                searchClearBtn.style.display = val.trim() ? 'flex' : 'none';
            }

            // Lọc tức thì trên DOM ngay lập tức
            filterDomItems(val);

            // Debounce tìm kiếm sâu qua server
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchServerSearch(val.trim());
            }, 350);
        });

        // Nút xóa tìm kiếm
        searchClearBtn?.addEventListener('click', () => {
            searchInput.value = '';
            searchClearBtn.style.display = 'none';
            searchInput.focus();
            filterDomItems('');
            fetchServerSearch('');
        });

        // Chặn submit form reload trang khi ấn Enter
        searchForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            clearTimeout(debounceTimer);
            fetchServerSearch(searchInput.value.trim());
        });
    }

    // ========================================================
    // SMART FLOATING TOOLTIP: HIỆN ĐỦ THÔNG TIN KHI DI CHUỘT VÀO NƠI BỊ THU GỌN
    // ========================================================
    let smartTooltipEl = null;
    let tooltipTimer = null;
    let currentTooltipTarget = null;
    let originalTitle = '';

    const getSmartTooltip = () => {
        if (!smartTooltipEl) {
            smartTooltipEl = document.createElement('div');
            smartTooltipEl.className = 'staff-smart-tooltip';
            document.body.appendChild(smartTooltipEl);
        }
        return smartTooltipEl;
    };

    const showSmartTooltip = (target, text) => {
        if (!text) return;
        const tooltip = getSmartTooltip();
        tooltip.textContent = text;
        tooltip.classList.add('is-visible');

        const rect = target.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();

        let top = rect.top - tooltipRect.height - 8;
        let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);

        if (top < 10) {
            top = rect.bottom + 8;
        }

        if (left < 12) left = 12;
        if (left + tooltipRect.width > window.innerWidth - 12) {
            left = window.innerWidth - tooltipRect.width - 12;
        }

        tooltip.style.top = `${top}px`;
        tooltip.style.left = `${left}px`;
    };

    const hideSmartTooltip = () => {
        clearTimeout(tooltipTimer);
        if (smartTooltipEl) {
            smartTooltipEl.classList.remove('is-visible');
        }
        if (currentTooltipTarget && originalTitle) {
            currentTooltipTarget.setAttribute('title', originalTitle);
            originalTitle = '';
        }
        currentTooltipTarget = null;
    };

    document.addEventListener('mouseover', (e) => {
        let el = e.target;
        let validTarget = null;
        let fullText = '';

        while (el && el !== document.body && el !== document.documentElement) {
            if (el.matches(
                '.staff-support-chat-customer-name, ' +
                '.staff-messenger-handler, ' +
                '.staff-support-case-name, ' +
                '.staff-support-case-preview, ' +
                '.staff-support-case-order, ' +
                '.staff-customer-profile-name, ' +
                '.staff-customer-detail-row span, ' +
                '.staff-support-case-meta-val, ' +
                '.staff-support-order-item-code, ' +
                '.staff-support-order-item-btn, ' +
                '.staff-support-order-item-tag, ' +
                '[data-tooltip]'
            )) {
                const isClipped = (el.offsetWidth < el.scrollWidth) || (el.offsetHeight < el.scrollHeight);
                const hasExplicitTooltip = Boolean(el.dataset.tooltip) || Boolean(el.getAttribute('title'));

                if (hasExplicitTooltip) {
                    validTarget = el;
                    fullText = el.dataset.tooltip || el.getAttribute('title') || '';
                    break;
                }

                if (isClipped) {
                    validTarget = el;
                    fullText = el.innerText.trim();
                    break;
                }
            }
            el = el.parentElement;
        }

        if (!validTarget || !fullText) return;

        currentTooltipTarget = validTarget;
        if (validTarget.hasAttribute('title')) {
            originalTitle = validTarget.getAttribute('title');
            validTarget.removeAttribute('title'); // Tạm thời xóa title để tránh browser hiện popup xám mặc định đè lên
        }

        clearTimeout(tooltipTimer);
        tooltipTimer = setTimeout(() => {
            showSmartTooltip(validTarget, fullText);
        }, 120);
    });

    document.addEventListener('mouseout', (e) => {
        const toEl = e.relatedTarget;
        if (currentTooltipTarget && (!toEl || !currentTooltipTarget.contains(toEl))) {
            hideSmartTooltip();
        }
    });

    window.addEventListener('scroll', hideSmartTooltip, true);
});
