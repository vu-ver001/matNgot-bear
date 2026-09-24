<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;

class GhnTrackingService
{
    /**
     * Generate or retrieve consistent GHN Tracking Code for order.
     */
    public static function getTrackingCode(Order $order): string
    {
        $hash = strtoupper(substr(md5('ghn_tracking_' . $order->id . '_' . $order->order_code), 0, 8));
        return 'GHN' . $hash . 'VN';
    }

    /**
     * Resolve Destination Hub and SOC warehouse name accurately based on recipient address.
     */
    public static function resolveDestinationLogistics(string $address): array
    {
        $addrClean = trim($address);
        $parts = array_filter(array_map('trim', explode(',', $addrClean)));
        $parts = array_values($parts);
        $totalParts = count($parts);

        // 1. Extract Province (last segment)
        $provinceRaw = $totalParts >= 1 ? $parts[$totalParts - 1] : 'Hà Nội';
        $provinceClean = preg_replace('/^(Tỉnh|Thành phố|TP\.?)\s+/iu', '', $provinceRaw);
        $provinceClean = trim($provinceClean);

        // 2. Extract District (second to last segment if available)
        $district = '';
        if ($totalParts >= 2) {
            $rawDistrict = $parts[$totalParts - 2];
            // If rawDistrict is actually a ward/commune ("Phường Ninh Xá", "Xã ...")
            if (preg_match('/^(Phường|Xã|Thị trấn|Ấp|Thôn|Khu phố)\s+/iu', $rawDistrict)) {
                $district = 'TP. ' . $provinceClean;
            } else {
                $district = $rawDistrict;
            }
        } else {
            $district = 'Trung tâm ' . $provinceClean;
        }

        $addrLower = mb_strtolower($addrClean, 'UTF-8');

        // 3. Determine Region for SOC Sorting Warehouse:
        // Miền Nam
        $southProvinces = ['hồ chí minh', 'tp.hcm', 'hcm', 'sài gòn', 'bình dương', 'đồng nai', 'long an', 'tiền giang', 'bến tre', 'vĩnh long', 'trà vinh', 'hậu giang', 'sóc trăng', 'bạc liêu', 'cà mau', 'kiên giang', 'an giang', 'đồng tháp', 'tây ninh', 'bình phước', 'bà rịa', 'vũng tàu'];
        $isSouth = false;
        foreach ($southProvinces as $sp) {
            if (str_contains($addrLower, $sp)) {
                $isSouth = true;
                break;
            }
        }

        // Miền Trung & Tây Nguyên
        $centralProvinces = ['đà nẵng', 'quảng nam', 'quảng ngãi', 'bình định', 'phú yên', 'khánh hòa', 'nha trang', 'ninh thuận', 'bình thuận', 'thừa thiên huế', 'huế', 'quảng trị', 'quảng bình', 'hà tĩnh', 'nghệ an', 'thanh hóa', 'kon tum', 'gia lai', 'đắk lắk', 'đắk nông', 'lâm đồng', 'đà lạt'];
        $isCentral = false;
        foreach ($centralProvinces as $cp) {
            if (str_contains($addrLower, $cp)) {
                $isCentral = true;
                break;
            }
        }

        if ($isSouth) {
            $socName = 'Kho Tổng Phân Loại GHN Tân Phú Trung SOC (TP. Hồ Chí Minh)';
        } elseif ($isCentral) {
            $socName = 'Kho Tổng Phân Loại GHN Đà Nẵng SOC (KCN Hòa Khánh)';
        } else {
            $socName = 'Kho Tổng Phân Loại GHN Hà Nội SOC (KCN Đài Tư, Long Biên)';
        }

        // 4. Resolve Hub name cleanly:
        if (str_contains($addrLower, 'hà nội') || str_contains($addrLower, 'ha noi')) {
            $cleanDist = preg_replace('/^(Quận|Huyện|Thị xã)\s+/iu', '', $district);
            $hubName = 'Bưu cục Giao nhận GHN ' . ($cleanDist ?: 'Cầu Giấy') . ' (Hà Nội)';
            $finalProvince = 'Hà Nội';
        } elseif (str_contains($addrLower, 'hồ chí minh') || str_contains($addrLower, 'sài gòn')) {
            $cleanDist = preg_replace('/^(Quận|Huyện|Thành phố|TP\.?)\s+/iu', '', $district);
            $hubName = 'Bưu cục Giao nhận GHN ' . ($cleanDist ?: 'Quận 1') . ' (TP. HCM)';
            $finalProvince = 'TP. Hồ Chí Minh';
        } else {
            $hubName = 'Bưu cục Giao nhận GHN ' . $provinceClean;
            $finalProvince = $provinceClean;
        }

        return [
            'soc_name' => $socName,
            'hub_name' => $hubName,
            'province' => $finalProvince,
            'district' => $district,
        ];
    }

