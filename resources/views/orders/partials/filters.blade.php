<form method="GET" action="{{ route($routePrefix.'.index') }}" class="toolbar-grid orders-filter-bar flex flex-wrap items-center gap-3 mb-5">
    @php
        $resetParams = array_filter([
            'order_status' => request('order_status'),
            'customer_id' => request()->routeIs('admin.orders.*') ? request('customer_id') : null,
        ], fn ($value) => $value !== null && $value !== '');
    @endphp

    @if (request('order_status'))
        <input type="hidden" name="order_status" value="{{ request('order_status') }}">
    @endif

    @if (request()->routeIs('admin.orders.*') && request()->filled('customer_id'))
        <input type="hidden" name="customer_id" value="{{ request('customer_id') }}">
    @endif

    <!-- 1. Search Box -->
    <div class="search-box flex-1 min-w-[240px] relative">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400 text-sm pointer-events-none"></i>
        <input type="text" 
               name="search" 
               value="{{ request('search') }}"
               placeholder="Tìm kiếm mã đơn, tên người nhận, số điện thoại..."
               class="input-control w-full pl-10 pr-4 py-2.5 bg-[#FDFBF7] border border-[#EADFCF] rounded-xl text-sm text-[#4E342E] placeholder:text-stone-400 focus:outline-none focus:border-[#B87309] focus:ring-2 focus:ring-[#B87309]/15 transition">
    </div>

    <!-- 2. Payment Status Filter -->
    <div class="filter-item-wrap w-full sm:w-auto min-w-[170px]">
        <select name="payment_status" 
                class="select-control w-full px-3.5 py-2.5 bg-[#FDFBF7] border border-[#EADFCF] rounded-xl text-sm text-[#4E342E] focus:outline-none focus:border-[#B87309] focus:ring-2 focus:ring-[#B87309]/15 cursor-pointer transition">
            <option value="">-- Thanh toán: Tất cả --</option>
            @foreach (['UNPAID' => 'Chưa thanh toán', 'PENDING' => 'Chờ xác nhận', 'PAID' => 'Đã thanh toán', 'FAILED' => 'Thất bại', 'REFUNDED' => 'Đã hoàn tiền'] as $value => $label)
                <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <!-- 3. Per Page Selector (Chỉ hiển thị cho Staff / Admin) -->
    @if(request()->routeIs('admin.*') || request()->routeIs('staff.*') || !empty($isStaff))
        <div class="filter-item-wrap w-full sm:w-auto min-w-[135px]">
            <select name="per_page" 
                    class="select-control w-full px-3.5 py-2.5 bg-[#FDFBF7] border border-[#EADFCF] rounded-xl text-sm text-[#4E342E] focus:outline-none focus:border-[#B87309] focus:ring-2 focus:ring-[#B87309]/15 cursor-pointer transition" 
                    onchange="this.form.submit()">
                <option value="15" @selected(request('per_page') == 15)>15 đơn / trang</option>
                <option value="30" @selected(request('per_page') == 30)>30 đơn / trang</option>
                <option value="50" @selected(request('per_page') == 50)>50 đơn / trang</option>
                <option value="100" @selected(request('per_page') == 100)>100 đơn / trang</option>
            </select>
        </div>
    @endif

    <!-- 4. Action Buttons -->
    <div class="flex items-center gap-2 shrink-0">
        <button type="submit" class="btn btn-primary inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#E08A1E] hover:bg-[#B87309] text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-2xs cursor-pointer">
            <i class="fa-solid fa-filter text-xs"></i>
            <span>Lọc</span>
        </button>
        @if (request()->hasAny(['search', 'payment_status', 'order_status', 'per_page']))
            <a href="{{ route($routePrefix.'.index', $resetParams) }}"
               class="btn btn-outline inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-white hover:bg-stone-50 text-[#795548] border border-[#EADFCF] font-semibold rounded-xl text-xs sm:text-sm transition shadow-2xs" 
               title="Xóa bộ lọc và đặt lại">
                <i class="fa-solid fa-rotate-left text-xs"></i>
                <span>Đặt lại</span>
            </a>
        @endif
    </div>
</form>
