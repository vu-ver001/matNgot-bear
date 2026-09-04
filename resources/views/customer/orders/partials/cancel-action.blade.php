@if ($order->order_status === 'PENDING')
    <div class="mt-6" x-data="{ open: false }">
        <button @click="open = true"
                class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-rose-700 bg-rose-50 border border-rose-200 rounded-xl hover:bg-rose-100">
            Hủy đơn hàng
        </button>

        <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="open = false"></div>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-medium text-[#1E293B]">Xác nhận hủy đơn hàng</h3>
                        <p class="mt-2 text-sm text-[#64748B]">
                            Bạn có chắc chắn muốn hủy đơn hàng <span class="font-medium text-[#1E293B]">{{ $order->order_code }}</span>? Hành động này không thể hoàn tác.
                        </p>
                    </div>
                    <div class="bg-amber-50 px-6 py-3 flex justify-end gap-3">
                        <button @click="open = false" class="px-4 py-2 text-sm font-medium text-[#64748B] bg-white border border-amber-200 rounded-full hover:bg-amber-50">Đóng</button>
                        <form method="POST" action="{{ route('customer.orders.cancel', $order) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-rose-600 rounded-full hover:bg-rose-700">Xác nhận hủy</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
