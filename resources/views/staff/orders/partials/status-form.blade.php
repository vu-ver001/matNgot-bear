<!-- Cập nhật trạng thái -->
        <div class="panel-card mb-0">
            <div class="panel-header">
                <div class="panel-title">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    Cập Nhật Trạng Thái
                </div>
            </div>

            @if (in_array($order->order_status, ['CANCELLED', 'RETURNED']))
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-xs text-[#795548]">
                    <i class="fa-solid fa-lock text-gray-500 mr-1"></i>
                    Đơn hàng đã kết thúc ở trạng thái <strong>{{ $order->order_status }}</strong>, không thể cập nhật thêm.
                </div>
            @else
                @if ($order->payment_status === 'FAILED')
                    <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-xmark text-rose-600 text-sm mt-0.5 shrink-0"></i>
                        <div>
                            <strong class="font-bold">Đơn hàng thanh toán thất bại:</strong>
                            <p class="text-stone-600 mt-0.5">Giao dịch thanh toán trực tuyến của đơn hàng này không thành công. Nhân viên/admin không được phép xác nhận đơn hàng này.</p>
                        </div>
                    </div>
                @elseif ($order->payment_method !== 'COD' && $order->payment_status !== 'PAID')
                    <div class="mb-4 p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900 flex items-start gap-2.5">
                        <i class="fa-solid fa-clock text-amber-600 text-sm mt-0.5 shrink-0"></i>
                        <div>
                            <strong class="font-bold">Đơn hàng chưa thanh toán:</strong>
                            <p class="text-stone-600 mt-0.5">Khách hàng chọn thanh toán trực tuyến nhưng chưa hoàn tất thanh toán. Chỉ có thể xác nhận đơn sau khi thanh toán thành công.</p>
                        </div>
                    </div>
                @endif
                <form method="POST" action="{{ route('staff.orders.updateStatus', $order) }}" x-data="{ status: '' }">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-bold text-[#795548] uppercase mb-1.5">Chuyển sang trạng thái:</label>
                        <select name="order_status" x-model="status" class="select-control" required>
                            <option value="" disabled>Chọn trạng thái mới</option>
                            @foreach (['PENDING' => 'Chờ xác nhận', 'PREPARING' => 'Đang chuẩn bị', 'SHIPPING' => 'Đang giao hàng', 'COMPLETED' => 'Đã giao', 'RETURNED' => 'Trả hàng / Hoàn tiền', 'CANCELLED' => 'Hủy đơn hàng'] as $value => $label)
                                @if (in_array($value, $order->allowedNextStatuses(), true))
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div x-show="status === 'CANCELLED'" x-cloak class="mt-3">
                        <label class="block text-xs font-bold text-rose-700 uppercase mb-1.5">Lý do hủy đơn <span class="text-rose-600">*</span></label>
                        <textarea name="cancel_reason" rows="3" maxlength="255" :required="status === 'CANCELLED'" :disabled="status !== 'CANCELLED'" placeholder="Nhập lý do hủy đơn chi tiết..."
                                  class="input-control"></textarea>

                        @if ($order->order_status === 'SHIPPING')
                            <label class="mt-3 flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-semibold text-rose-800">
                                <input type="checkbox" name="stock_returned" value="1"
                                       :required="status === 'CANCELLED'" :disabled="status !== 'CANCELLED'"
                                       class="mt-0.5 rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                                <span>Tôi xác nhận kiện hàng đã quay lại kho và có thể hoàn lại tồn kho.</span>
                            </label>
                        @endif
                    </div>

                    <button type="submit" :disabled="!status" class="mt-4 w-full btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi
                    </button>
                </form>
            @endif
        </div>
