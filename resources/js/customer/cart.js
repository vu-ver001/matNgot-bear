export function cartComponent(initialItems = []) {
    return {
        items: initialItems,
        // Mặc định tích chọn tất cả sản phẩm khi truy cập giỏ hàng (Shopee style)
        selectedItems: initialItems.map(i => i.id),

        isSelected(itemId) {
            return this.selectedItems.includes(itemId);
        },

        get isAllSelected() {
            return this.items.length > 0 && this.selectedItems.length === this.items.length;
        },

        toggleSelectAll(checked) {
            if (checked) {
                this.selectedItems = this.items.map(i => i.id);
                console.log('✅ [GIỎ HÀNG] Đã chọn tất cả (' + this.selectedItems.length + ' sản phẩm)');
            } else {
                this.selectedItems = [];
                const nowStr = new Date().toLocaleTimeString('vi-VN');
                console.warn('⚠️ [GIỎ HÀNG - LOG BỎ TÍCH]: Người dùng vừa BỎ CHỌN TẤT CẢ sản phẩm lúc ' + nowStr);

                // Gửi log tức thì về máy chủ
                this.sendUncheckLog({
                    action: 'uncheck_all',
                    remaining_count: 0
                });
            }
        },

        toggleItem(itemId, checked, productName = '') {
            const item = this.items.find(i => i.id === itemId);
            const pName = productName || (item ? item.name : `Mã #${itemId}`);

            if (checked) {
                if (!this.selectedItems.includes(itemId)) {
                    this.selectedItems.push(itemId);
                }
                console.log('✅ [GIỎ HÀNG] Đã chọn lại sản phẩm:', pName, `(CartItem ID: ${itemId})`);
            } else {
                this.selectedItems = this.selectedItems.filter(id => id !== itemId);
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
            const pName = productName || (item ? item.name : 'sản phẩm này');

            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: 'Xóa khỏi giỏ hàng?',
                    html: `Bạn có chắc muốn bỏ chú gấu <strong>"${pName}"</strong> ra khỏi giỏ hàng không?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#5C3219',
                    cancelButtonColor: '#9CA3AF',
                    confirmButtonText: '<i class="fa-solid fa-trash-can"></i> Đồng ý xóa',
                    cancelButtonText: 'Giữ lại',
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
                if (!confirm(`Bạn có chắc muốn xóa "${pName}" khỏi giỏ hàng?`)) return;
            }

            try {
                const response = await fetch(`/customer/cart/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.getCSRFToken(),
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.items = this.items.filter(i => i.id !== itemId);
                    this.selectedItems = this.selectedItems.filter(id => id !== itemId);
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Đã xóa!',
                            text: 'Sản phẩm đã được xóa khỏi giỏ hàng.',
                            confirmButtonColor: '#5C3219',
                            timer: 1500,
                            showConfirmButton: false,
                            background: '#FAF6F0',
                            color: '#2E190E'
                        });
                    }

                    if (this.items.length === 0) {
                        setTimeout(() => location.reload(), 600);
                    }
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
                    location.reload();
                }
            } catch (error) {
                console.error('Error clearing cart:', error);
            }
        }
    };
}
