export function cartComponent(initialItems = []) {
    // SHOPEE PATTERN: Mặc định tất cả sản phẩm trong giỏ hàng đều được TÍCH CHỌN.
    // Chỉ những sản phẩm mà khách hàng CHỦ ĐỘNG BỎ TÍCH mới không được chọn (lưu trong mn_unselected_cart_items).
    // Bất kỳ sản phẩm nào mới được thêm vào giỏ hàng sẽ LUÔN ĐƯỢC TÍCH SẴN!
    let unselectedIds = [];
    try {
        const unselectedSaved = localStorage.getItem('mn_unselected_cart_items');
        if (unselectedSaved !== null) {
            const parsed = JSON.parse(unselectedSaved);
            if (Array.isArray(parsed)) {
                // Chỉ giữ lại ID của những sản phẩm thực sự còn tồn tại trong giỏ
                unselectedIds = parsed.filter(id => initialItems.some(i => i.id === id));
            }
        }
    } catch (e) {
        unselectedIds = [];
    }

    // Các sản phẩm được chọn = tất cả sản phẩm trong giỏ ngoại trừ những món khách chủ động bỏ tích
    const initialSelected = initialItems
        .map(i => i.id)
        .filter(id => !unselectedIds.includes(id));

    return {
        items: initialItems,
        selectedItems: initialSelected,
        unselectedItems: unselectedIds,
        activeVariantModal: null,

        init() {
            this.saveSelection();
        },

        saveSelection() {
            try {
                this.unselectedItems = this.items
                    .map(i => i.id)
                    .filter(id => !this.selectedItems.includes(id));
                localStorage.setItem('mn_unselected_cart_items', JSON.stringify(this.unselectedItems));
                localStorage.setItem('mn_selected_cart_items', JSON.stringify(this.selectedItems));
            } catch (e) {}
        },

        isSelected(itemId) {
            return this.selectedItems.includes(itemId);
        },

        hasItem(itemId) {
            return this.items.some(i => i.id === itemId);
        },

        get isAllSelected() {
            return this.items.length > 0 && this.selectedItems.length === this.items.length;
        },

        toggleSelectAll(checked) {
            if (checked) {
                this.selectedItems = this.items.map(i => i.id);
                this.unselectedItems = [];
                console.log('✅ [GIỎ HÀNG] Đã chọn tất cả (' + this.selectedItems.length + ' sản phẩm)');
            } else {
                this.selectedItems = [];
                this.unselectedItems = this.items.map(i => i.id);
                const nowStr = new Date().toLocaleTimeString('vi-VN');
                console.warn('⚠️ [GIỎ HÀNG - LOG BỎ TÍCH]: Người dùng vừa BỎ CHỌN TẤT CẢ sản phẩm lúc ' + nowStr);

                // Gửi log tức thì về máy chủ
                this.sendUncheckLog({
                    action: 'uncheck_all',
                    remaining_count: 0
                });
            }
            this.saveSelection();
        },

        toggleItem(itemId, checked, productName = '') {
            const item = this.items.find(i => i.id === itemId);
            const pName = productName || (item ? item.name : `Mã #${itemId}`);

            if (checked) {
                if (!this.selectedItems.includes(itemId)) {
                    this.selectedItems.push(itemId);
                }
                this.unselectedItems = this.unselectedItems.filter(id => id !== itemId);
                console.log('✅ [GIỎ HÀNG] Đã chọn lại sản phẩm:', pName, `(CartItem ID: ${itemId})`);
            } else {
                this.selectedItems = this.selectedItems.filter(id => id !== itemId);
                if (!this.unselectedItems.includes(itemId)) {
                    this.unselectedItems.push(itemId);
                }
                const nowStr = new Date().toLocaleTimeString('vi-VN');
                console.warn(`⚠️ [GIỎ HÀNG - LOG BỎ TÍCH TỨC THÌ]: Bỏ tích sản phẩm "${pName}" (ID: ${itemId}) lúc ${nowStr}. Còn lại: ${this.selectedItems.length} sản phẩm được chọn.`);

                // Gửi log tức thì về máy chủ Laravel
                this.sendUncheckLog({
                    cart_item_id: itemId,
                    product_name: pName,
                    action: 'uncheck_single',
                    remaining_count: this.selectedItems.length
                });
            }
            this.saveSelection();
        },

        async sendUncheckLog(payload) {
            try {
                await fetch('/customer/cart/log-uncheck', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.getCSRFToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
            } catch (error) {
                console.error('Không thể gửi log bỏ tích về server:', error);
            }
        },

        getItemQuantity(itemId) {
            const item = this.items.find(i => i.id === itemId);
            return item ? item.quantity : 1;
        },

        getItemLineTotal(itemId) {
            const item = this.items.find(i => i.id === itemId);
            return item ? item.line_total : 0;
        },

        get selectedSubtotal() {
            return this.items
                .filter(i => this.selectedItems.includes(i.id))
                .reduce((sum, item) => sum + item.line_total, 0);
        },

        formatVND(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        },

        getCSRFToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        getItemVariantDisplay(itemId) {
            const item = this.items.find(i => i.id === itemId);
            return item ? item.variant_display : null;
        },

        getItemPrice(itemId) {
            const item = this.items.find(i => i.id === itemId);
            return item ? item.unit_price : 0;
        },

        getItemImageUrl(itemId) {
            const item = this.items.find(i => i.id === itemId);
            return item ? item.image_url : '';
        },

        getItemStock(itemId) {
            const item = this.items.find(i => i.id === itemId);
            return item ? item.stock_quantity : 0;
        },

        openVariantSelector(itemId) {
            const item = this.items.find(i => i.id === itemId);
            if (!item || !item.available_variants || item.available_variants.length === 0) return;

            const currentVar = item.available_variants.find(v => v.id === item.product_variant_id) || item.available_variants[0];
            const colors = [...new Set(item.available_variants.map(v => v.color).filter(Boolean))];
            const sizes = [...new Set(item.available_variants.map(v => v.size).filter(Boolean))];

            this.activeVariantModal = {
                item: item,
                colors: colors,
                sizes: sizes,
                selectedColor: currentVar ? currentVar.color : (colors[0] || ''),
                selectedSize: currentVar ? currentVar.size : (sizes[0] || ''),
                selectedVariant: currentVar,
                isUpdating: false,
            };
            this.updateModalMatchedVariant();
        },

        closeVariantSelector() {
            this.activeVariantModal = null;
        },

        selectModalColor(color) {
            if (!this.activeVariantModal) return;
            this.activeVariantModal.selectedColor = color;
            this.updateModalMatchedVariant();
        },

        selectModalSize(size) {
            if (!this.activeVariantModal) return;
            this.activeVariantModal.selectedSize = size;
            this.updateModalMatchedVariant();
        },

        updateModalMatchedVariant() {
            if (!this.activeVariantModal) return;
            const { item, selectedColor, selectedSize } = this.activeVariantModal;

            let matched = item.available_variants.find(v =>
                (!selectedColor || v.color === selectedColor) &&
                (!selectedSize || v.size === selectedSize)
            );

            if (!matched && selectedColor) {
                matched = item.available_variants.find(v => v.color === selectedColor);
                if (matched && matched.size) {
                    this.activeVariantModal.selectedSize = matched.size;
                }
            }

            this.activeVariantModal.selectedVariant = matched || item.available_variants[0];
        },

        isModalSizeAvailable(size) {
            if (!this.activeVariantModal) return false;
            const { item, selectedColor } = this.activeVariantModal;
            return item.available_variants.some(v =>
                (!selectedColor || v.color === selectedColor) &&
                v.size === size &&
                v.stock_quantity > 0
            );
        },

        isModalColorAvailable(color) {
            if (!this.activeVariantModal) return false;
            const { item, selectedSize } = this.activeVariantModal;
            return item.available_variants.some(v =>
                v.color === color &&
                (!selectedSize || v.size === selectedSize) &&
                v.stock_quantity > 0
            );
        },

        async confirmVariantChange() {
            if (!this.activeVariantModal || !this.activeVariantModal.selectedVariant) return;
            const { item, selectedVariant } = this.activeVariantModal;

            if (selectedVariant.id === item.product_variant_id) {
                this.closeVariantSelector();
                return;
            }

            if (selectedVariant.stock_quantity <= 0) {
                if (typeof Toast !== 'undefined') {
                    Toast.fire({ icon: 'error', title: 'Phân loại này hiện đã hết hàng!' });
                }
                return;
            }

            this.activeVariantModal.isUpdating = true;

            try {
                const response = await fetch(`/customer/cart/${item.id}/variant`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.getCSRFToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_variant_id: selectedVariant.id })
                });

                const data = await response.json();
                if (data.success) {
                    if (data.merged) {
                        const existing = this.items.find(i => i.id === data.merged_id);
                        if (existing) {
                            existing.quantity = data.quantity;
                            existing.unit_price = data.unit_price;
                            existing.line_total = data.line_total;
                            existing.stock_quantity = data.stock_quantity;
                            existing.variant_display = data.variant_display;
                            existing.image_url = data.image_url;
                        }
                        this.items = this.items.filter(i => i.id !== data.deleted_id);
                        this.selectedItems = this.selectedItems.filter(id => id !== data.deleted_id);
                        if (!this.selectedItems.includes(data.merged_id)) {
                            this.selectedItems.push(data.merged_id);
                        }
                    } else {
                        item.product_variant_id = data.product_variant_id;
                        item.variant_display = data.variant_display;
                        item.unit_price = data.unit_price;
                        item.quantity = data.quantity;
                        item.line_total = data.line_total;
                        item.stock_quantity = data.stock_quantity;
                        item.image_url = data.image_url;
                    }

                    this.saveSelection();
                    if (typeof window.cartItemsCount !== 'undefined' && data.cart_count !== undefined) {
                        window.cartItemsCount = data.cart_count;
                        if (typeof updateCartBadge === 'function') updateCartBadge();
                    }

                    if (typeof Toast !== 'undefined') {
                        Toast.fire({
                            icon: 'success',
                            title: data.message || 'Đã đổi phân loại sản phẩm!'
                        });
                    }
                    this.closeVariantSelector();
                } else {
                    alert(data.message || 'Không thể đổi phân loại sản phẩm.');
                }
            } catch (e) {
                console.error('Lỗi đổi phân loại:', e);
            } finally {
                if (this.activeVariantModal) {
                    this.activeVariantModal.isUpdating = false;
                }
            }
        },

        async updateQuantity(itemId, newQty) {
            const item = this.items.find(i => i.id === itemId);
            if (!item) return;
            if (newQty < 1 || newQty > item.stock_quantity) return;

            item.quantity = newQty;
            item.line_total = item.unit_price * newQty;

            try {
                const response = await fetch(`/customer/cart/${itemId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.getCSRFToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity: newQty })
                });

                const data = await response.json();
                if (!data.success) {
                    alert(data.message || 'Cập nhật thất bại');
                    location.reload();
                }
            } catch (error) {
                console.error('Error updating quantity:', error);
            }
        },

        async deleteItem(itemId, productName = '') {
            const item = this.items.find(i => i.id === itemId);
            const pName = (item ? item.name : '') || productName || 'sản phẩm';

            // Xóa ngay lập tức trên giao diện (Optimistic UI) để mượt mà không cần popup chờ đợi
            this.items = this.items.filter(i => i.id !== itemId);
            this.selectedItems = this.selectedItems.filter(id => id !== itemId);
            this.saveSelection();

            if (typeof window.cartItemsCount !== 'undefined') {
                window.cartItemsCount = Math.max(0, (window.cartItemsCount || 1) - 1);
            }
            if (typeof updateCartBadge === 'function') {
                updateCartBadge();
            }

            // Thông báo Toast góc nhẹ nhàng (không chặn màn hình)
            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'success',
                    title: `Đã xóa "${pName}" khỏi giỏ hàng.`
                });
            }

            try {
                const response = await fetch(`/customer/cart/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.getCSRFToken(),
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data && data.cart_count !== undefined && typeof window.cartItemsCount !== 'undefined') {
                        window.cartItemsCount = data.cart_count;
                        if (typeof updateCartBadge === 'function') {
                            updateCartBadge();
                        }
                    }
                }

                if (this.items.length === 0) {
                    setTimeout(() => location.reload(), 300);
                }
            } catch (error) {
                console.error('Error deleting item:', error);
            }
        },

        async clearAllCart() {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: 'Xóa toàn bộ giỏ hàng?',
                    html: 'Bạn có chắc chắn muốn dọn sạch tất cả sản phẩm đang có trong giỏ hàng không?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#E53E3E',
                    cancelButtonColor: '#9CA3AF',
                    confirmButtonText: '<i class="fa-solid fa-trash"></i> Xóa tất cả',
                    cancelButtonText: 'Hủy',
                    reverseButtons: true,
                    background: '#FAF6F0',
                    color: '#2E190E',
                    customClass: {
                        popup: 'rounded-3xl border-2 border-[#EBDDCD] shadow-2xl',
                        title: 'text-[#2E190E] font-bold text-xl',
                        confirmButton: 'rounded-xl font-bold px-5 py-2.5 shadow-md',
                        cancelButton: 'rounded-xl font-bold px-5 py-2.5'
                    }
                });

                if (!result.isConfirmed) return;
            } else {
                if (!confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm trong giỏ hàng?')) return;
            }

            try {
                const response = await fetch('/customer/cart-clear', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.getCSRFToken(),
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.selectedItems = [];
                    this.unselectedItems = [];
                    try {
                        localStorage.removeItem('mn_unselected_cart_items');
                        localStorage.removeItem('mn_selected_cart_items');
                    } catch (e) {}
                    location.reload();
                }
            } catch (error) {
                console.error('Error clearing cart:', error);
            }
        },

        handleCheckoutSubmit(e) {
            if (this.selectedItems.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Chưa chọn sản phẩm',
                        text: 'Vui lòng tích chọn ít nhất 1 sản phẩm để tiến hành thanh toán.',
                        confirmButtonColor: '#E08A1E'
                    });
                }
                return;
            }

            if (typeof window.isCustomerAuthenticated !== 'undefined' && !window.isCustomerAuthenticated) {
                const params = new URLSearchParams();
                this.selectedItems.forEach(id => params.append('selected_items[]', id));
                const targetUrl = '/customer/checkout?' + params.toString();
                if (typeof window.openAuthModal === 'function') {
                    window.openAuthModal(targetUrl);
                } else {
                    window.location.href = '/login?redirect=' + encodeURIComponent(targetUrl);
                }
                return;
            }

            document.getElementById('checkoutForm').submit();
        }
    };
}
