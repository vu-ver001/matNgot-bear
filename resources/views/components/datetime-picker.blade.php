@props([
    'name',
    'value' => '',
    'placeholder' => 'Chọn ngày giờ...',
    'required' => false,
    'minDate' => null,
    'disablePast' => true,
])

<div class="relative" 
     x-data="cuteDateTimePicker({
         name: '{{ $name }}',
         initialValue: '{{ $value }}',
         minDate: '{{ $minDate }}',
         disablePast: {{ $disablePast ? 'true' : 'false' }}
     })"
     @click.outside="open = false"
     @keydown.escape.window="open = false">
     
    {{-- Hidden raw input for form submit --}}
    <input type="hidden" :name="name" :value="formattedRawValue" {{ $required ? 'required' : '' }}>

    {{-- Trigger Input Display --}}
    <div class="relative cursor-pointer" @click="togglePicker()" x-ref="triggerBtn">
        <input type="text"
               readonly
               :value="formattedDisplayValue"
               placeholder="{{ $placeholder }}"
               {{ $required ? 'required' : '' }}
               class="w-full rounded-xl border-[#EBDDCD] focus:border-[#DDA760] focus:ring-[#DDA760] text-xs sm:text-sm py-2.5 pl-10 pr-10 bg-white text-[#2E190E] font-semibold cursor-pointer shadow-xs select-none">
        
        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#5C3219] text-base select-none pointer-events-none">
            📅
        </span>

        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#8E8076] text-xs select-none pointer-events-none transition-transform duration-200"
              :class="open ? (dropUp ? 'rotate-0 text-[#5C3219]' : 'rotate-180 text-[#5C3219]') : ''">
            <span x-text="dropUp ? '▲' : '▼'"></span>
        </span>
    </div>

    {{-- Dropdown Modal (Tự động hiển thị TRÊN hoặc DƯỚI tùy khoảng trống màn hình) --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 bg-[#FAF6F0] rounded-3xl border-2 border-[#EBDDCD] shadow-2xl p-4 w-full sm:w-auto min-w-[320px] sm:min-w-[460px] text-[#2E190E]"
         :class="[
             dropUp ? 'bottom-full mb-2' : 'top-full mt-2',
             alignRight ? 'right-0' : 'left-0'
         ]"
         style="display: none;">
        
        <div class="flex flex-col sm:flex-row gap-4">
            
            {{-- CỘT TRÁI: LỊCH CHỌN NGÀY THÁNG --}}
            <div class="w-[280px] flex-shrink-0 flex flex-col justify-between">
                
                {{-- Header Lịch: Tháng/Năm + Mũi tên --}}
                <div class="bg-gradient-to-r from-[#7E4A28] to-[#5C3219] rounded-2xl p-2 px-3 text-white flex items-center justify-between shadow-sm mb-3">
                    <button type="button" 
                            @click="prevMonth()"
                            :disabled="isPrevMonthDisabled"
                            class="p-1.5 rounded-xl transition flex items-center justify-center"
                            :class="isPrevMonthDisabled ? 'opacity-20 cursor-not-allowed pointer-events-none' : 'hover:bg-white/20'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                    </button>

                    <div class="font-bold text-sm tracking-wide flex items-center gap-1">
                        <span x-text="monthNames[viewMonth]"></span>
                        <span>,</span>
                        <span x-text="viewYear"></span>
                    </div>

                    <button type="button" 
                            @click="nextMonth()"
                            class="p-1.5 hover:bg-white/20 rounded-xl transition flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>

                {{-- Hàng thứ trong tuần --}}
                <div class="grid grid-cols-7 gap-1 text-center font-bold text-[11px] text-[#7E4A28] mb-2">
                    <div>T2</div>
                    <div>T3</div>
                    <div>T4</div>
                    <div>T5</div>
                    <div>T6</div>
                    <div>T7</div>
                    <div class="text-[#E09028]">CN</div>
                </div>

                {{-- Lưới các ngày --}}
                <div class="grid grid-cols-7 gap-1 text-center font-semibold text-xs">
                    <template x-for="day in daysInGrid" :key="day.key">
                        <button type="button"
                                @click="day.clickable ? selectDay(day.year, day.month, day.date) : null"
                                :disabled="!day.clickable"
                                class="h-8 w-8 rounded-xl flex items-center justify-center mx-auto transition-all text-xs select-none"
                                :class="{
                                    'text-[#C4B5A5] opacity-20 cursor-not-allowed pointer-events-none bg-transparent': !day.clickable,
                                    'text-[#8E8076] opacity-50': day.isOtherMonth && day.clickable,
                                    'text-[#2E190E] hover:bg-[#FFF5E6] hover:scale-105': !day.isSelected && !day.isToday && day.clickable,
                                    'border-2 border-[#E09028] font-bold text-[#5C3219] bg-[#FFF5E6]': day.isToday && !day.isSelected && day.clickable,
                                    'bg-gradient-to-r from-[#E09028] to-[#5C3219] text-white font-bold shadow-md shadow-[#5C3219]/25 scale-105': day.isSelected
                                }">
                            <span x-text="day.date"></span>
                        </button>
                    </template>
                </div>

                {{-- Quick helper buttons --}}
                <div class="mt-3 pt-2.5 border-t border-[#EBDDCD] flex items-center justify-between text-[11px]">
                    <button type="button" 
                            @click="selectNow()"
                            class="text-[#5C3219] hover:text-[#2C160B] font-bold hover:underline flex items-center gap-1">
                        <span>⚡</span> Chọn hiện tại
                    </button>
                    <span class="text-[#8E8076]">🧸 Mật Ngọt Bear</span>
                </div>
            </div>

            {{-- CỘT PHẢI: CUỘN CHỌN GIỜ & PHÚT --}}
            <div class="sm:border-l sm:border-[#EBDDCD] sm:pl-4 flex flex-col justify-between">
                
                {{-- Tiêu đề cột Giờ / Phút --}}
                <div class="bg-[#FFF5E6] border border-[#EBDDCD] rounded-2xl p-2 px-3 text-center mb-2">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#7E4A28]">Thời Gian</div>
                    <div class="text-lg font-bold text-[#2E190E] flex items-center justify-center gap-1 mt-0.5">
                        <span class="bg-white px-2 py-0.5 rounded-lg border border-[#EBDDCD] shadow-2xs" x-text="padZero(selectedHour)"></span>
                        <span class="text-[#E09028] animate-pulse">:</span>
                        <span class="bg-white px-2 py-0.5 rounded-lg border border-[#EBDDCD] shadow-2xs" x-text="padZero(selectedMinute)"></span>
                    </div>
                </div>

                {{-- 2 Cột Cuộn Song Song --}}
                <div class="grid grid-cols-2 gap-2 h-48">
                    
                    {{-- Cột Giờ (00 - 23) --}}
                    <div class="flex flex-col">
                        <div class="text-[10px] font-bold text-center text-[#7E4A28] mb-1">GIỜ</div>
                        <div class="overflow-y-auto flex-1 pr-1 space-y-1 rounded-xl p-1 bg-white border border-[#EBDDCD] max-h-40 scrollbar-cute"
                             x-ref="hourList">
                            <template x-for="h in 24" :key="h - 1">
                                <button type="button"
                                        @click="selectHour(h - 1)"
                                        class="w-full py-1 text-center text-xs font-semibold rounded-lg transition-all"
                                        :class="selectedHour === (h - 1) ? 'bg-gradient-to-r from-[#E09028] to-[#5C3219] text-white shadow-xs' : 'text-[#2E190E] hover:bg-[#FFF5E6]'">
                                    <span x-text="padZero(h - 1)"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Cột Phút (00 - 59) --}}
                    <div class="flex flex-col">
                        <div class="text-[10px] font-bold text-center text-[#7E4A28] mb-1">PHÚT</div>
                        <div class="overflow-y-auto flex-1 pr-1 space-y-1 rounded-xl p-1 bg-white border border-[#EBDDCD] max-h-40 scrollbar-cute"
                             x-ref="minuteList">
                            <template x-for="m in 60" :key="m - 1">
                                <button type="button"
                                        @click="selectMinute(m - 1)"
                                        class="w-full py-1 text-center text-xs font-semibold rounded-lg transition-all"
                                        :class="selectedMinute === (m - 1) ? 'bg-gradient-to-r from-[#E09028] to-[#5C3219] text-white shadow-xs' : 'text-[#2E190E] hover:bg-[#FFF5E6]'">
                                    <span x-text="padZero(m - 1)"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                </div>

                {{-- Nút Xong / Xác nhận --}}
                <div class="mt-3 pt-2">
                    <button type="button"
                            @click="confirmSelection()"
                            class="w-full py-2 bg-gradient-to-r from-[#E09028] to-[#5C3219] hover:from-[#5C3219] hover:to-[#2C160B] text-white font-bold rounded-xl text-xs shadow-md shadow-[#5C3219]/20 transition flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        <span>Xác Nhận</span>
                    </button>
                </div>

            </div>

        </div>

    </div>

</div>
