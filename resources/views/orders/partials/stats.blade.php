<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Tổng đơn hàng</div>
            <div class="stat-value">{{ $stats['total'] ?? $orders->total() }}</div>
            <div class="stat-subtext text-[#8E8076]">Tất cả thời gian</div>
        </div>
        <div class="stat-icon brown"><i class="fa-solid fa-cart-shopping"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Chờ xác nhận</div>
            <div class="stat-value text-amber-600">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stat-subtext text-amber-700">{{ $isStaff ? 'Cần duyệt & đóng gói' : 'Đang chờ cửa hàng xác nhận' }}</div>
        </div>
        <div class="stat-icon amber"><i class="fa-solid fa-clock"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đang giao hàng</div>
            <div class="stat-value text-cyan-700">{{ $stats['shipping'] ?? 0 }}</div>
            <div class="stat-subtext text-cyan-600">Shipper đang vận chuyển</div>
        </div>
        <div class="stat-icon cyan"><i class="fa-solid fa-truck-fast"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Đã hoàn thành</div>
            <div class="stat-value text-emerald-700">{{ $stats['completed'] ?? 0 }}</div>
            <div class="stat-subtext text-emerald-600">Giao thành công</div>
        </div>
        <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
    </div>
</div>
