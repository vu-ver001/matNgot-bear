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
            } else {
                this.selectedItems = [];
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

        async deleteItem(itemId) {
            if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) return;

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
                    if (this.items.length === 0) {
                        location.reload();
                    }
                }
            } catch (error) {
                console.error('Error deleting item:', error);
            }
        },

        async clearAllCart() {
            if (!confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm trong giỏ hàng?')) return;

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
