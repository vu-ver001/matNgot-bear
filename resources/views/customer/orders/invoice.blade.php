<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn điện tử #{{ $order->order_code }} - Mật Ngọt Bear</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS (via Vite or CDN for standalone invoice print) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Be Vietnam Pro"', 'sans-serif'],
                    },
                    colors: {
                        honey: '#E08A1E',
                        'honey-dark': '#8C4A19',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #F8F5F0;
            color: #2D241E;
        }

        @media print {
            body {
                background-color: #FFFFFF !important;
                color: #000000 !important;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            .invoice-wrapper {
                box-shadow: none !important;
                border: 1px solid #E5E7EB !important;
                margin: 0 auto !important;
                max-width: 100% !important;
                border-radius: 0 !important;
                padding: 24px !important;
            }
            @page {
                size: A4 portrait;
                margin: 10mm 12mm;
            }
        }

        .stamp-paid {
            border: 3px solid #16A34A;
            color: #16A34A;
            transform: rotate(-10deg);
            display: inline-block;
            padding: 4px 16px;
            font-weight: 800;
            font-size: 14px;
            text-transform: uppercase;
            border-radius: 8px;
            letter-spacing: 1px;
        }

        .stamp-unpaid {
            border: 3px solid #DC2626;
            color: #DC2626;
            transform: rotate(-10deg);
            display: inline-block;
            padding: 4px 16px;
            font-weight: 800;
            font-size: 14px;
            text-transform: uppercase;
            border-radius: 8px;
            letter-spacing: 1px;
        }
    </style>
