const wishlistRoot = document.querySelector('[data-wishlist-root]');

if (wishlistRoot) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const grid = wishlistRoot.querySelector('[data-wishlist-grid]');
    const emptyState = wishlistRoot.querySelector('[data-wishlist-empty]');
    const pagination = wishlistRoot.querySelector('[data-wishlist-pagination]');
    const clearControl = wishlistRoot.querySelector('[data-wishlist-clear-control]');
    const sortControl = wishlistRoot.querySelector('[data-wishlist-sort-control]');
    const toast = wishlistRoot.querySelector('[data-wishlist-toast]');
    const toastCloseButton = toast?.querySelector('[data-wishlist-toast-close]');
    let totalItems = Number(wishlistRoot.dataset.totalItems || 0);
    let toastTimer;

    const updateTotal = () => {
        wishlistRoot.querySelectorAll('[data-wishlist-total]').forEach((element) => {
            element.textContent = String(totalItems);
        });
    };

    const showToast = (message, isError = false) => {
        if (!toast) return;

        window.clearTimeout(toastTimer);
        toast.querySelector('p').textContent = message;
        toast.querySelector('span').textContent = isError ? '!' : '✓';
        toast.classList.toggle('is-error', isError);
        toast.setAttribute('role', isError ? 'alert' : 'status');
        toast.classList.add('is-visible');

        toastTimer = window.setTimeout(() => {
            toast.classList.remove('is-visible');
        }, 4200);
    };

    toastCloseButton?.addEventListener('click', () => {
        window.clearTimeout(toastTimer);
        toast?.classList.remove('is-visible');
    });

    if (toast?.dataset.initialMessage) {
        showToast(toast.dataset.initialMessage, toast.dataset.initialError === 'true');
    }

    wishlistRoot.querySelectorAll('[data-product-image]').forEach((image) => {
        image.addEventListener('error', () => image.remove(), { once: true });
    });

    wishlistRoot.addEventListener('submit', async (event) => {
        const cartForm = event.target.closest('[data-wishlist-cart-form]');

        if (cartForm) {
            event.preventDefault();

            const button = cartForm.querySelector('button[type="submit"]');
            button.disabled = true;

            try {
                const response = await fetch(cartForm.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });
                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
                }

                showToast(result.message);
            } catch (error) {
                showToast(error.message, true);
            } finally {
                button.disabled = false;
            }

            return;
        }

        const form = event.target.closest('[data-wishlist-remove-form]');

        if (!form) return;

        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const card = form.closest('[data-wishlist-card]');
        button.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Không thể xóa sản phẩm lúc này.');
            }

            card.classList.add('is-removing');
            totalItems = Math.max(0, totalItems - 1);
            updateTotal();
            showToast(result.message);

            window.setTimeout(() => {
                card.remove();

                if (totalItems === 0) {
                    grid?.classList.add('hidden');
                    emptyState?.classList.remove('hidden');
                    pagination?.classList.add('hidden');
                    clearControl?.classList.add('hidden');
                    sortControl?.classList.add('hidden');
                } else if (!grid?.querySelector('[data-wishlist-card]')) {
                    window.location.reload();
                }
            }, 220);
        } catch (error) {
            button.disabled = false;
            showToast(error.message, true);
        }
    });
}
