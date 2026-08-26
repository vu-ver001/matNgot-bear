<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    // Hanoi Inner City Districts (Eligible for Express Hỏa Tốc)
    protected array $hanoiInnerDistricts = [
        'Ba Đình', 'Hoàn Kiếm', 'Cầu Giấy', 'Đống Đa', 'Hai Bà Trưng',
        'Thanh Xuân', 'Nam Từ Liêm', 'Bắc Từ Liêm', 'Tây Hồ',
        'Hoàng Mai', 'Hà Đông', 'Long Biên'
    ];

    // Hanoi Outer City Districts
    protected array $hanoiOuterDistricts = [
        'Đông Anh', 'Gia Lâm', 'Sóc Sơn', 'Mê Linh', 'Hoài Đức',
        'Đan Phượng', 'Quốc Oai', 'Thạch Thất', 'Chương Mỹ', 'Thanh Trì',
        'Thường Tín', 'Thanh Oai', 'Phú Xuyên', 'Ứng Hòa', 'Mỹ Đức',
        'Ba Vì', 'Phúc Thọ', 'Sơn Tây'
    ];

    // Approximate road distance (km) and region classification from Hanoi (Origin) to 63 provinces
    protected array $provinceDistances = [
        // Miền Bắc
        'Hà Nội' => ['distance' => 10, 'region' => 'NORTH_INNER', 'days_standard' => '1 - 2 ngày', 'days_fast' => 'Trong ngày'],
        'Bắc Ninh' => ['distance' => 35, 'region' => 'NORTH', 'days_standard' => '1 - 2 ngày', 'days_fast' => '1 ngày'],
        'Hưng Yên' => ['distance' => 45, 'region' => 'NORTH', 'days_standard' => '1 - 2 ngày', 'days_fast' => '1 ngày'],
        'Hà Nam' => ['distance' => 60, 'region' => 'NORTH', 'days_standard' => '1 - 2 ngày', 'days_fast' => '1 ngày'],
        'Vĩnh Phúc' => ['distance' => 55, 'region' => 'NORTH', 'days_standard' => '1 - 2 ngày', 'days_fast' => '1 ngày'],
        'Hải Dương' => ['distance' => 65, 'region' => 'NORTH', 'days_standard' => '1 - 2 ngày', 'days_fast' => '1 ngày'],
        'Thái Nguyên' => ['distance' => 75, 'region' => 'NORTH', 'days_standard' => '1 - 2 ngày', 'days_fast' => '1 ngày'],
        'Bắc Giang' => ['distance' => 70, 'region' => 'NORTH', 'days_standard' => '1 - 2 ngày', 'days_fast' => '1 ngày'],
        'Hòa Bình' => ['distance' => 75, 'region' => 'NORTH', 'days_standard' => '1 - 2 ngày', 'days_fast' => '1 ngày'],
        'Phú Thọ' => ['distance' => 85, 'region' => 'NORTH', 'days_standard' => '1 - 2 ngày', 'days_fast' => '1 ngày'],
        'Nam Định' => ['distance' => 90, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Thái Bình' => ['distance' => 100, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Ninh Bình' => ['distance' => 95, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Hải Phòng' => ['distance' => 120, 'region' => 'NORTH', 'days_standard' => '1 - 2 ngày', 'days_fast' => '1 ngày'],
        'Quảng Ninh' => ['distance' => 150, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Lạng Sơn' => ['distance' => 155, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Tuyên Quang' => ['distance' => 130, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Yên Bái' => ['distance' => 160, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Bắc Kạn' => ['distance' => 165, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Cao Bằng' => ['distance' => 280, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Hà Giang' => ['distance' => 300, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Lào Cai' => ['distance' => 290, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Sơn La' => ['distance' => 300, 'region' => 'NORTH', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Điện Biên' => ['distance' => 450, 'region' => 'NORTH', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Lai Châu' => ['distance' => 400, 'region' => 'NORTH', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],

        // Miền Trung & Tây Nguyên
        'Thanh Hóa' => ['distance' => 160, 'region' => 'CENTRAL', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Nghệ An' => ['distance' => 300, 'region' => 'CENTRAL', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Hà Tĩnh' => ['distance' => 350, 'region' => 'CENTRAL', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Quảng Bình' => ['distance' => 500, 'region' => 'CENTRAL', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Quảng Trị' => ['distance' => 600, 'region' => 'CENTRAL', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Thừa Thiên Huế' => ['distance' => 660, 'region' => 'CENTRAL', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Đà Nẵng' => ['distance' => 760, 'region' => 'CENTRAL', 'days_standard' => '2 - 3 ngày', 'days_fast' => '1 - 2 ngày'],
        'Quảng Nam' => ['distance' => 820, 'region' => 'CENTRAL', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Quảng Ngãi' => ['distance' => 880, 'region' => 'CENTRAL', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Bình Định' => ['distance' => 1050, 'region' => 'CENTRAL', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Phú Yên' => ['distance' => 1150, 'region' => 'CENTRAL', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Khánh Hòa' => ['distance' => 1280, 'region' => 'CENTRAL', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Ninh Thuận' => ['distance' => 1380, 'region' => 'CENTRAL', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Bình Thuận' => ['distance' => 1500, 'region' => 'CENTRAL', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Kon Tum' => ['distance' => 1050, 'region' => 'HIGHLANDS', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Gia Lai' => ['distance' => 1100, 'region' => 'HIGHLANDS', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Đắk Lắk' => ['distance' => 1250, 'region' => 'HIGHLANDS', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Đắk Nông' => ['distance' => 1350, 'region' => 'HIGHLANDS', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Lâm Đồng' => ['distance' => 1450, 'region' => 'HIGHLANDS', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],

        // Miền Nam
        'TP. Hồ Chí Minh' => ['distance' => 1700, 'region' => 'SOUTH', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Bình Dương' => ['distance' => 1670, 'region' => 'SOUTH', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Đồng Nai' => ['distance' => 1680, 'region' => 'SOUTH', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Bà Rịa - Vũng Tàu' => ['distance' => 1750, 'region' => 'SOUTH', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Tây Ninh' => ['distance' => 1720, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Bình Phước' => ['distance' => 1630, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Long An' => ['distance' => 1740, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Tiền Giang' => ['distance' => 1770, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Bến Tre' => ['distance' => 1800, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Trà Vinh' => ['distance' => 1850, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Vĩnh Long' => ['distance' => 1820, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Đồng Tháp' => ['distance' => 1830, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'An Giang' => ['distance' => 1880, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Kiên Giang' => ['distance' => 1950, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Cần Thơ' => ['distance' => 1860, 'region' => 'SOUTH', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'],
        'Hậu Giang' => ['distance' => 1900, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Sóc Trăng' => ['distance' => 1930, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Bạc Liêu' => ['distance' => 1980, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
        'Cà Mau' => ['distance' => 2050, 'region' => 'SOUTH', 'days_standard' => '3 - 5 ngày', 'days_fast' => '2 - 3 ngày'],
    ];

    /**
     * Check if a given district or ward in Hanoi is in the inner city.
     */
    public function isHanoiInnerCity(?string $province, ?string $district = '', ?string $ward = ''): bool
    {
        if (empty($province)) return false;
        
        $cleanProvince = $this->cleanString($province);
        if ($cleanProvince !== 'ha noi' && $cleanProvince !== 'tp. ha noi' && $cleanProvince !== 'thanh pho ha noi') {
            return false;
        }

        // If outer district or outer ward is explicitly indicated
        $combined = $this->cleanString(($district ?? '') . ' ' . ($ward ?? ''));
        foreach ($this->hanoiOuterDistricts as $outer) {
            if (str_contains($combined, $this->cleanString($outer))) {
                return false;
            }
        }

        // If inner district is found
        foreach ($this->hanoiInnerDistricts as $inner) {
            if (str_contains($combined, $this->cleanString($inner))) {
                return true;
            }
        }

        // Communes (Xã) outside inner urban area
        if (!empty($ward) && (str_starts_with($this->cleanString($ward), 'xa ') || str_starts_with($this->cleanString($ward), 'xã '))) {
            return false;
        }

        // Urban wards (Phường) in Hanoi default to inner city unless outer
        return true;
    }

    /**
     * Calculate Distance & Shipping Options based on Google Maps API or Regional Distance Matrix
     */
    public function calculateShippingOptions(
        ?string $province,
        ?string $district = '',
        ?string $ward = '',
        ?string $street = '',
        float $subtotal = 0
    ): array {
        $originAddress = config('services.google_maps.origin_address', 'Số 41A, Phú Diễn, Bắc Từ Liêm, Hà Nội');
        $apiKey = config('services.google_maps.api_key');

        $fullDestination = trim(implode(', ', array_filter([$street, $ward, $district, $province])));
        if (empty($fullDestination)) {
            $fullDestination = 'Hà Nội';
            $province = 'Hà Nội';
        }

        $distanceKm = null;
        $durationText = null;
        $usedGoogleMaps = false;

        // 1. Try Google Maps Distance Matrix API if key is set
        if (!empty($apiKey)) {
            try {
                $response = Http::timeout(4)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                    'origins' => $originAddress,
                    'destinations' => $fullDestination,
                    'units' => 'metric',
                    'language' => 'vi',
                    'key' => $apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'OK' && !empty($data['rows'][0]['elements'][0]['status']) && $data['rows'][0]['elements'][0]['status'] === 'OK') {
                        $element = $data['rows'][0]['elements'][0];
                        $distanceKm = round(($element['distance']['value'] ?? 0) / 1000, 1);
                        $durationText = $element['duration']['text'] ?? null;
                        $usedGoogleMaps = true;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Google Maps Distance Matrix Error: ' . $e->getMessage());
            }
        }

        // 2. Fallback to Regional Matrix Distance Calculation
        if ($distanceKm === null) {
            $distanceKm = $this->estimateDistance($province, $district, $ward, $street);
            $durationText = $this->estimateDurationText($distanceKm);
        }

        $isHanoiInner = $this->isHanoiInnerCity($province, $district, $ward);
        $provinceInfo = $this->getProvinceInfo($province);

        // 3. Compute Fees for the 3 Methods
        // STANDARD (Giao hàng tiêu chuẩn)
        if ($isHanoiInner) {
            $standardFee = 22000;
            $standardDays = '1 - 2 ngày';
        } elseif (($provinceInfo['region'] ?? '') === 'NORTH' || str_contains($this->cleanString($province ?? ''), 'ha noi')) {
            $standardFee = 28000;
            $standardDays = '1 - 2 ngày';
        } elseif (($provinceInfo['region'] ?? '') === 'CENTRAL' || ($provinceInfo['region'] ?? '') === 'HIGHLANDS') {
            $standardFee = 32000;
            $standardDays = '2 - 3 ngày';
        } else {
            // SOUTH
            $standardFee = 38000;
            $standardDays = '3 - 4 ngày';
        }

        // FAST (Giao hàng nhanh)
        if ($isHanoiInner) {
            $fastFee = 32000;
            $fastDays = 'Trong 24h';
        } elseif (($provinceInfo['region'] ?? '') === 'NORTH' || str_contains($this->cleanString($province ?? ''), 'ha noi')) {
            $fastFee = 38000;
            $fastDays = '1 ngày';
        } else {
            $fastFee = 48000;
            $fastDays = $provinceInfo['days_fast'] ?? '2 ngày';
        }

        // EXPRESS (Giao hàng hoả tốc) - STRICTLY Hanoi inner city
        $expressFee = 55000;
        $expressDays = '2 - 4 giờ';
        $expressAvailable = $isHanoiInner;
        $expressDisabledReason = $isHanoiInner ? '' : 'Chỉ áp dụng cho đơn giao tại khu vực nội thành Hà Nội.';

        return [
            'success' => true,
            'source' => $usedGoogleMaps ? 'GOOGLE_MAPS_API' : 'REGIONAL_MATRIX',
            'origin' => $originAddress,
            'destination' => $fullDestination,
            'distance_km' => $distanceKm,
            'duration_text' => $durationText,
            'is_hanoi_inner' => $isHanoiInner,
            'options' => [
                'standard' => [
                    'id' => 'standard',
                    'name' => 'Giao hàng tiêu chuẩn',
                    'desc' => 'Giao qua đối tác vận chuyển',
                    'fee' => $standardFee,
                    'time' => $standardDays,
                    'available' => true,
                ],
                'fast' => [
                    'id' => 'fast',
                    'name' => 'Giao hàng nhanh',
                    'desc' => 'Ưu tiên xử lý trong ngày',
                    'fee' => $fastFee,
                    'time' => $fastDays,
                    'available' => true,
                ],
                'express' => [
                    'id' => 'express',
                    'name' => 'Giao hàng hoả tốc',
                    'desc' => $isHanoiInner ? 'Giao trong 2 - 4 giờ tại Hà Nội' : 'Chỉ hỗ trợ nội thành Hà Nội',
                    'fee' => $expressFee,
                    'time' => $expressDays,
                    'available' => $expressAvailable,
                    'disabled_reason' => $expressDisabledReason,
                ]
            ]
        ];
    }

    /**
     * Estimate Distance (km) using local database of 63 provinces and districts
     */
    protected function estimateDistance(?string $province, ?string $district = '', ?string $ward = ''): float
    {
        if (empty($province)) return 10.0;

        $cleanProv = $this->cleanString($province);
        $combined = $this->cleanString(($street ?? '') . ' ' . ($ward ?? '') . ' ' . ($district ?? ''));

        if (str_contains($cleanProv, 'ha noi')) {
            // Distance from warehouse at Số 41A Phú Diễn, Bắc Từ Liêm, Hà Nội:
            
            // 1. Siêu gần (Phú Diễn, Phúc Diễn, Cầu Diễn, Kiều Mai, ga Phú Diễn): 0.5 - 2.0 km
            if (str_contains($combined, 'phu dien') || str_contains($combined, 'phuc dien') || str_contains($combined, 'cau dien') || str_contains($combined, 'kieu mai') || str_contains($combined, 'duc dien')) {
                return 1.5;
            }

            // 2. Khu vực Cầu Giấy / Mai Dịch / ĐH Sư Phạm / ĐH Quốc Gia / ĐH Thương Mại: 2.5 - 3.5 km
            if (str_contains($combined, 'su pham') || str_contains($combined, 'thuong mai') || str_contains($combined, 'quoc gia') || str_contains($combined, 'ngoai ngu') || str_contains($combined, 'bao chi') || str_contains($combined, 'mai dich') || str_contains($combined, 'ho tung mau') || str_contains($combined, 'xuan thuy') || str_contains($combined, 'cau giay') || str_contains($combined, 'tran thai tong') || str_contains($combined, 'duy tan') || str_contains($combined, 'ton that thuyet') || str_contains($combined, 'dich vong') || str_contains($combined, 'pham van dong')) {
                return 3.2;
            }

            // 3. Cổ Nhuế, Xuân Đỉnh, Đông Ngạc, Nghĩa Đô, Nghĩa Tân: 3.5 - 4.5 km
            if (str_contains($combined, 'co nhue') || str_contains($combined, 'xuan dinh') || str_contains($combined, 'dong ngac') || str_contains($combined, 'nghia do') || str_contains($combined, 'nghia tan') || str_contains($combined, 'hoang quoc viet') || str_contains($combined, 'vo chi cong')) {
                return 4.0;
            }

            // 4. Mỹ Đình, Mễ Trì, Nam Từ Liêm, Sân vận động Mỹ Đình, Keangnam: 4.0 - 5.5 km
            if (str_contains($combined, 'my dinh') || str_contains($combined, 'me tri') || str_contains($combined, 'nam tu liem') || str_contains($combined, 'le duc tho') || str_contains($combined, 'pham hung') || str_contains($combined, 'keangnam') || str_contains($combined, 'phu do') || str_contains($combined, 'trung van') || str_contains($combined, 'tay mo') || str_contains($combined, 'dai mo')) {
                return 4.8;
            }

            // 5. Ba Đình, Giảng Võ, Liễu Giai, Kim Mã, Đội Cấn, Ngọc Hà: 6.0 - 7.5 km
            if (str_contains($combined, 'ba dinh') || str_contains($combined, 'giang vo') || str_contains($combined, 'lieu giai') || str_contains($combined, 'kim ma') || str_contains($combined, 'doi can') || str_contains($combined, 'ngoc ha') || str_contains($combined, 'thanh cong') || str_contains($combined, 'hoang hoa tham')) {
                return 6.5;
            }

            // 6. Đống Đa, Láng Hạ, Chùa Bộc, Thái Hà, Ô Chợ Dừa, Cát Linh, Xã Đàn: 6.8 - 8.0 km
            if (str_contains($combined, 'dong da') || str_contains($combined, 'lang ha') || str_contains($combined, 'chua boc') || str_contains($combined, 'thai ha') || str_contains($combined, 'o cho dua') || str_contains($combined, 'cat linh') || str_contains($combined, 'xa dan') || str_contains($combined, 'ton duc thang') || str_contains($combined, 'kim lien')) {
                return 7.2;
            }

            // 7. Tây Hồ, Thụy Khuê, Quảng An, Nhật Tân, Xuân La: 7.0 - 8.5 km
            if (str_contains($combined, 'tay ho') || str_contains($combined, 'thuy khue') || str_contains($combined, 'quang an') || str_contains($combined, 'nhat tan') || str_contains($combined, 'xuan la') || str_contains($combined, 'lac long quan')) {
                return 7.5;
            }

            // 8. Thanh Xuân, Hà Đông, Mộ Lao, Văn Quán: 7.5 - 9.5 km
            if (str_contains($combined, 'thanh xuan') || str_contains($combined, 'nguyen trai') || str_contains($combined, 'khuong dinh') || str_contains($combined, 'ha dong') || str_contains($combined, 'mo lao') || str_contains($combined, 'van quan') || str_contains($combined, 'le van luong') || str_contains($combined, 'to huu')) {
                return 8.2;
            }

            // 9. Hoàn Kiếm, Cửa Nam, Phố Cổ, Tràng Tiền, Hàng Bạc, Hàng Bài: 9.0 - 10.5 km
            if (str_contains($combined, 'hoan kiem') || str_contains($combined, 'cua nam') || str_contains($combined, 'trang tien') || str_contains($combined, 'hang bac') || str_contains($combined, 'pho co') || str_contains($combined, 'hang bai') || str_contains($combined, 'dinh tien hoang')) {
                return 9.8;
            }

            // 10. Hai Bà Trưng, Bách Khoa, Bạch Mai, Minh Khai, Times City: 10.0 - 12.0 km
            if (str_contains($combined, 'hai ba trung') || str_contains($combined, 'bach khoa') || str_contains($combined, 'bach mai') || str_contains($combined, 'minh khai') || str_contains($combined, 'times city') || str_contains($combined, 'vinh tuy')) {
                return 10.8;
            }

            // 11. Hoàng Mai, Linh Đàm, Định Công, Đại Kim, Hoàng Liệt: 11.0 - 13.5 km
            if (str_contains($combined, 'hoang mai') || str_contains($combined, 'linh dam') || str_contains($combined, 'dinh cong') || str_contains($combined, 'dai kim') || str_contains($combined, 'hoang liet') || str_contains($combined, 'giai phong')) {
                return 12.0;
            }

            // 12. Long Biên, Gia Thụy, Bồ Đề, Ngọc Lâm, Aeon Mall: 13.0 - 16.0 km
            if (str_contains($combined, 'long bien') || str_contains($combined, 'gia thuy') || str_contains($combined, 'bo de') || str_contains($combined, 'ngoc lam') || str_contains($combined, 'aeon')) {
                return 14.5;
            }

            // 13. Ngoại thành gần (Hoài Đức, Đan Phượng, Thiên Lộc, Đông Anh): 12.0 - 20.0 km
            if (str_contains($combined, 'hoai duc') || str_contains($combined, 'dan phuong') || str_contains($combined, 'thien loc') || str_contains($combined, 'dong anh') || str_contains($combined, 'me linh') || str_contains($combined, 'tien phong')) {
                return 16.5;
            }

            // 14. Inner Hanoi default
            if ($this->isHanoiInnerCity($province, $district, $ward)) {
                return 6.5;
            }
            return 28.0;
        }

        foreach ($this->provinceDistances as $name => $info) {
            if (str_contains($cleanProv, $this->cleanString($name))) {
                return (float) $info['distance'];
            }
        }

        return 500.0;
    }

    protected function getProvinceInfo(?string $province): array
    {
        if (empty($province)) return $this->provinceDistances['Hà Nội'];

        $cleanProv = $this->cleanString($province);
        foreach ($this->provinceDistances as $name => $info) {
            if (str_contains($cleanProv, $this->cleanString($name))) {
                return $info;
            }
        }

        return ['distance' => 500, 'region' => 'CENTRAL', 'days_standard' => '3 - 4 ngày', 'days_fast' => '2 ngày'];
    }

    protected function estimateDurationText(float $distanceKm): string
    {
        if ($distanceKm <= 4) {
            $mins = max(7, (int) round($distanceKm * 2.5));
            return "{$mins} phút";
        } elseif ($distanceKm <= 15) {
            $mins = max(15, (int) round($distanceKm * 2.2));
            return "{$mins} phút";
        } elseif ($distanceKm <= 150) {
            $hours = max(1, round($distanceKm / 45, 1));
            return "{$hours} giờ";
        } else {
            $hours = round($distanceKm / 60, 1);
            return "{$hours} giờ";
        }
    }

    protected function cleanString(?string $str): string
    {
        if (!$str) return '';
        $str = mb_strtolower(trim($str), 'UTF-8');
        
        $accents = [
            'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ẳ'=>'a','ẵ'=>'a','ặ'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
            'è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
            'ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
            'ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
            'ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u',
            'ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y',
            'đ'=>'d'
        ];
        return strtr($str, $accents);
    }
}
