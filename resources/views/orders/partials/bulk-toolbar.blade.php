{{-- Bulk Order Operations Toolbar (Thao tác hàng loạt) --}}
@if($showBulkToolbar ?? true)
<div class="bulk-order-toolbar bg-white border border-amber-200/90 rounded-2xl p-4 shadow-2xs mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-all"
     :class="{ 'border-amber-400 bg-amber-50/20 shadow-sm': selectedOrderIds.length > 0 }">
    <div class="flex flex-wrap items-center gap-3">
        <label class="flex items-center gap-2.5 cursor-pointer select-none font-semibold text-sm text-[#4E342E]">
            <input type="checkbox"
                   :checked="isAllSelected"
                   @change="toggleSelectAll()"
                   class="w-4.5 h-4.5 rounded border-[#D4C3B3] text-[#B87309] focus:ring-[#B87309] cursor-pointer">
            <span>Chọn tất cả (<span x-text="pageOrderIds.length"></span> đơn <span x-text="config.countLabel || 'có thể xử lý'"></span>)</span>
        </label>

        <template x-if="selectedOrderIds.length > 0">
            <div class="flex items-center gap-2 pl-2 border-l border-amber-200">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#E08A1E] text-white">
                    Đã chọn: <span x-text="selectedOrderIds.length" class="ml-1 font-black"></span>
                </span>
                <button type="button" 
                        @click="clearSelection()" 
                        class="text-xs text-stone-500 hover:text-rose-600 hover:underline cursor-pointer font-medium">
                    Bỏ chọn
                </button>
            </div>
        </template>
    </div>

    <div class="flex items-center gap-2">
        <!-- Nút THAO TÁC HÀNG LOẠT -->
        <button type="button"
                @click="confirmBulkAction()"
                :disabled="selectedOrderIds.length === 0"
                class="btn-bulk-ship">
            <i :class="config.actionIcon || 'fa-solid fa-circle-check'"></i>
            <span x-text="config.actionLabel || 'Chấp nhận đơn hàng loạt'"></span>
            <template x-if="selectedOrderIds.length > 0">
                <span class="px-1.5 py-0.5 text-[11px] bg-white/25 rounded-md ml-1" x-text="'(' + selectedOrderIds.length + ')'"></span>
            </template>
        </button>
    </div>
</div>

<!-- Form submit ẩn -->
<form x-ref="bulkForm" action="{{ route($routePrefix . '.bulkUpdateStatus') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="target_status" :value="config.targetStatus || 'CONFIRMED'">
    <template x-for="id in selectedOrderIds" :key="id">
        <input type="hidden" name="order_ids[]" :value="id">
    </template>
</form>
@endif

<script>
    if (typeof window.bulkOrderManager !== 'function') {
        window.bulkOrderManager = function(pageIds = [], customConfig = {}) {
            return {
                pageOrderIds: pageIds,
                selectedOrderIds: [],
                config: Object.assign({
                    targetStatus: 'CONFIRMED',
                    actionLabel: 'Chấp nhận đơn hàng loạt',
                    actionIcon: 'fa-solid fa-circle-check',
                    confirmTitle: 'Xác nhận chấp nhận đơn hàng loạt?',
                    confirmText: 'Bạn có chắc chắn muốn chấp nhận các đơn hàng đã chọn?',
                    confirmButtonText: '<i class="fa-solid fa-circle-check mr-1"></i> Đồng ý chấp nhận',
                    confirmButtonColor: '#B87309',
                    countLabel: 'chờ xác nhận'
                }, customConfig),
                get isAllSelected() {
                    return this.pageOrderIds.length > 0 && this.pageOrderIds.every(id => this.selectedOrderIds.includes(id));
                },
                isSelected(id) {
                    return this.selectedOrderIds.includes(id);
                },
                toggleOrder(id) {
                    if (this.isSelected(id)) {
                        this.selectedOrderIds = this.selectedOrderIds.filter(i => i !== id);
                    } else {
                        this.selectedOrderIds.push(id);
                    }
                },
                toggleSelectAll() {
                    if (this.isAllSelected) {
                        this.selectedOrderIds = this.selectedOrderIds.filter(id => !this.pageOrderIds.includes(id));
                    } else {
                        const toAdd = this.pageOrderIds.filter(id => !this.selectedOrderIds.includes(id));
                        this.selectedOrderIds = [...this.selectedOrderIds, ...toAdd];
                    }
                },
                clearSelection() {
                    this.selectedOrderIds = [];
                },
                confirmBulkShip() {
                    this.confirmBulkAction();
                },
                confirmBulkAction() {
                    if (this.selectedOrderIds.length === 0) return;
                    const count = this.selectedOrderIds.length;
                    const title = this.config.confirmTitle || 'Xác nhận thao tác hàng loạt?';
                    const text = (this.config.confirmText || 'Bạn có chắc chắn muốn cập nhật :count đơn hàng đã chọn?').replace(':count', count);
                    const btnText = this.config.confirmButtonText || 'Đồng ý';
                    const btnColor = this.config.confirmButtonColor || '#B87309';

                    if (window.Swal) {
                        Swal.fire({
                            title: title,
                            text: text,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: btnColor,
                            cancelButtonColor: '#6B7280',
                            confirmButtonText: btnText,
                            cancelButtonText: 'Hủy bỏ',
                            customClass: {
                                popup: 'rounded-2xl',
                                confirmButton: 'rounded-xl font-bold px-4 py-2',
                                cancelButton: 'rounded-xl font-bold px-4 py-2'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.$refs.bulkForm.submit();
                            }
                        });
                    } else {
                        if (confirm(text)) {
                            this.$refs.bulkForm.submit();
                        }
                    }
                }
            };
        };
    }
</script>
