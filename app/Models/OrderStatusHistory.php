<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'changed_by',
        'note',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Chuyển mã trạng thái tiếng Anh sang tiếng Việt thân thiện
     */
    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'PENDING' => 'Chờ xác nhận',
            'CONFIRMED' => 'Đã xác nhận',
            'PREPARING' => 'Đang chuẩn bị hàng',
            'SHIPPING' => 'Đang giao hàng',
            'COMPLETED' => 'Đã giao thành công',
            'CANCELLED' => 'Đã hủy đơn',
            'RETURNED' => 'Đã trả hàng / hoàn tiền',
            default => $status ?? 'Không xác định',
        };
    }

    public function getFromStatusLabelAttribute(): ?string
    {
        return $this->from_status ? self::statusLabel($this->from_status) : null;
    }

    public function getToStatusLabelAttribute(): string
    {
        return self::statusLabel($this->to_status);
    }

    /**
     * Tiêu đề hiển thị thân thiện trên dòng thời gian đơn hàng (Timeline)
     */
    public function getDisplayTitleAttribute(): string
    {
        $note = (string) $this->note;

        // Xử lý các sự kiện hủy đơn
        if (str_contains($note, 'yêu cầu hủy') || str_contains($note, 'yêu cầu huỷ')) {
            if (str_contains($note, 'Từ chối') || str_contains($note, 'từ chối')) {
                return 'Shop đã từ chối yêu cầu hủy đơn';
            }
            if (str_contains($note, 'rút lại') || str_contains($note, 'Rút lại')) {
                return 'Rút lại yêu cầu hủy đơn';
            }
            if (str_contains($note, 'duyệt') || str_contains($note, 'Duyệt') || str_contains($note, 'chấp thuận')) {
                return 'Shop duyệt hủy đơn hàng';
            }
            return 'Khách gửi yêu cầu hủy đơn';
        }

        // Xử lý sự kiện trả hàng / hoàn tiền
        if (str_contains($note, 'yêu cầu hoàn') || str_contains($note, 'yêu cầu đổi') || str_contains($note, 'trả hàng') || str_contains($note, 'hoàn tiền')) {
            if (str_contains($note, 'Từ chối') || str_contains($note, 'từ chối')) {
                return 'Từ chối yêu cầu trả hàng';
            }
            if (str_contains($note, 'duyệt') || str_contains($note, 'chấp thuận')) {
                return 'Duyệt yêu cầu trả hàng / hoàn tiền';
            }
            if (str_contains($note, 'yêu cầu')) {
                return 'Khách gửi yêu cầu trả hàng';
            }
        }

        // Xử lý xác nhận thanh toán thủ công
        if (str_contains($note, 'Xác nhận thanh toán') || str_contains($note, 'xác nhận thanh toán')) {
            return 'Xác nhận thanh toán (+Bill)';
        }

        // Xử lý hệ thống tự động hoàn thành đơn sau 7 ngày
        if (str_contains($note, 'tự động') && (str_contains($note, 'hoàn thành') || str_contains($note, 'nhận hàng'))) {
            return 'Tự động hoàn thành đơn (sau 7 ngày)';
        }

        // Khi đơn hàng mới được tạo (không có trạng thái trước đó)
        if (empty($this->from_status)) {
            return 'Đơn hàng được tạo';
        }

        // Khi trạng thái trước và sau giống nhau (sự kiện/ghi chú nội bộ)
        if ($this->from_status === $this->to_status) {
            return 'Cập nhật đơn hàng (' . self::statusLabel($this->to_status) . ')';
        }

        // Chuyển trạng thái: Trả về chuỗi tiếng Việt rõ ràng, ví dụ "Chờ xác nhận → Đã xác nhận"
        return self::statusLabel($this->from_status) . ' → ' . self::statusLabel($this->to_status);
    }

    /**
     * Biểu tượng FontAwesome tương ứng cho từng sự kiện
     */
    public function getDisplayIconAttribute(): string
    {
        $note = (string) $this->note;

        if (str_contains($note, 'Từ chối') || str_contains($note, 'từ chối')) {
            return 'fa-ban text-[9px]';
        }
        if (str_contains($note, 'yêu cầu hủy') || str_contains($note, 'yêu cầu huỷ')) {
            return 'fa-hourglass-half text-[9px]';
        }
        if (str_contains($note, 'rút lại') || str_contains($note, 'Rút lại')) {
            return 'fa-rotate-left text-[9px]';
        }

        return match ($this->to_status) {
            'CANCELLED' => 'fa-xmark text-[9px]',
            'COMPLETED' => 'fa-circle-check text-[9px]',
            'SHIPPING' => 'fa-truck-fast text-[9px]',
            'PREPARING' => 'fa-box-open text-[9px]',
            'CONFIRMED' => 'fa-clipboard-check text-[9px]',
            'RETURNED' => 'fa-arrow-rotate-left text-[9px]',
            default => 'fa-check text-[9px]',
        };
    }
}
