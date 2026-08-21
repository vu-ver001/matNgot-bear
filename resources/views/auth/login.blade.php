@extends('layouts.customer')

@section('title', 'Đăng Nhập - Mật Ngọt Bear')

@section('content')
<div class="section-container" style="padding: 3.5rem 1.5rem; max-width: 680px; margin: 0 auto;">
    <div style="background: var(--bg-card); border: 2px dashed var(--border); border-radius: var(--radius-xl); padding: 3rem 2.25rem; box-shadow: var(--shadow-card); text-align: center;">
        
        <!-- Icon -->
        <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--honey-light); color: var(--honey-dark); display: inline-flex; align-items: center; justify-content: center; font-size: 30px; margin-bottom: 1.25rem;">
            <i class="fa-solid fa-right-to-bracket"></i>
        </div>

        <h2 style="font-family: 'Montserrat', sans-serif; font-size: 22px; font-weight: 800; color: var(--text-main); margin-bottom: 8px;">
            Đăng Nhập Tài Khoản
        </h2>
        <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6; max-width: 500px; margin: 0 auto 1.5rem auto;">
            Chức năng Đăng Nhập, Đăng Ký và Xác Thực tài khoản do thành viên trong nhóm phụ trách và sẽ được liên kết hoàn thiện sau.
        </p>

        <!-- Hướng dẫn bạn nhóm -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px 18px; text-align: left; font-size: 12px; color: var(--text-main); line-height: 1.7; margin-bottom: 1.75rem;">
            <div style="font-weight: 800; color: #8D6E63; margin-bottom: 4px;">
                <i class="fa-solid fa-circle-info"></i> HƯỚNG DẪN KẾT NỐI CHO THÀNH VIÊN NHÓM:
            </div>
            <div>
                Bạn chỉ cần gắn Form đăng nhập hoặc Auth Controller của bạn vào route <code>route('login')</code> tại <code>routes/auth.php</code>:
            </div>
            <div style="margin-top: 4px; font-family: monospace; font-size: 11.5px; background: rgba(141, 110, 99, 0.1); padding: 4px 8px; border-radius: 4px;">
                Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
            </div>
        </div>

        <!-- Tiện ích 1-Click Đăng Nhập Theo Vai Trò để Test -->
        <div style="background: #FFF8E1; border: 1px solid #FFE082; border-radius: var(--radius-md); padding: 16px; margin-bottom: 1.75rem; text-align: center;">
            <div style="font-size: 12.5px; font-weight: 800; color: #795548; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fa-solid fa-wand-magic-sparkles" style="color: #E59819;"></i> Đăng Nhập Nhanh 1-Click Để Test Giao Diện:
            </div>
            <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('switch-role', ['role' => 'customer']) }}" class="btn-brown-main" style="font-size: 11.5px; padding: 7px 14px; background: linear-gradient(135deg, #BCAAA4, #8D6E63);">
                    <i class="fa-solid fa-user"></i> Khách Hàng
                </a>
                <a href="{{ route('switch-role', ['role' => 'staff']) }}" class="btn-brown-main" style="font-size: 11.5px; padding: 7px 14px; background: linear-gradient(135deg, #A1887F, #6D4C41);">
                    <i class="fa-solid fa-headset"></i> Nhân Viên (Staff)
                </a>
                <a href="{{ route('switch-role', ['role' => 'admin']) }}" class="btn-honey-main" style="font-size: 11.5px; padding: 7px 14px;">
                    <i class="fa-solid fa-shield-halved"></i> Quản Trị (Admin)
                </a>
            </div>
        </div>

        <!-- Nút Về Trang Chủ -->
        <div>
            <a href="{{ route('home') }}" class="btn-brown-main" style="font-size: 13px; padding: 10px 24px;">
                <i class="fa-solid fa-house"></i> Quay Về Trang Chủ
            </a>
        </div>
    </div>
</div>
@endsection
