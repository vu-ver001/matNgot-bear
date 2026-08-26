import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import { Vietnamese } from 'flatpickr/dist/l10n/vn.js';

import { toastManager } from './toast.js';
import { cuteDateTimePicker } from './datetime-picker.js';
import { voucherForm } from './admin/vouchers/create.js';
import { vouchersList } from './admin/vouchers/index.js';
import { cartComponent } from './customer/cart.js';

flatpickr.localize(Vietnamese);

window.Alpine = Alpine;
window.flatpickr = flatpickr;

// Expose globally for inline x-data or direct script calls
window.toastManager = toastManager;
window.cuteDateTimePicker = cuteDateTimePicker;
window.voucherForm = voucherForm;
window.vouchersList = vouchersList;
window.cartComponent = cartComponent;

// Register Alpine data components
Alpine.data('toastManager', toastManager);
Alpine.data('cuteDateTimePicker', cuteDateTimePicker);
Alpine.data('voucherForm', voucherForm);
Alpine.data('vouchersList', vouchersList);
Alpine.data('cartComponent', cartComponent);

Alpine.start();
