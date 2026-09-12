/* ==========================================================================
   Admin Voucher Index Page Logic - Mật Ngọt Bear
   ========================================================================== */

export function vouchersList() {
    return {
        restoreModalOpen: false,
        restoreData: {
            id: null,
            code: '',
            used_count: 0,
            usage_limit: 0,
            end_date_formatted: '',
            new_end_date: '',
            new_usage_limit: 0,
            is_expired: false,
            is_out_of_stock: false,
            action: '',
        },

        openRestoreModal(data) {
            const now = new Date();
            let initialDate = new Date();
            initialDate.setDate(now.getDate() + 7); // Mặc định gia hạn +7 ngày

            if (data.end_date_raw) {
                const parsed = new Date(data.end_date_raw.replace(/-/g, '/'));
                if (!isNaN(parsed.getTime()) && parsed > now) {
                    initialDate = parsed;
                }
            }

            const pad = (n) => String(n).padStart(2, '0');
            const initialDateStr = `${initialDate.getFullYear()}-${pad(initialDate.getMonth() + 1)}-${pad(initialDate.getDate())} ${pad(initialDate.getHours())}:${pad(initialDate.getMinutes())}`;

            this.restoreData = {
                id: data.id,
                code: data.code,
                used_count: data.used_count || 0,
                usage_limit: data.usage_limit || 0,
                end_date_formatted: data.end_date_formatted || '---',
                new_end_date: initialDateStr,
                new_usage_limit: data.usage_limit || 0,
                is_expired: !!data.is_expired,
                is_out_of_stock: !!data.is_out_of_stock,
                action: data.action || '',
            };
            this.restoreModalOpen = true;

            this.$nextTick(() => {
                window.dispatchEvent(new CustomEvent('set-datetime-end_date', { detail: initialDateStr }));
            });
        },

        closeRestoreModal() {
            this.restoreModalOpen = false;
        },

        addDaysToEndDate(days) {
            const now = new Date();
            let baseDate = new Date();
            const hiddenInput = document.querySelector('input[name="end_date"]');
            if (hiddenInput && hiddenInput.value) {
                const parsed = new Date(hiddenInput.value.replace(/-/g, '/'));
                if (!isNaN(parsed.getTime()) && parsed > now) {
                    baseDate = parsed;
                }
            }
            baseDate.setDate(baseDate.getDate() + days);
            const pad = (n) => String(n).padStart(2, '0');
            const formatted = `${baseDate.getFullYear()}-${pad(baseDate.getMonth() + 1)}-${pad(baseDate.getDate())} ${pad(baseDate.getHours())}:${pad(baseDate.getMinutes())}`;
            this.restoreData.new_end_date = formatted;
            window.dispatchEvent(new CustomEvent('set-datetime-end_date', { detail: formatted }));
        },

        submitRestoreForm(e) {
            const hiddenInput = document.querySelector('input[name="end_date"]');
            const val = hiddenInput ? hiddenInput.value : '';
            if (val) {
                const selected = new Date(val.replace(/-/g, '/'));
                const now = new Date();
                if (selected <= now) {
                    e.preventDefault();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Thời gian không hợp lệ',
                            html: 'Thời gian kết thúc gia hạn phải ở <strong>tương lai</strong> (sau thời điểm hiện tại).<br><span style="font-size: 13px; color: #DC2626; margin-top: 6px; display: inline-block;">Vui lòng không chọn thời gian trong quá khứ hoặc hiện tại!</span>',
                            icon: 'warning',
                            confirmButtonText: 'Đã hiểu',
                            confirmButtonColor: '#E08A1E',
                            background: '#FAF6F0',
                            color: '#2E190E',
                            customClass: {
                                popup: 'rounded-3xl border-2 border-[#EBDDCD]',
                                confirmButton: 'rounded-xl font-bold px-5 py-2.5 shadow-md'
                            }
                        });
                    } else {
                        alert('Thời gian kết thúc gia hạn phải ở tương lai, không được chọn thời gian trong quá khứ hoặc hiện tại!');
                    }
                    return false;
                }
            }
            return true;
        },

        addLimit(amount) {
            const current = parseInt(this.restoreData.new_usage_limit) || parseInt(this.restoreData.used_count) || 0;
            this.restoreData.new_usage_limit = current + amount;
        },

        copyCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: {
                        type: 'success',
                        title: 'Đã sao chép mã',
                        message: `Mã voucher [${code}] đã được sao chép vào bộ nhớ tạm!`
                    }
                }));
            }).catch(() => {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: {
                        type: 'error',
                        title: 'Không thể sao chép',
                        message: 'Trình duyệt không hỗ trợ sao chép tự động.'
                    }
                }));
            });
        },

        confirmDelete(code, formId) {
            this.confirmSoftDelete(code, formId);
        },

        confirmSoftDelete(code, formId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Xóa voucher?',
                    html: `Bạn có chắc muốn xóa voucher <strong style="color: #5C3219;">[${code}]</strong>?<br><span style="font-size: 13px; color: #786B61; margin-top: 6px; display: inline-block;">Hệ thống sẽ <strong>xóa mềm</strong> (lưu vết CSDL) để bảo toàn nguyên vẹn lịch sử đơn hàng và đối soát của khách hàng.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận xóa mềm',
                    cancelButtonText: 'Hủy bỏ',
                    confirmButtonColor: '#DC2626',
                    cancelButtonColor: '#8E8076',
                    background: '#FAF6F0',
                    color: '#2E190E',
                    customClass: {
                        popup: 'rounded-3xl border-2 border-[#EBDDCD]',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 shadow-md',
                        cancelButton: 'rounded-xl font-bold px-5 py-2.5'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(formId);
                        if (form) form.submit();
                    }
                });
            } else {
                if (confirm(`Bạn có chắc chắn muốn xóa mềm voucher [${code}]? Dữ liệu lịch sử khách hàng sẽ được bảo toàn nguyên vẹn.`)) {
                    const form = document.getElementById(formId);
                    if (form) form.submit();
                }
            }
        },

        confirmRestore(code, formId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Khôi phục voucher?',
                    html: `Bạn có muốn khôi phục voucher <strong style="color: #5C3219;">[${code}]</strong> trở lại hoạt động bình thường không?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Đồng ý khôi phục',
                    cancelButtonText: 'Hủy bỏ',
                    confirmButtonColor: '#10B981',
                    cancelButtonColor: '#8E8076',
                    background: '#FAF6F0',
                    color: '#2E190E',
                    customClass: {
                        popup: 'rounded-3xl border-2 border-[#EBDDCD]',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 shadow-md',
                        cancelButton: 'rounded-xl font-bold px-5 py-2.5'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(formId);
                        if (form) form.submit();
                    }
                });
            } else {
                if (confirm(`Bạn có muốn khôi phục voucher [${code}] không?`)) {
                    const form = document.getElementById(formId);
                    if (form) form.submit();
                }
            }
        },

        confirmForceDelete(code, formId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Xóa vĩnh viễn voucher?',
                    html: `Voucher <strong style="color: #E53E3E;">[${code}]</strong> chưa từng có đơn hàng nào sử dụng.<br><span style="font-size: 13px; color: #E53E3E; font-weight: bold; margin-top: 6px; display: inline-block;">Thao tác này sẽ xóa hoàn toàn khỏi cơ sở dữ liệu và không thể hoàn tác!</span>`,
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận xóa vĩnh viễn',
                    cancelButtonText: 'Hủy bỏ',
                    confirmButtonColor: '#DC2626',
                    cancelButtonColor: '#8E8076',
                    background: '#FAF6F0',
                    color: '#2E190E',
                    customClass: {
                        popup: 'rounded-3xl border-2 border-[#EBDDCD]',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 shadow-md',
                        cancelButton: 'rounded-xl font-bold px-5 py-2.5'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById(formId);
                        if (form) form.submit();
                    }
                });
            } else {
                if (confirm(`Bạn có chắc muốn xóa vĩnh viễn voucher [${code}]?`)) {
                    const form = document.getElementById(formId);
                    if (form) form.submit();
                }
            }
        },

        async toggleVoucherStatus(voucherId, toggleUrl, code, usedCount) {
            const btn = document.getElementById(`toggle-btn-${voucherId}`);
            if (!btn || btn.disabled) return;

            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.pointerEvents = 'none';

            try {
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const tokenInput = document.querySelector('input[name="_token"]');
                const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : (tokenInput ? tokenInput.value : '');

                const response = await fetch(toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        _method: 'PATCH'
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    const isNowActive = data.status === 'ACTIVE';

                    // 1. Cập nhật toggle switch class & title
                    if (isNowActive) {
                        btn.classList.remove('is-inactive');
                        btn.classList.add('is-active');
                        btn.setAttribute('title', 'Bấm để vô hiệu hóa');
                    } else {
                        btn.classList.remove('is-active');
                        btn.classList.add('is-inactive');
                        btn.setAttribute('title', 'Bấm để kích hoạt');
                    }

                    // 2. Cập nhật Badge Trạng Thái
                    const badgeContainer = document.getElementById(`voucher-badge-${voucherId}`);
                    if (badgeContainer) {
                        if (!isNowActive) {
                            badgeContainer.innerHTML = `
                                <span class="bg-[#F1F5F9] text-[#64748B] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all duration-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#64748B] shrink-0"></span> Vô hiệu hóa
                                </span>
                            `;
                        } else if (data.is_expired || data.is_out_of_stock) {
                            badgeContainer.innerHTML = `
                                <span class="bg-[#FFEBEE] text-[#C62828] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all duration-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#C62828] shrink-0"></span> Đã hết hạn
                                </span>
                            `;
                        } else if (data.is_upcoming) {
                            badgeContainer.innerHTML = `
                                <span class="bg-[#FFF3E0] text-[#EF6C00] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all duration-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#EF6C00] shrink-0"></span> Sắp diễn ra
                                </span>
                            `;
                        } else {
                            badgeContainer.innerHTML = `
                                <span class="bg-[#E8F5E9] text-[#2E7D32] text-xs font-bold px-2 py-0.5 rounded-md inline-flex items-center gap-1.5 whitespace-nowrap transition-all duration-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D32] shrink-0"></span> Đang diễn ra
                                </span>
                            `;
                        }
                    }

                    // 3. Cập nhật nút xóa trong cột thao tác nếu có
                    const deleteContainer = document.getElementById(`voucher-actions-delete-${voucherId}`);
                    if (deleteContainer && usedCount > 0) {
                        const activeOrders = parseInt(deleteContainer.getAttribute('data-active-orders') || '0');
                        if (activeOrders === 0) {
                            const self = this;
                            if (isNowActive && data.is_running) {
                                deleteContainer.innerHTML = `
                                    <button type="button"
                                        class="text-gray-300 hover:text-gray-400 p-1.5 rounded-lg hover:bg-gray-100 transition cursor-not-allowed"
                                        title="Không thể xóa: Voucher đang diễn ra và đã có ${usedCount} lượt dùng. Vui lòng chuyển công tắc sang 'Vô hiệu hóa' trước khi xóa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                `;
                                const btnCannot = deleteContainer.querySelector('button');
                                if (btnCannot) {
                                    btnCannot.addEventListener('click', () => self.alertCannotDeleteRunning(code, usedCount));
                                }
                            } else {
                                deleteContainer.innerHTML = `
                                    <button type="button"
                                        class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition cursor-pointer"
                                        title="Xóa voucher (xóa mềm bảo toàn dữ liệu)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                `;
                                const btnCanDelete = deleteContainer.querySelector('button');
                                if (btnCanDelete) {
                                    btnCanDelete.addEventListener('click', () => self.confirmSoftDelete(code, `delete-form-${voucherId}`));
                                }
                            }
                        }
                    }

                    // 4. Cập nhật thẻ thống kê (Stats Cards)
                    if (data.stats) {
                        const elRunning = document.getElementById('stat-running');
                        if (elRunning) elRunning.textContent = data.stats.running;
                        const elInactive = document.getElementById('stat-inactive');
                        if (elInactive) elInactive.textContent = data.stats.inactive;
                        const elExpired = document.getElementById('stat-expired');
                        if (elExpired) elExpired.textContent = data.stats.expired;
                        const elTotal = document.getElementById('stat-total');
                        if (elTotal) elTotal.textContent = data.stats.total;
                    }

                    // 5. Bắn thông báo Toast mượt mà ở góc trên bên phải
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: {
                            type: isNowActive ? 'success' : 'warning',
                            title: isNowActive ? 'Kích hoạt thành công' : 'Đã vô hiệu hóa',
                            message: data.message
                        }
                    }));
                }
            } catch (err) {
                console.error('Error toggling voucher status:', err);
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: {
                        type: 'error',
                        title: 'Lỗi',
                        message: 'Không thể cập nhật trạng thái voucher. Vui lòng thử lại!'
                    }
                }));
            } finally {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
            }
        },

        toggleStatus(toggleUrl, formId) {
            const form = document.getElementById(formId);
            if (form) {
                form.submit();
            } else {
                window.location.href = toggleUrl;
            }
        },

        alertCannotDeleteRunning(code, usedCount) {
            const countText = usedCount ? ` và đã có <strong>${usedCount} lượt dùng</strong>` : '';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Chưa thể xóa voucher đang diễn ra',
                    html: `Voucher <strong style="color: #5C3219;">[${code}]</strong> hiện đang trong thời gian diễn ra${countText}.<br><span style="font-size: 13px; color: #786B61; margin-top: 6px; display: inline-block;">Để xóa mềm mã này, bạn vui lòng gạt công tắc trạng thái sang <strong>"Vô hiệu hóa"</strong> trước khi xóa.</span>`,
                    icon: 'warning',
                    confirmButtonText: 'Đã hiểu',
                    confirmButtonColor: '#E08A1E',
                    background: '#FAF6F0',
                    color: '#2E190E',
                    customClass: {
                        popup: 'rounded-3xl border-2 border-[#EBDDCD]',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 shadow-md'
                    }
                });
            } else {
                alert(`Không thể xóa voucher [${code}] khi đang diễn ra và đã có lượt dùng. Vui lòng chuyển trạng thái sang Vô hiệu hóa trước khi xóa!`);
            }
        },

        alertCannotDeleteActive(code, count) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Không thể xóa voucher',
                    html: `Voucher <strong style="color: #5C3219;">[${code}]</strong> hiện đang được áp dụng cho <strong style="color: #DC2626;">${count} đơn hàng</strong> chưa hoàn tất.<br><span style="font-size: 13px; color: #786B61; margin-top: 6px; display: inline-block;">Vui lòng xử lý hoàn tất hoặc hủy các đơn hàng liên quan trước khi xóa voucher.</span>`,
                    icon: 'warning',
                    confirmButtonText: 'Đã hiểu',
                    confirmButtonColor: '#E08A1E',
                    background: '#FAF6F0',
                    color: '#2E190E',
                    customClass: {
                        popup: 'rounded-3xl border-2 border-[#EBDDCD]',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 shadow-md'
                    }
                });
            } else {
                alert(`Không thể xóa voucher [${code}] vì đang có ${count} đơn hàng chưa hoàn tất áp dụng mã này!`);
            }
        },

        alertCannotDeleteNotExhausted(code, used, limit) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Không thể xóa voucher',
                    html: `Voucher <strong style="color: #5C3219;">[${code}]</strong> đã phát sinh đơn hàng và <strong style="color: #E08A1E;">chưa sử dụng hết lượt</strong> (${used}/${limit} lượt).<br><span style="font-size: 13px; color: #786B61; margin-top: 6px; display: inline-block;">Theo quy định hệ thống: chỉ voucher <strong>đã sử dụng hết lượt</strong> mới được phép xóa mềm, hoặc voucher <strong>chưa có đơn hàng nào</strong> mới được xóa vĩnh viễn.</span>`,
                    icon: 'info',
                    confirmButtonText: 'Đã hiểu',
                    confirmButtonColor: '#5C3219',
                    background: '#FAF6F0',
                    color: '#2E190E',
                    customClass: {
                        popup: 'rounded-3xl border-2 border-[#EBDDCD]',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 shadow-md'
                    }
                });
            } else {
                alert(`Không thể xóa voucher [${code}] vì mã đã phát sinh đơn hàng và chưa sử dụng hết lượt (${used}/${limit})!`);
            }
        },

        alertCannotSoftDelete(code, count) {
            this.alertCannotDeleteActive(code, count);
        },

        alertCannotForceDelete(code, count) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Không thể xóa vĩnh viễn',
                    html: `Voucher <strong style="color: #5C3219;">[${code}]</strong> đã phát sinh <strong style="color: #DC2626;">${count} đơn hàng</strong> trong hệ thống.<br><span style="font-size: 13px; color: #786B61; margin-top: 6px; display: inline-block;">Để bảo toàn lịch sử hóa đơn, đối soát tài chính và quyền lợi khách hàng, voucher chỉ được lưu trữ dưới dạng xóa mềm chứ không thể xóa vĩnh viễn.</span>`,
                    icon: 'info',
                    confirmButtonText: 'Đã hiểu',
                    confirmButtonColor: '#5C3219',
                    background: '#FAF6F0',
                    color: '#2E190E',
                    customClass: {
                        popup: 'rounded-3xl border-2 border-[#EBDDCD]',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 shadow-md'
                    }
                });
            } else {
                alert(`Không thể xóa vĩnh viễn voucher [${code}] vì đã có ${count} đơn hàng từng áp dụng mã này. Cần lưu lại để bảo toàn dữ liệu!`);
            }
        }
    };
}
