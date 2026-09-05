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
                <form method="POST" action="{{ route('staff.orders.updateStatus', $order) }}" x-data="{ status: '' }">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-bold text-[#795548] uppercase mb-1.5">Chuyển sang trạng thái:</label>
                        <select name="order_status" x-model="status" class="select-control" required>
                            <option value="" disabled>Chọn trạng thái mới</option>
                            @foreach (['PENDING' => 'Chờ xác nhận', 'CONFIRMED' => 'Đã xác nhận', 'PREPARING' => 'Đang đóng gói', 'SHIPPING' => 'Chờ giao hàng', 'COMPLETED' => 'Đã giao thành công', 'RETURNED' => 'Trả hàng / Hoàn tiền', 'CANCELLED' => 'Hủy đơn hàng'] as $value => $label)
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
                    </div>

                    <button type="submit" :disabled="!status" class="mt-4 w-full btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi
                    </button>
                </form>
            @endif
        </div>
