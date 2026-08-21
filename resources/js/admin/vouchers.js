export function voucherForm(initialData = {}) {
    return {
        code: initialData.code || '',
        voucher_type: initialData.voucher_type || 'ORDER',
        apply_scope: initialData.apply_scope || 'ALL',
        selectedCategories: initialData.selectedCategories || [],
        selectedProducts: initialData.selectedProducts || [],
        discount_type: initialData.discount_type || 'PERCENTAGE',
        discount_value: initialData.discount_value || '',
        min_order_value: initialData.min_order_value || 0,
        max_discount_value: initialData.max_discount_value || '',
        start_date: initialData.start_date || '',
        end_date: initialData.end_date || '',
        usage_limit: initialData.usage_limit || '',
        used_count: initialData.used_count || 0,
        status: initialData.status || 'ACTIVE',
        searchProduct: '',
        filterCategory: '',

        init() {
            window.addEventListener('datetime-updated-start_date', (e) => {
                if (e.detail) this.start_date = e.detail;
            });
            window.addEventListener('datetime-updated-end_date', (e) => {
                if (e.detail) this.end_date = e.detail;
            });
        },

        matchesProduct(id, name, catId) {
            if (this.filterCategory && catId != this.filterCategory) {
                return false;
            }
            if (this.searchProduct.trim()) {
                const q = this.searchProduct.trim().toLowerCase();
                return name.includes(q);
            }
            return true;
        },

        onVoucherTypeChange() {
            if (this.code) {
                if (this.voucher_type === 'ORDER' && this.code.startsWith('SHIP-')) {
                    this.code = this.code.replace(/^SHIP-/, 'BEAR-');
                } else if (this.voucher_type === 'SHIPPING' && this.code.startsWith('BEAR-')) {
                    this.code = this.code.replace(/^BEAR-/, 'SHIP-');
                }
            }
        },

        generateRandomCode() {
            const prefix = this.voucher_type === 'SHIPPING' ? 'SHIP' : 'BEAR';
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            let randomStr = '';
            for (let i = 0; i < 5; i++) {
                randomStr += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            this.code = `${prefix}-${randomStr}`;
        },

        setPresetDays(days) {
            let start = new Date();
            if (this.start_date) {
                const parsed = new Date(typeof this.start_date === 'string' ? this.start_date.replace(/-/g, '/') : this.start_date);
                if (!isNaN(parsed.getTime())) start = parsed;
            }
            const end = new Date(start.getTime() + days * 24 * 60 * 60 * 1000);
            
            const pad = (n) => n.toString().padStart(2, '0');
            const formatted = `${end.getFullYear()}-${pad(end.getMonth()+1)}-${pad(end.getDate())} ${pad(end.getHours())}:${pad(end.getMinutes())}`;
            this.end_date = formatted;

            // Cập nhật ngày trực tiếp vào component datetime-picker
            window.dispatchEvent(new CustomEvent('set-datetime-end_date', { detail: formatted }));
        },

        formatCurrency(val) {
            if (!val || isNaN(val)) return '0đ';
            return new Intl.NumberFormat('vi-VN').format(val) + 'đ';
        },

        get previewDiscountText() {
            const val = parseFloat(this.discount_value) || 0;
            const suffix = this.voucher_type === 'SHIPPING' ? ' phí ship' : '';
            if (this.discount_type === 'PERCENTAGE') {
                let text = `Giảm ${val}%${suffix}`;
                if (this.max_discount_value && parseFloat(this.max_discount_value) > 0) {
                    text += ` (Tối đa ${new Intl.NumberFormat('vi-VN').format(this.max_discount_value)}đ)`;
                }
                return text;
            } else {
                return `Giảm ${new Intl.NumberFormat('vi-VN').format(val)}đ${suffix}`;
            }
        },

        get previewConditionText() {
            const min = parseFloat(this.min_order_value) || 0;
            if (min <= 0) return 'Áp dụng cho mọi giá trị đơn hàng';
            return `Áp dụng cho đơn hàng từ ${new Intl.NumberFormat('vi-VN').format(min)}đ`;
        },

        get previewScopeText() {
            if (this.voucher_type === 'SHIPPING') return '🚚 Toàn đơn hàng (Phí ship)';
            if (this.apply_scope === 'CATEGORY') {
                return `📂 Áp dụng ${this.selectedCategories.length} danh mục`;
            }
            if (this.apply_scope === 'PRODUCT') {
                return `🧸 Áp dụng ${this.selectedProducts.length} sản phẩm`;
            }
            return '🌐 Áp dụng toàn bộ shop';
        },

        get previewEndDate() {
            if (!this.end_date) return 'Vô thời hạn';
            try {
                const d = new Date(this.end_date);
                return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            } catch {
                return this.end_date;
            }
        }
    };
}

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
            }).catch(err => {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: {
                        type: 'error',
                        title: 'Không thể sao chép',
                        message: 'Trình duyệt không hỗ trợ sao chép tự động.'
                    }
                }));
            });
        }
    };
}
