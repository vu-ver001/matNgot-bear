<!-- Lịch sử giao dịch thanh toán -->
        <div class="panel-card mb-0 order-payments">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="fa-solid fa-credit-card"></i>
                    Lịch Sử Giao Dịch Thanh Toán
                </div>
            </div>

            <div class="table-container order-payments-container">
                <table class="data-table order-payments-table">
                    <thead>
                        <tr>
                            <th>Giao dịch</th>
                            <th class="text-right">Số Tiền</th>
                            <th>Trạng Thái</th>
                            <th class="text-right">Xử Lý</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->payments as $payment)
                            <tr>
                                <td data-label="Giao dịch">
                                    <div class="font-bold text-[#4E342E]">{{ $payment->method }}</div>
                                    <div class="text-xs text-[#795548] font-mono mt-1">{{ $payment->transaction_ref ?? '—' }}</div>
                                </td>
                                <td data-label="Số tiền" class="text-right font-extrabold text-amber-700">{{ number_format($payment->amount, 0, ',', '.') }} đ</td>
                                <td data-label="Trạng thái">
                                    <x-payment-status-badge :status="$payment->status" />
                                    <div class="text-xs text-[#8E8076] mt-1">{{ $payment->paid_at?->format('d/m/Y H:i') ?? $payment->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td data-label="Xử lý" class="text-right">
                                    @if ($payment->status === 'PENDING')
                                        <div class="flex flex-wrap justify-end gap-1.5">
                                            <form method="POST" action="{{ route('staff.payments.updateStatus', $payment) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="PAID">
                                                <button class="btn btn-success btn-sm">Xác nhận</button>
                                            </form>
                                            <form method="POST" action="{{ route('staff.payments.updateStatus', $payment) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="FAILED">
                                                <button class="btn btn-danger btn-sm">Hủy</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-[#8E8076]">Đã xử lý</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-sm text-[#8E8076]">Chưa có bản ghi giao dịch nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
