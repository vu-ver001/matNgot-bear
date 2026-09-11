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
                    html: `Bạn có chắc muốn xóa voucher <strong style="color: #5C3219;">[${code}]</strong>?<br><span style="font-size: 13px; color: #786B61; margin-top: 6px; display: inline-block;">Voucher sẽ được chuyển sang trạng thái xóa mềm để lưu vết lịch sử hóa đơn. Bạn có thể khôi phục lại bất kỳ lúc nào.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận xóa',
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
                if (confirm(`Bạn có chắc chắn muốn xóa mềm voucher [${code}]?`)) {
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

        toggleStatus(toggleUrl, formId) {
            const form = document.getElementById(formId);
            if (form) {
                form.submit();
            } else {
                window.location.href = toggleUrl;
            }
        },

        alertCannotDeleteRunning(code) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Không thể xóa voucher đang diễn ra',
                    html: `Voucher <strong style="color: #5C3219;">[${code}]</strong> hiện đang trong thời gian áp dụng (Đang diễn ra).<br><span style="font-size: 13px; color: #786B61; margin-top: 6px; display: inline-block;">Để xóa mềm voucher này, bạn vui lòng chuyển công tắc trạng thái sang <strong>"Vô hiệu hóa"</strong> trước khi xóa.</span>`,
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
                alert(`Không thể xóa voucher [${code}] khi đang diễn ra. Vui lòng chuyển trạng thái sang Vô hiệu hóa trước khi xóa!`);
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
