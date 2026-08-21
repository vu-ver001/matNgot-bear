@extends('layouts.customer')

@section('title', $pageTitle ?? 'Khu Vực Khách Hàng')

@section('content')
<div class="section-container" style="padding: 4rem 1.5rem; max-width: 760px; margin: 0 auto; text-align: center;">
    <div style="background: var(--bg-card); border: 2px dashed var(--border); border-radius: var(--radius-xl); padding: 3.5rem 2rem; box-shadow: var(--shadow-card);">
        <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--honey-light); color: var(--honey-dark); display: inline-flex; align-items: center; justify-content: center; font-size: 34px; margin-bottom: 1.5rem;">
            <i class="{{ $pageIcon ?? 'fa-solid fa-box-open' }}"></i>
        </div>
        <h2 style="font-family: 'Montserrat', sans-serif; font-size: 24px; font-weight: 800; color: var(--text-main); margin-bottom: 10px;">
            {{ $pageTitle ?? 'Trang Khách Hàng' }}
        </h2>
        <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; max-width: 520px; margin: 0 auto 1.75rem auto;">
            {{ $pageDesc ?? 'Khu vực tính năng này do thành viên nhóm phụ trách và sẽ được liên kết hoàn thiện sau.' }}
        </p>

        <!-- Hướng dẫn bạn nhóm -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px 18px; text-align: left; font-size: 12px; color: var(--text-main); line-height: 1.7; margin-bottom: 2rem;">
            <div style="font-weight: 800; color: #8D6E63; margin-bottom: 4px;">
                <i class="fa-solid fa-circle-info"></i> HƯỚNG DẪN KẾT NỐI CHO THÀNH VIÊN NHÓM:
            </div>
            <div>
                Bạn chỉ cần thay View hoặc Controller của bạn vào route tương ứng tại file <code>routes/customer.php</code>:
            </div>
            <div style="margin-top: 4px; font-family: monospace; font-size: 11.5px; background: rgba(141, 110, 99, 0.1); padding: 4px 8px; border-radius: 4px;">
                {{ $routeCode ?? "Route::get('/cart', [CartController::class, 'index'])->name('cart');" }}
            </div>
        </div>

        <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('home') }}" class="btn-brown-main" style="font-size: 13px; padding: 10px 22px;">
                <i class="fa-solid fa-house"></i> Về Trang Chủ
            </a>
            <a href="{{ route('products.index') }}" class="btn-honey-main" style="font-size: 13px; padding: 10px 22px;">
                <i class="fa-solid fa-paw"></i> Xem Sản Phẩm Gấu Bông
            </a>
        </div>
    </div>
</div>
@endsection
