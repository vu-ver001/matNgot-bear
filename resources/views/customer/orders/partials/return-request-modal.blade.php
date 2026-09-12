{{-- Modal Yêu cầu Trả hàng / Hoàn tiền chuẩn Sàn TMĐT --}}
<div x-show="openReturnModal" 
     x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;"
     @keydown.escape.window="openReturnModal = false">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity" 
             @click="openReturnModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-rose-200/80"
             @click.stop
             x-data="{
                 selectedReason: 'Hàng bị lỗi / hỏng hóc hoặc không đúng mô tả',
                 customNote: '',
                 bankName: '{{ $order->refund_bank_name ?? '' }}',
                 bankAccount: '{{ $order->refund_bank_account ?? '' }}',
                 accountHolder: '{{ $order->refund_account_holder ?? '' }}',
                 reasons: [
                     'Chưa nhận được hàng (nhưng hệ thống báo đã giao)',
                     'Hàng bị lỗi / hỏng hóc hoặc không đúng mô tả',
                     'Shop giao sai mẫu / sai kích thước / sai màu sắc',
                     'Hàng thiếu số lượng hoặc phụ kiện đính kèm',
                     'Sản phẩm khác xa so với hình ảnh trên website',
                     'Lý do khác'
                 ]
             }">
            <form action="{{ route('customer.orders.request_return', $order->id) }}" method="POST">
                @csrf
                {{-- Header --}}
                <div class="bg-gradient-to-r from-rose-50 via-amber-50 to-orange-50 px-6 py-5 border-b border-rose-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-rose-500 text-white flex items-center justify-center text-lg shrink-0 shadow-sm shadow-rose-500/25">
                            <i class="fa-solid fa-arrow-rotate-left"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-[#2C1408]">Yêu cầu Trả hàng / Hoàn tiền</h3>
                            <p class="text-xs text-[#7D6B5D]">Đơn hàng: <strong class="font-mono text-amber-900">#{{ $order->order_code }}</strong></p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="openReturnModal = false" 
                            class="w-8 h-8 rounded-full bg-white/80 hover:bg-white text-stone-500 hover:text-stone-800 flex items-center justify-center transition border border-stone-200">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-3.5 text-xs text-[#7A5835] leading-relaxed flex items-start gap-2.5">
                        <i class="fa-solid fa-shield-halved text-amber-600 text-sm mt-0.5 shrink-0"></i>
                        <span>Chính sách đổi trả bảo vệ người mua của <strong>Mật Ngọt Bear</strong>: Sau khi bạn gửi yêu cầu, nhân viên CSKH sẽ gọi điện hỗ trợ bạn đổi sản phẩm mới hoặc hoàn tiền theo quy định.</span>
                    </div>

                    {{-- Chọn lý do --}}
                    <div>
                        <label class="block text-xs font-bold text-[#2C1408] mb-2">
                            Lý do yêu cầu Trả hàng / Hoàn tiền <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <template x-for="(reason, index) in reasons" :key="index">
                                <label class="flex items-center gap-3 p-3 rounded-2xl border transition cursor-pointer text-xs"
                                       :class="selectedReason === reason ? 'border-amber-500 bg-amber-50/50 text-[#2C1408] font-bold shadow-2xs' : 'border-stone-200 hover:bg-stone-50 text-stone-700'">
                                    <input type="radio" 
                                           name="return_reason" 
                                           :value="reason" 
                                           x-model="selectedReason"
                                           class="text-[#E08A1E] focus:ring-[#E08A1E]">
                                    <span x-text="reason"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    {{-- Chi tiết mô tả lỗi --}}
                    <div>
                        <label class="block text-xs font-bold text-[#2C1408] mb-1.5">
                            Mô tả chi tiết tình trạng hàng hóa <span class="text-stone-400 font-normal">(không bắt buộc)</span>
                        </label>
                        <textarea name="return_note" 
                                  rows="3" 
                                  x-model="customNote"
                                  placeholder="Vui lòng mô tả cụ thể tình trạng sản phẩm bạn nhận được để shop xử lý nhanh nhất..."
                                  class="w-full rounded-2xl border-stone-200 text-xs p-3 focus:border-amber-500 focus:ring-amber-500 bg-stone-50/50"></textarea>
                    </div>

                    {{-- Thông tin STK nhận tiền hoàn lại --}}
                    <div class="pt-2 border-t border-stone-100">
                        <div class="flex items-center gap-2 mb-2 text-xs font-bold text-[#2C1408]">
                            <i class="fa-solid fa-building-columns text-amber-600"></i>
                            <span>Thông tin tài khoản nhận tiền hoàn lại <span class="text-stone-400 font-normal">(nếu có hoàn tiền)</span></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-600 mb-1">Ngân hàng</label>
                                <input type="text" 
                                       name="refund_bank_name" 
                                       x-model="bankName"
                                       placeholder="VD: MB Bank, Vietcombank..."
                                       class="w-full rounded-xl border-stone-200 text-xs py-2 px-3 focus:border-amber-500 focus:ring-amber-500 bg-stone-50/50">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-stone-600 mb-1">Số tài khoản</label>
                                <input type="text" 
                                       name="refund_bank_account" 
                                       x-model="bankAccount"
                                       placeholder="VD: 0987654321..."
                                       class="w-full rounded-xl border-stone-200 text-xs py-2 px-3 focus:border-amber-500 focus:ring-amber-500 bg-stone-50/50">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-semibold text-stone-600 mb-1">Tên chủ tài khoản</label>
                                <input type="text" 
                                       name="refund_account_holder" 
                                       x-model="accountHolder"
                                       placeholder="VD: NGUYEN VAN A"
                                       class="w-full rounded-xl border-stone-200 text-xs py-2 px-3 uppercase focus:border-amber-500 focus:ring-amber-500 bg-stone-50/50">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-stone-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-stone-200">
                    <button type="button" 
                            @click="openReturnModal = false" 
                            class="px-4 py-2.5 text-xs font-bold text-stone-700 bg-white border border-stone-200 rounded-xl hover:bg-stone-100 transition cursor-pointer">
                        Đóng
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 text-xs font-extrabold text-white bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 rounded-xl shadow-md shadow-rose-600/25 transition cursor-pointer flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                        <span>Gửi yêu cầu Trả hàng / Hoàn tiền</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
