{{-- Modal đổi địa chỉ nhận hàng --}}
@if ($order->order_status === 'PENDING')
<div x-show="showEditAddressModal"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs text-left"
     @click.self="showEditAddressModal = false"
     @keydown.escape.window="showEditAddressModal = false">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-amber-200 space-y-4"
         @click.stop>
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div>
                <h3 class="font-black text-lg text-[#2C1408] flex items-center gap-2">
                    <span class="text-xl">📍</span> Thay đổi địa chỉ nhận hàng
                </h3>
                <p class="text-xs text-[#786B61] mt-0.5">Đơn hàng #{{ $order->order_code }} (Đang chờ nhân viên xác nhận)</p>
            </div>
            <button type="button" @click="showEditAddressModal = false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-full hover:bg-gray-100 transition">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <form action="{{ route('customer.orders.update_shipping_address', $order->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-xs font-bold text-[#4A3B32] mb-1">Tên người nhận <span class="text-rose-500">*</span></label>
                <input type="text" name="recipient_name" value="{{ old('recipient_name', $order->recipient_name) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-amber-200 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#4A3B32] mb-1">Số điện thoại liên hệ <span class="text-rose-500">*</span></label>
                <input type="tel" name="recipient_phone" value="{{ old('recipient_phone', $order->recipient_phone) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-amber-200 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-[#4A3B32] mb-1">Địa chỉ giao hàng chi tiết <span class="text-rose-500">*</span></label>
                <textarea name="recipient_address" rows="3" required
                          placeholder="Số nhà, tên đường, thôn xóm, phường/xã, quận/huyện, tỉnh/thành..."
                          class="w-full px-4 py-2.5 rounded-xl border border-amber-200 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">{{ old('recipient_address', $order->recipient_address) }}</textarea>
                <p class="text-[11px] text-[#8C4A19] mt-1">💡 Hệ thống sẽ tự động làm sạch và định dạng địa chỉ chuẩn đẹp.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-[#4A3B32] mb-1">Ghi chú cho shipper (Tùy chọn)</label>
                <input type="text" name="note" value="{{ old('note', $order->note) }}"
                       placeholder="VD: Gọi trước khi giao, giao giờ hành chính..."
                       class="w-full px-4 py-2.5 rounded-xl border border-amber-200 text-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                <button type="button" @click="showEditAddressModal = false"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-[#64748B] hover:bg-gray-100 transition cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#E08A1E] to-[#8C4A19] hover:from-[#C77815] hover:to-[#733C14] shadow-md shadow-[#E08A1E]/30 transition cursor-pointer">
                    Lưu thay đổi địa chỉ
                </button>
            </div>
        </form>
    </div>
</div>
@endif
