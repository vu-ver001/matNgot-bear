{{-- Contact Seller Modal --}}
<div x-show="showContactModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true"
     @keydown.escape.window="showContactModal = false">
    
    <!-- Backdrop -->
    <div x-show="showContactModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity"
         @click="showContactModal = false"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div x-show="showContactModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-amber-100"
             @click.outside="showContactModal = false">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-[#FFF8EE] via-[#FDF3E2] to-[#FFF8EE] p-6 border-b border-amber-200/60">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#E08A1E] to-[#B87309] text-white flex items-center justify-center text-xl shadow-md shadow-amber-600/20">
                            🧸
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-[#4E342E]" id="modal-title">Liên hệ Mật Ngọt Bear</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#E08A1E] text-white shadow-2xs">
                                    <i class="fa-solid fa-check text-[10px] mr-1"></i> Yêu Thích
                                </span>
                            </div>
                            <p class="text-xs text-[#8E8076] mt-0.5">Tiệm gấu bông & quà tặng thủ công Mật Ngọt</p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="showContactModal = false"
                            class="w-8 h-8 rounded-full bg-white/80 hover:bg-white text-stone-500 hover:text-stone-800 flex items-center justify-center transition border border-amber-200/60 cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-4">
                <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-4 text-xs text-[#795548] leading-relaxed">
                    <span class="font-bold text-[#4E342E] block mb-1">
                        <i class="fa-solid fa-circle-info text-[#E08A1E] mr-1"></i> Hỗ trợ đơn hàng:
                    </span>
                    Quý khách cần hỗ trợ kiểm tra đơn hàng, giao gấp hoặc thay đổi thông tin vui lòng liên hệ trực tiếp qua các kênh bên dưới:
                </div>

                <!-- Contact Channels -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="tel:0987654321" 
                       class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-amber-200 hover:border-[#E08A1E] hover:bg-amber-50/50 transition group">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg group-hover:scale-110 transition">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-[#8E8076]">Hotline / Tổng đài</div>
                            <div class="text-sm font-bold text-[#4E342E] group-hover:text-[#E08A1E] transition">0987.654.321</div>
                        </div>
                    </a>

                    <a href="https://zalo.me" target="_blank" rel="noopener noreferrer"
                       class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-blue-200 hover:border-blue-500 hover:bg-blue-50/50 transition group">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition">
                            Z
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-blue-600">Chat qua Zalo</div>
                            <div class="text-sm font-bold text-blue-900 group-hover:text-blue-700 transition">Zalo Mật Ngọt Bear</div>
                        </div>
                    </a>

                    <a href="https://m.me" target="_blank" rel="noopener noreferrer"
                       class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-indigo-200 hover:border-indigo-500 hover:bg-indigo-50/50 transition group">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-lg group-hover:scale-110 transition">
                            <i class="fa-brands fa-facebook-messenger"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-indigo-600">Messenger</div>
                            <div class="text-sm font-bold text-indigo-900 group-hover:text-indigo-700 transition">Fanpage Mật Ngọt</div>
                        </div>
                    </a>

                    <a href="mailto:support@matngotbear.vn"
                       class="flex items-center gap-3.5 p-3.5 rounded-2xl border border-rose-200 hover:border-rose-500 hover:bg-rose-50/50 transition group">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-lg group-hover:scale-110 transition">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs text-rose-600">Email Hỗ trợ</div>
                            <div class="text-sm font-bold text-rose-900 truncate group-hover:text-rose-700 transition">support@matngotbear.vn</div>
                        </div>
                    </a>
                </div>

                <!-- Quick message box -->
                <div class="pt-2">
                    <label class="block text-xs font-semibold text-[#4E342E] mb-1.5">Gửi lời nhắn nhanh cho Mật Ngọt Bear:</label>
                    <textarea rows="3" 
                              class="w-full text-xs rounded-2xl border border-amber-200 p-3 focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E] text-[#4E342E]"
                              placeholder="Nhập nội dung bạn cần hỗ trợ về đơn hàng này..."></textarea>
                    <div class="mt-2 flex justify-end">
                        <button type="button"
                                @click="alert('Cảm ơn bạn! Đội ngũ Mật Ngọt Bear đã tiếp nhận tin nhắn và sẽ phản hồi trong giây lát.'); showContactModal = false"
                                class="px-4 py-2 bg-gradient-to-r from-[#E08A1E] to-[#B87309] text-white text-xs font-bold rounded-xl shadow-md hover:from-[#C77815] hover:to-[#965A04] transition cursor-pointer">
                            <i class="fa-regular fa-paper-plane mr-1"></i> Gửi tin nhắn
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-stone-50 px-6 py-3.5 border-t border-stone-200/60 flex justify-end">
                <button type="button" 
                        @click="showContactModal = false"
                        class="px-4 py-2 text-xs font-semibold text-stone-600 hover:text-stone-900 rounded-xl hover:bg-stone-200/70 transition cursor-pointer">
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>
