/* ==========================================================================
   Admin Voucher Index Page Logic - Mật Ngọt Bear
   ========================================================================== */

export function vouchersList() {
    return {
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
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Xóa mã voucher?',
                    html: `Bạn có chắc chắn muốn chuyển voucher <strong style="color: #5C3219;">[${code}]</strong> vào thùng rác không?<br><span style="font-size: 13px; color: #8E8076;">Voucher sẽ tạm ẩn khỏi shop, lịch sử mua hàng của khách hàng vẫn được bảo toàn và bạn có thể khôi phục bất cứ lúc nào.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Đồng ý xóa',
                    cancelButtonText: 'Hủy bỏ',
                    confirmButtonColor: '#E53E3E',
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
                if (confirm(`Bạn có chắc chắn muốn xóa voucher [${code}]?`)) {
                    const form = document.getElementById(formId);
                    if (form) form.submit();
                }
            }
        },

        confirmRestore(code, formId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Khôi phục voucher?',
                    html: `Bạn có muốn khôi phục voucher <strong style="color: #5C3219;">[${code}]</strong> trở lại danh sách hoạt động không?`,
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
                    html: `Bạn có chắc chắn muốn xóa vĩnh viễn voucher <strong style="color: #E53E3E;">[${code}]</strong> khỏi cơ sở dữ liệu không?<br><span style="font-size: 13px; color: #E53E3E; font-weight: bold;">Hành động này không thể hoàn tác!</span>`,
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
        }
    };
}
