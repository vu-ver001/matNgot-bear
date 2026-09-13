@extends('layouts.admin-dashboard')

@section('page-title', 'Cấu hình cổng & API thanh toán')

@section('content')
<div class="px-2 sm:px-4 lg:px-6 max-w-7xl mx-auto font-sans" x-data="{
    copiedWebhook: false,
    copiedMomoIpn: false,
    copiedMomoReturn: false,
    showMomoSecret: false,
    copyWebhook() {
        navigator.clipboard.writeText('{{ $settings['webhook_url'] }}');
        this.copiedWebhook = true;
        setTimeout(() => this.copiedWebhook = false, 2500);
    },
    copyMomoIpn() {
        navigator.clipboard.writeText('{{ $settings['momo_ipn_url'] }}');
        this.copiedMomoIpn = true;
        setTimeout(() => this.copiedMomoIpn = false, 2500);
    },
    copyMomoReturn() {
        navigator.clipboard.writeText('{{ $settings['momo_return_url'] }}');
        this.copiedMomoReturn = true;
        setTimeout(() => this.copiedMomoReturn = false, 2500);
    }
}">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumb :items="[
                ['label' => 'Trang chủ', 'url' => route('admin.dashboard')],
                ['label' => 'Quản lý thanh toán', 'url' => route('admin.payments.index')],
                ['label' => 'Cấu hình cổng & API']
            ]" class="mb-2 text-xs" />
            <h1 class="text-2xl sm:text-3xl font-black text-[#2C1408] tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#5C3219] to-[#8C5835] text-white flex items-center justify-center text-lg shadow-sm">
                    ⚙️
                </span>
                <span>Cấu Hình Cổng &amp; API Thanh Toán</span>
            </h1>
            <p class="text-xs sm:text-sm text-[#786B61] mt-1">
                Chỉ Admin (Chủ shop) có quyền thay đổi thông tin số tài khoản ngân hàng VietQR, API SePAY và Webhook.
            </p>
        </div>

        <a href="{{ route('admin.payments.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-[#EBDDCD] hover:border-[#E08A1E] text-[#5C3219] font-bold text-xs shadow-xs hover:shadow-sm transition">
            <i class="fa-solid fa-arrow-left text-[#E08A1E]"></i>
            <span>Quay lại</span>
        </a>
    </div>

    {{-- Flash Notifications --}}
    @if (session('success'))
        <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-900 rounded-2xl flex items-center justify-between text-xs sm:text-sm shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">✓</span>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-900 rounded-2xl text-xs sm:text-sm shadow-xs">
            <div class="font-bold mb-1 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>Vui lòng kiểm tra lại thông tin cấu hình:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-rose-700">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.payments.saveSettings') }}" class="space-y-6">
        @csrf

        {{-- 1. Cấu hình VietQR Napas 247 --}}
        <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xs overflow-hidden">
            <div class="p-5 border-b border-[#F0E6D8] bg-[#FAF8F5] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-[#C2751D] flex items-center justify-center text-base font-bold">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-[#2C1408]">Tài Khoản Ngân Hàng Nhận Tiền (VietQR)</h3>
                        <p class="text-[11px] text-[#786B61]">Khách hàng quét mã này khi thanh toán chuyển khoản đơn hàng.</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700">Đang kích hoạt</span>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Mã ngân hàng --}}
                <div>
                    <label class="block text-xs font-bold text-[#5C3219] mb-1">Mã ngân hàng (Bank Code) <span class="text-rose-500">*</span></label>
                    <select name="vietqr_bank_code" class="w-full py-2.5 px-3 text-xs rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E]">
                        @php
                            $banks = [
                                'MB' => 'MB Bank (Ngân hàng Quân Đội)',
                                'VCB' => 'Vietcombank (Ngoại thương Việt Nam)',
                                'TCB' => 'Techcombank (Kỹ Thương)',
                                'CTG' => 'VietinBank (Công Thương Việt Nam)',
                                'BIDV' => 'BIDV (Đầu tư & Phát triển)',
                                'ACB' => 'ACB (Á Châu)',
                                'VPB' => 'VPBank (Việt Nam Thịnh Vượng)',
                                'TPB' => 'TPBank (Tiên Phong)',
                                'VIB' => 'VIB (Quốc Tế)',
                                'MSB' => 'MSB (Hàng Hải)',
                                'STB' => 'Sacombank (Sài Gòn Thương Tín)',
                                'OCB' => 'OCB (Phương Đông)',
                            ];
                            $currBank = old('vietqr_bank_code', $settings['vietqr_bank_code']);
                        @endphp
                        @foreach ($banks as $bCode => $bName)
                            <option value="{{ $bCode }}" @selected($currBank === $bCode)>{{ $bCode }} - {{ $bName }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">Mã chuẩn VietQR Napas (ví dụ: MB, VCB, TCB,...)</p>
                </div>

                {{-- Tên hiển thị ngân hàng --}}
                <div>
                    <label class="block text-xs font-bold text-[#5C3219] mb-1">Tên ngân hàng đầy đủ <span class="text-rose-500">*</span></label>
                    <input type="text" name="vietqr_bank_name" value="{{ old('vietqr_bank_name', $settings['vietqr_bank_name']) }}"
                           placeholder="MB Bank (Ngân hàng Quân Đội)"
                           class="w-full py-2.5 px-3 text-xs rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E]">
                </div>

                {{-- Số tài khoản nhận tiền --}}
                <div>
                    <label class="block text-xs font-bold text-[#5C3219] mb-1">Số tài khoản nhận tiền (STK) <span class="text-rose-500">*</span></label>
                    <input type="text" name="vietqr_account_number" value="{{ old('vietqr_account_number', $settings['vietqr_account_number']) }}"
                           placeholder="0377466205"
                           class="w-full py-2.5 px-3 text-xs font-mono font-bold rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E]">
                    <p class="text-[10px] text-gray-400 mt-1">Số tài khoản chính xác để tạo mã QR nạp tiền.</p>
                </div>

                {{-- Tên chủ tài khoản --}}
                <div>
                    <label class="block text-xs font-bold text-[#5C3219] mb-1">Tên chủ tài khoản (In hoa không dấu) <span class="text-rose-500">*</span></label>
                    <input type="text" name="vietqr_account_name" value="{{ old('vietqr_account_name', $settings['vietqr_account_name']) }}"
                           placeholder="NGUYỄN NGỌC ANH"
                           class="w-full py-2.5 px-3 text-xs font-bold uppercase rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E]">
                </div>
            </div>
        </div>

        {{-- 2. Cấu hình Cổng Tự Động SePAY & Webhook --}}
        <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xs overflow-hidden">
            <div class="p-5 border-b border-[#F0E6D8] bg-[#FAF8F5] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-base font-bold">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-[#2C1408]">Cấu Hình API SePAY &amp; Webhook Tự Động</h3>
                        <p class="text-[11px] text-[#786B61]">Tự động đối soát và xác nhận thanh toán khi có biến động số dư ngân hàng.</p>
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="sepay_active" value="1" @checked(old('sepay_active', $settings['sepay_active'])) class="w-4 h-4 rounded text-[#E08A1E] focus:ring-[#E08A1E]">
                    <span class="text-xs font-bold text-[#5C3219]">Kích hoạt SePAY</span>
                </label>
            </div>

            <div class="p-6 space-y-4">
                {{-- Webhook URL to copy --}}
                <div class="p-4 rounded-2xl bg-[#FAF6EE] border border-[#EBDDCD]">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                        <span class="text-xs font-bold text-[#5C3219] flex items-center gap-1.5">
                            <i class="fa-solid fa-link text-[#E08A1E]"></i>
                            <span>Địa chỉ Webhook URL (Dán vào SePAY):</span>
                        </span>
                        <button type="button" @click="copyWebhook()" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-[#EBDDCD] hover:border-[#E08A1E] text-[#5C3219] font-bold text-[11px] transition shadow-2xs">
                            <i class="fa-regular fa-copy"></i>
                            <span x-text="copiedWebhook ? '✓ Đã sao chép!' : 'Sao chép URL'"></span>
                        </button>
                    </div>
                    <div class="font-mono text-xs text-blue-700 font-bold break-all select-all bg-white p-2.5 rounded-xl border border-[#F0E6D8]">
                        {{ $settings['webhook_url'] }}
                    </div>
                    <p class="text-[11px] text-[#786B61] mt-2">
                        Đăng nhập vào <strong>my.sepay.vn</strong> &rarr; Cấu hình Webhook &rarr; Thêm Webhook URL trên và chọn sự kiện "Biến động số dư".
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- API Key --}}
                    <div>
                        <label class="block text-xs font-bold text-[#5C3219] mb-1">SePAY API Key</label>
                        <input type="text" name="sepay_api_key" value="{{ old('sepay_api_key', $settings['sepay_api_key']) }}"
                               placeholder="F8WAYM1QBA2FNXG4..."
                               class="w-full py-2.5 px-3 text-xs font-mono rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E]">
                        <p class="text-[10px] text-gray-400 mt-1">Dùng để tra cứu đối soát trực tiếp theo thời gian thực.</p>
                    </div>

                    {{-- Webhook Token / Secret --}}
                    <div>
                        <label class="block text-xs font-bold text-[#5C3219] mb-1">SePAY Webhook Token (Tùy chọn)</label>
                        <input type="text" name="sepay_webhook_token" value="{{ old('sepay_webhook_token', $settings['sepay_webhook_token']) }}"
                               placeholder="Nhập secret token nếu cài đặt xác thực..."
                               class="w-full py-2.5 px-3 text-xs font-mono rounded-xl border border-[#EBDDCD] focus:border-[#E08A1E] focus:ring-1 focus:ring-[#E08A1E]">
                        <p class="text-[10px] text-gray-400 mt-1">Mã bí mật xác thực chữ ký Webhook gửi từ SePAY.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Cấu hình Cổng Thanh Toán MoMo Gateway & Ví MoMo --}}
        <div class="bg-white rounded-3xl border border-[#EBDDCD] shadow-xs overflow-hidden">
            <div class="p-5 border-b border-[#F0E6D8] bg-[#FAF8F5] flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#A50064] to-[#C2185B] text-white flex items-center justify-center text-base font-bold shadow-xs">
                        <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5h-2v-5h2v5zm0-6.5h-2V8h2v2zm4 6.5h-2v-5h2v5zm0-6.5h-2V8h2v2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-[#2C1408]">Cổng Thanh Toán MoMo Gateway (M4B & Ví MoMo)</h3>
                        <p class="text-[11px] text-[#786B61]">Tích hợp cổng thanh toán trực tuyến MoMo All-in-One: quét mã QR, App MoMo và Thẻ ATM.</p>
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="momo_active" value="1" @checked(old('momo_active', $settings['momo_active'] ?? true)) class="w-4 h-4 rounded text-[#A50064] focus:ring-[#A50064]">
                    <span class="text-xs font-bold text-[#5C3219]">Kích hoạt MoMo</span>
                </label>
            </div>

            <div class="p-6 space-y-4">
                {{-- MoMo Webhook IPN & Return URL boxes to copy --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- IPN Webhook --}}
                    <div class="p-3.5 rounded-2xl bg-[#FFF5F8] border border-[#FAD2E1]">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <span class="text-xs font-bold text-[#A50064] flex items-center gap-1.5">
                                <i class="fa-solid fa-bell text-[11px]"></i>
                                <span>MoMo IPN Webhook URL:</span>
                            </span>
                            <button type="button" @click="copyMomoIpn()" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-[#FAD2E1] hover:border-[#A50064] text-[#A50064] font-bold text-[10px] transition shadow-2xs">
                                <i class="fa-regular fa-copy"></i>
                                <span x-text="copiedMomoIpn ? '✓ Đã chép!' : 'Sao chép'"></span>
                            </button>
                        </div>
                        <div class="font-mono text-[11px] text-[#A50064] font-bold break-all select-all bg-white p-2 rounded-xl border border-[#FAD2E1]">
                            {{ $settings['momo_ipn_url'] }}
                        </div>
                    </div>

                    {{-- Return URL --}}
                    <div class="p-3.5 rounded-2xl bg-[#FFF5F8] border border-[#FAD2E1]">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <span class="text-xs font-bold text-[#A50064] flex items-center gap-1.5">
                                <i class="fa-solid fa-arrow-turn-down text-[11px]"></i>
                                <span>MoMo Redirect Return URL:</span>
                            </span>
                            <button type="button" @click="copyMomoReturn()" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white border border-[#FAD2E1] hover:border-[#A50064] text-[#A50064] font-bold text-[10px] transition shadow-2xs">
                                <i class="fa-regular fa-copy"></i>
                                <span x-text="copiedMomoReturn ? '✓ Đã chép!' : 'Sao chép'"></span>
                            </button>
                        </div>
                        <div class="font-mono text-[11px] text-[#A50064] font-bold break-all select-all bg-white p-2 rounded-xl border border-[#FAD2E1]">
                            {{ $settings['momo_return_url'] }}
                        </div>
                    </div>
                </div>

                {{-- Key credentials --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Partner Code --}}
                    <div>
                        <label class="block text-xs font-bold text-[#5C3219] mb-1">Partner Code</label>
                        <input type="text" name="momo_partner_code" value="{{ old('momo_partner_code', $settings['momo_partner_code']) }}"
                               placeholder="MOMO"
                               class="w-full py-2.5 px-3 text-xs font-mono font-bold rounded-xl border border-[#EBDDCD] focus:border-[#A50064] focus:ring-1 focus:ring-[#A50064]">
                        <p class="text-[10px] text-gray-400 mt-1">Mã định danh đối tác (Mặc định test: MOMO)</p>
                    </div>

                    {{-- Access Key --}}
                    <div>
                        <label class="block text-xs font-bold text-[#5C3219] mb-1">Access Key</label>
                        <input type="text" name="momo_access_key" value="{{ old('momo_access_key', $settings['momo_access_key']) }}"
                               placeholder="F8BBA842ECF85"
                               class="w-full py-2.5 px-3 text-xs font-mono rounded-xl border border-[#EBDDCD] focus:border-[#A50064] focus:ring-1 focus:ring-[#A50064]">
                        <p class="text-[10px] text-gray-400 mt-1">Khóa truy cập API được MoMo cung cấp</p>
                    </div>

                    {{-- Secret Key --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-[#5C3219]">Secret Key</label>
                            <button type="button" @click="showMomoSecret = !showMomoSecret" class="text-[10px] text-[#A50064] font-bold hover:underline">
                                <span x-text="showMomoSecret ? 'Ẩn' : 'Hiện'"></span>
                            </button>
                        </div>
                        <input :type="showMomoSecret ? 'text' : 'password'" name="momo_secret_key" value="{{ old('momo_secret_key', $settings['momo_secret_key']) }}"
                               placeholder="K951B6PE1waDMi640xX08PD3vg6EkVlz"
                               class="w-full py-2.5 px-3 text-xs font-mono rounded-xl border border-[#EBDDCD] focus:border-[#A50064] focus:ring-1 focus:ring-[#A50064]">
                        <p class="text-[10px] text-gray-400 mt-1">Khóa bí mật tạo chữ ký SHA256</p>
                    </div>
                </div>

                {{-- Receiver Info --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    {{-- SĐT Ví --}}
                    <div>
                        <label class="block text-xs font-bold text-[#5C3219] mb-1">Số điện thoại Ví MoMo nhận tiền</label>
                        <input type="text" name="momo_phone" value="{{ old('momo_phone', $settings['momo_phone']) }}"
                               placeholder="0377466205"
                               class="w-full py-2.5 px-3 text-xs font-mono font-bold rounded-xl border border-[#EBDDCD] focus:border-[#A50064] focus:ring-1 focus:ring-[#A50064]">
                        <p class="text-[10px] text-gray-400 mt-1">Dùng tạo mã QR P2P trực tiếp trên trang thanh toán</p>
                    </div>

                    {{-- Tên chủ ví --}}
                    <div>
                        <label class="block text-xs font-bold text-[#5C3219] mb-1">Tên chủ Ví MoMo (In hoa)</label>
                        <input type="text" name="momo_name" value="{{ old('momo_name', $settings['momo_name']) }}"
                               placeholder="NGUYỄN NGỌC ANH"
                               class="w-full py-2.5 px-3 text-xs font-bold uppercase rounded-xl border border-[#EBDDCD] focus:border-[#A50064] focus:ring-1 focus:ring-[#A50064]">
                        <p class="text-[10px] text-gray-400 mt-1">Tên hiển thị cho người chuyển tiền</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.payments.index') }}" 
               class="px-5 py-2.5 rounded-xl border border-[#EBDDCD] bg-white text-[#786B61] font-bold text-xs hover:bg-gray-50 transition">
                Hủy Bỏ
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-[#5C3219] hover:bg-[#432310] text-white font-extrabold text-xs shadow-md hover:shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Lưu Cấu Hình Thanh Toán</span>
            </button>
        </div>
    </form>

</div>
@endsection