</head>
<body class="py-6 sm:py-10 px-4">

    <!-- Top Action Toolbar (Hidden when printing) -->
    <div class="max-w-3xl mx-auto mb-6 flex flex-wrap items-center justify-between gap-4 no-print">
        <a href="{{ route('customer.orders.show', $order) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white hover:bg-amber-50 text-[#8C4A19] font-bold text-sm border border-[#E8D9C8] shadow-xs transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Quay lại chi tiết đơn hàng</span>
        </a>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#E08A1E] to-[#8C4A19] hover:from-[#C77815] hover:to-[#733C14] text-white font-extrabold text-sm shadow-md shadow-[#E08A1E]/30 transition transform hover:-translate-y-0.5 cursor-pointer">
                <i class="fa-solid fa-print"></i>
                <span>In Hóa Đơn / Lưu PDF</span>
            </button>
        </div>
    </div>

    <!-- Main Printable Invoice Card -->
    <div class="invoice-wrapper max-w-3xl mx-auto bg-white rounded-3xl border border-[#EBDDCD] shadow-xl p-8 sm:p-12 relative overflow-hidden">
        
        <!-- Watermark Bear Background -->
        <div class="absolute right-6 top-24 opacity-[0.03] select-none pointer-events-none text-9xl">
            🧸
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-[#F0E6DA] pb-8">
            <div>
                <div class="flex items-center gap-2 text-2xl font-black text-[#2B1810]">
                    <span class="text-3xl">🧸</span>
                    <span>MẬT NGỌT BEAR</span>
                </div>
                <p class="text-xs text-[#7D6B5D] mt-1 font-medium italic">Thế giới gấu bông & Quà tặng ngọt ngào</p>
                <div class="text-xs text-[#6B5A4D] space-y-1 mt-3">
                    <p><i class="fa-solid fa-location-dot w-4 text-[#E08A1E]"></i> 123 Đường Cầu Giấy, Hà Nội</p>
                    <p><i class="fa-solid fa-phone w-4 text-[#E08A1E]"></i> Hotline: <strong>0377.466.205</strong></p>
                    <p><i class="fa-solid fa-envelope w-4 text-[#E08A1E]"></i> contact@matngotbear.vn</p>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <h1 class="text-xl sm:text-2xl font-black text-[#2B1810] tracking-tight uppercase">HÓA ĐƠN BÁN HÀNG</h1>
                <p class="text-xs text-[#7D6B5D] mt-0.5">Electronic Customer Invoice</p>
                
                <div class="mt-3 space-y-1 text-xs">
                    <p class="font-bold text-[#8C4A19] text-sm">Số HĐ: #{{ $order->order_code }}</p>
                    <p class="text-[#7D6B5D]">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <div class="pt-2">
                        @if($order->payment_status === 'PAID')
                            <div class="stamp-paid">
                                <i class="fa-solid fa-check-double"></i> ĐÃ THANH TOÁN
                            </div>
                        @else
                            <div class="stamp-unpaid">
                                <i class="fa-solid fa-clock"></i> CHƯA THANH TOÁN
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Columns -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-[#F0E6DA] text-xs">
            <!-- Customer & Recipient Info -->
            <div class="space-y-2">
                <h3 class="font-extrabold text-[#2B1810] text-sm flex items-center gap-1.5 uppercase tracking-wide">
                    <i class="fa-solid fa-user text-[#E08A1E]"></i> Thông Tin Người Nhận
                </h3>
                <div class="bg-[#FFFDF9] border border-[#F2DECA] rounded-2xl p-4 space-y-1.5 leading-relaxed text-[#4A3B32]">
                    <p><strong>Họ tên:</strong> {{ $order->recipient_name }}</p>
                    <p><strong>Số điện thoại:</strong> {{ $order->recipient_phone }}</p>
                    <p><strong>Địa chỉ giao:</strong> {{ $order->recipient_address }}</p>
                    @if($order->note)
                        <p class="pt-1 text-[#8C4A19]"><strong>Ghi chú:</strong> {{ $order->note }}</p>
                    @endif
                </div>
            </div>

            <!-- Payment & Delivery Info -->
            <div class="space-y-2">
                <h3 class="font-extrabold text-[#2B1810] text-sm flex items-center gap-1.5 uppercase tracking-wide">
                    <i class="fa-solid fa-receipt text-[#E08A1E]"></i> Thông Tin Thanh Toán
                </h3>
                <div class="bg-[#FFFDF9] border border-[#F2DECA] rounded-2xl p-4 space-y-1.5 leading-relaxed text-[#4A3B32]">
                    <p>
                        <strong>Phương thức:</strong> 
                        @if($order->payment_method === 'BANK_TRANSFER')
                            🏦 Chuyển khoản QR (MB Bank)
                        @elseif($order->payment_method === 'CARD')
                            💳 Cổng thanh toán VNPAY (ATM/Visa/QR)
                        @elseif($order->payment_method === 'E_WALLET')
                            👛 Ví điện tử MoMo
                        @else
                            💵 Thanh toán khi nhận hàng (COD)
                        @endif
                    </p>
                    <p>
                        <strong>Trạng thái đơn:</strong> 
                        <span class="font-bold text-[#E08A1E]">{{ $order->status_label ?? $order->order_status }}</span>
                    </p>
                    @php
                        $paidPayment = $order->payments->firstWhere('status', 'PAID');
                    @endphp
                    @if($paidPayment && $paidPayment->transaction_ref)
                        <p><strong>Mã giao dịch:</strong> <code class="font-mono bg-amber-50 px-1.5 py-0.5 rounded text-[#8C4A19]">{{ $paidPayment->transaction_ref }}</code></p>
                    @endif
                    @if($order->payment_method === 'COD')
                        <p class="text-rose-700 font-bold">
                            ⚠️ Số tiền shipper thu khi giao: {{ number_format($order->total_amount, 0, ',', '.') }}đ
                        </p>
                    @else
                        <p class="text-emerald-700 font-bold">
                            ✓ Đã thanh toán trực tuyến (Thu hộ: 0đ)
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="py-6 border-b border-[#F0E6DA]">
            <h3 class="font-extrabold text-[#2B1810] text-sm uppercase tracking-wide mb-3 flex items-center gap-1.5">
                <i class="fa-solid fa-box-open text-[#E08A1E]"></i> Danh Sách Sản Phẩm
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-[#FFF8F0] border-y border-[#EBDDCD] text-[#7D6B5D] font-bold">
                            <th class="py-2.5 px-3 w-10 text-center">STT</th>
                            <th class="py-2.5 px-3">Tên sản phẩm</th>
                            <th class="py-2.5 px-3 text-center">Kích thước</th>
                            <th class="py-2.5 px-3 text-center">Số lượng</th>
                            <th class="py-2.5 px-3 text-right">Đơn giá</th>
                            <th class="py-2.5 px-3 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F0E6DA]">
                        @foreach($order->details as $index => $detail)
                            @php
                                $product = $detail->product;
                            @endphp
                            <tr class="hover:bg-amber-50/30 transition">
                                <td class="py-3 px-3 text-center text-[#7D6B5D]">{{ $index + 1 }}</td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-[#2B1810] text-xs sm:text-sm">{{ $product->name ?? 'Sản phẩm' }}</div>
                                    <div class="text-[11px] text-[#8C4A19] mt-0.5">Mã SP: #{{ $product->id ?? '---' }}</div>
                                </td>
                                <td class="py-3 px-3 text-center text-[#4A3B32] font-medium">{{ $product->size ?? 'Chuẩn' }}</td>
                                <td class="py-3 px-3 text-center font-bold text-[#2B1810]">{{ $detail->quantity }}</td>
                                <td class="py-3 px-3 text-right text-[#4A3B32] font-semibold">{{ number_format($detail->unit_price, 0, ',', '.') }} đ</td>
                                <td class="py-3 px-3 text-right font-extrabold text-[#2B1810]">{{ number_format($detail->total_price, 0, ',', '.') }} đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="py-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 border-b border-[#F0E6DA]">
            <div class="text-xs text-[#7D6B5D] space-y-1">
                <p><em>* Giá trên đã bao gồm thuế GTGT (nếu có).</em></p>
                <p><em>* Hóa đơn điện tử có giá trị tra cứu và bảo hành chính hãng.</em></p>
            </div>

            <div class="w-full sm:w-72 space-y-2 text-xs">
                @php
                    $subtotal = $order->details->sum('total_price');
                @endphp
                <div class="flex justify-between text-[#4A3B32]">
                    <span>Tạm tính tiền hàng:</span>
                    <span class="font-bold">{{ number_format($subtotal, 0, ',', '.') }} đ</span>
                </div>
                <div class="flex justify-between text-[#4A3B32]">
                    <span>Phí vận chuyển:</span>
                    <span class="font-bold">+{{ number_format($order->shipping_fee ?? 0, 0, ',', '.') }} đ</span>
                </div>
                @if(($order->voucher_discount ?? 0) > 0)
                    <div class="flex justify-between text-emerald-700 font-bold">
                        <span>Giảm giá Voucher:</span>
                        <span>-{{ number_format($order->voucher_discount, 0, ',', '.') }} đ</span>
                    </div>
                @endif
                <div class="flex justify-between items-baseline pt-2 border-t border-[#EBDDCD] text-sm">
                    <span class="font-extrabold text-[#2B1810]">TỔNG THANH TOÁN:</span>
                    <span class="font-black text-lg text-[#E08A1E]">{{ number_format($order->total_amount, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>

        <!-- Footer Notes & Signature -->
        <div class="pt-8 flex flex-col sm:flex-row justify-between items-center gap-6 text-center sm:text-left">
            <div class="space-y-1">
                <p class="font-extrabold text-sm text-[#2B1810]">🧸 Mật Ngọt Bear xin chân thành cảm ơn quý khách!</p>
                <p class="text-xs text-[#7D6B5D]">Chúc bạn và những người thân yêu luôn tràn ngập niềm vui và ngọt ngào!</p>
            </div>

            <div class="text-center sm:text-right">
                <div class="text-xs text-[#7D6B5D]">Xác nhận điện tử</div>
                <div class="font-extrabold text-xs text-[#8C4A19] mt-1">ĐẠI DIỆN CỬA HÀNG</div>
                <div class="mt-2 text-sm font-black text-[#2B1810] tracking-wider font-mono">
                    MẬT NGỌT BEAR
                </div>
            </div>
        </div>

    </div>

</body>
</html>