    /**
     * Generate structured tracking information for the order.
     */
    public static function getTrackingInfo(Order $order): array
    {
        $trackingCode = self::getTrackingCode($order);
        $logistics = self::resolveDestinationLogistics($order->recipient_address ?? '');

        $createdAt = $order->created_at ?? now();
        $confirmedAt = $order->confirmed_at ?? $createdAt->copy()->addMinutes(20);
        $shippedAt = $order->shipped_at ?? $createdAt->copy()->addHours(2);
        $completedAt = $order->completed_at ?? $shippedAt->copy()->addDays(2);

        $events = [];

        // 1. Tiếp nhận đơn hàng
        $events[] = [
            'step' => 1,
            'title' => 'Shop Mật Ngọt Bear đã tiếp nhận đơn hàng',
            'time' => $createdAt->format('H:i - d/m/Y'),
            'location' => 'Kho Shop Mật Ngọt Bear - Số 41A, Phú Diễn, Bắc Từ Liêm, Hà Nội',
            'description' => "Đơn hàng #{$order->order_code} đã được ghi nhận trên hệ thống và chuyển đến bộ phận chuẩn bị kiện hàng.",
            'status' => 'completed',
            'icon' => 'fa-clipboard-check',
        ];

        // 2. Đã xác nhận & Đóng gói
        if (in_array($order->order_status, ['CONFIRMED', 'PREPARING', 'SHIPPING', 'COMPLETED'])) {
            $isPreparing = $order->order_status === 'PREPARING';
            $isPastPreparing = in_array($order->order_status, ['SHIPPING', 'COMPLETED']);
            $events[] = [
                'step' => 2,
                'title' => 'Đã đóng gói kiện gấu bông & Tạo vận đơn GHN',
                'time' => $confirmedAt->format('H:i - d/m/Y'),
                'location' => 'Kho Shop Mật Ngọt Bear - Số 41A, Phú Diễn, Bắc Từ Liêm, Hà Nội',
                'description' => "Kiện hàng đã được đóng gói bọc 3 lớp chống ẩm, dán mã vận đơn {$trackingCode}. Bưu tá GHN hẹn lấy hàng trong ca.",
                'status' => $isPastPreparing ? 'completed' : ($isPreparing ? 'current' : 'completed'),
                'icon' => 'fa-box-open',
            ];
        }

        // 3. Bưu tá GHN đã lấy hàng & nhập kho phân loại tổng
        if (in_array($order->order_status, ['SHIPPING', 'COMPLETED'])) {
            $events[] = [
                'step' => 3,
                'title' => 'Bưu tá GHN đã lấy hàng - Nhập Kho Tổng Phân Loại',
                'time' => $shippedAt->format('H:i - d/m/Y'),
                'location' => $logistics['soc_name'],
                'description' => "Bưu tá Trần Văn Nam (0983.551.205) đã tiếp nhận kiện hàng từ Mật Ngọt Bear. Kiện hàng đã qua máy quét barcode tự động tại {$logistics['soc_name']}.",
                'status' => 'completed',
                'icon' => 'fa-warehouse',
            ];

            // 4. Luân chuyển tới bưu cục giao
            $events[] = [
                'step' => 4,
                'title' => 'Đang luân chuyển trên xe tải chuyên dụng GHN Express',
                'time' => $shippedAt->copy()->addHours(6)->format('H:i - d/m/Y'),
                'location' => 'Tuyến xe tải GHN Express ➔ ' . $logistics['hub_name'],
                'description' => "Kiện hàng đã xuất kho trung chuyển, đang trên đường vận chuyển tới {$logistics['hub_name']}.",
                'status' => 'completed',
                'icon' => 'fa-truck-fast',
            ];

            // 5. Shipper đi giao
            $isDelivering = $order->order_status === 'SHIPPING';
            $deliveryTime = $order->order_status === 'COMPLETED' 
                ? $completedAt->copy()->subHours(2)->format('H:i - d/m/Y')
                : now()->subHours(1)->format('H:i - d/m/Y');

            $events[] = [
                'step' => 5,
                'title' => 'Bưu tá GHN đang phát kiện hàng đến bạn',
                'time' => $deliveryTime,
                'location' => $logistics['hub_name'] . ' ➔ ' . $order->recipient_address,
                'description' => "Bưu tá Lê Hoàng Minh (SĐT: 0971.882.304) đang trên đường giao kiện hàng đến địa chỉ người nhận. Quý khách vui lòng để ý điện thoại.",
                'status' => $order->order_status === 'COMPLETED' ? 'completed' : 'current',
                'icon' => 'fa-motorcycle',
            ];
        }

        // 6. Giao thành công
        if ($order->order_status === 'COMPLETED') {
            $events[] = [
                'step' => 6,
                'title' => 'Giao hàng thành công',
                'time' => $completedAt->format('H:i - d/m/Y'),
                'location' => $order->recipient_address,
                'description' => "Kiện hàng gấu bông đã được giao thành công đến tay người nhận {$order->recipient_name}. Cảm ơn bạn đã lựa chọn Mật Ngọt Bear!",
                'status' => 'completed',
                'icon' => 'fa-circle-check',
            ];
        }

        // Current status label and location summary
        $currentLocation = match ($order->order_status) {
            'PENDING' => 'Chờ duyệt tại Shop Mật Ngọt Bear (Hà Nội)',
            'CONFIRMED' => 'Kho Shop Mật Ngọt Bear (Đang đóng gói)',
            'PREPARING' => 'Kho Shop Mật Ngọt Bear (Chờ bưu tá GHN qua lấy)',
            'SHIPPING' => 'Đang phát hàng (' . $logistics['hub_name'] . ')',
            'COMPLETED' => 'Đã giao thành công tại ' . ($order->recipient_address ?? 'địa chỉ khách hàng'),
            'CANCELLED' => 'Đơn hàng đã hủy',
            'RETURNED' => 'Đang hoàn về kho Shop',
            default => 'Hệ thống Mật Ngọt Bear',
        };

        $shipperInfo = [
            'name' => 'Lê Hoàng Minh',
            'phone' => '0971.882.304',
            'service' => 'Giao Hàng Nhanh - GHN Express Chuẩn',
            'license_plate' => '29B1-884.92',
        ];

        return [
            'tracking_code' => $trackingCode,
            'carrier_name' => 'Giao Hàng Nhanh (GHN Express)',
            'carrier_phone' => '1900 636677',
            'current_location' => $currentLocation,
            'logistics' => $logistics,
            'events' => array_reverse($events), // Mới nhất lên đầu
            'shipper' => $shipperInfo,
        ];
    }
}
