<form method="GET" action="{{ route($routePrefix.'.index') }}" class="toolbar-grid">
    @if (request('order_status'))
        <input type="hidden" name="order_status" value="{{ request('order_status') }}">
    @endif

    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Tìm mã đơn, tên người nhận, số điện thoại..."
               class="input-control">
    </div>

    <div style="min-width: 180px;">
        <select name="payment_status" class="select-control">
            <option value="">-- Thanh toán: Tất cả --</option>
            @foreach (['UNPAID' => 'Chưa thanh toán', 'PENDING' => 'Chờ xác nhận', 'PAID' => 'Đã thanh toán', 'FAILED' => 'Thất bại', 'REFUNDED' => 'Đã hoàn tiền'] as $value => $label)
                <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex items-center gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-filter text-xs"></i> Lọc
        </button>
        @if (request()->hasAny(['search', 'payment_status', 'order_status']))
            <a href="{{ route($routePrefix.'.index') }}" class="btn btn-outline" title="Xóa bộ lọc">
                <i class="fa-solid fa-rotate-left text-xs"></i> Đặt lại
            </a>
        @endif
    </div>
</form>
