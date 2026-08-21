export function toastManager(flash = {}) {
    return {
        toasts: [],
        init() {
            // Flash messages from Laravel session
            if (flash && flash.success) {
                this.addToast({
                    type: 'success',
                    title: 'Thành công',
                    message: flash.success
                });
            }

            if (flash && flash.error) {
                this.addToast({
                    type: 'error',
                    title: 'Có lỗi xảy ra',
                    message: flash.error
                });
            }

            if (flash && flash.info) {
                this.addToast({
                    type: 'info',
                    title: 'Thông báo',
                    message: flash.info
                });
            }

            // Real-time custom toast events from anywhere
            window.addEventListener('show-toast', (event) => {
                if (event.detail) {
                    this.addToast(event.detail);
                }
            });
        },
        addToast(detail) {
            const id = Date.now() + Math.random();
            const toast = {
                id: id,
                type: detail.type || 'success',
                title: detail.title || '',
                message: detail.message || '',
                visible: true
            };
            this.toasts.push(toast);

            // Auto-dismiss after 4 seconds
            setTimeout(() => {
                this.removeToast(id);
            }, detail.duration || 4000);
        },
        removeToast(id) {
            const idx = this.toasts.findIndex(t => t.id === id);
            if (idx !== -1) {
                this.toasts[idx].visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        }
    };
}
