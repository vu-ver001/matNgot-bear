<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
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

    // Regional classification for offline fallback when GHN API is unavailable
    protected array $northernProvinces = [
        'Hà Nội', 'Bắc Ninh', 'Hưng Yên', 'Hà Nam', 'Vĩnh Phúc', 'Hải Dương',
        'Thái Nguyên', 'Bắc Giang', 'Hòa Bình', 'Phú Thọ', 'Nam Định', 'Thái Bình',
        'Ninh Bình', 'Hải Phòng', 'Quảng Ninh', 'Lạng Sơn', 'Tuyên Quang', 'Yên Bái',
        'Bắc Kạn', 'Cao Bằng', 'Hà Giang', 'Lào Cai', 'Sơn La', 'Điện Biên', 'Lai Châu'
    ];

    protected array $centralProvinces = [
        'Thanh Hóa', 'Nghệ An', 'Hà Tĩnh', 'Quảng Bình', 'Quảng Trị', 'Thừa Thiên Huế',
        'Đà Nẵng', 'Quảng Nam', 'Quảng Ngãi', 'Bình Định', 'Phú Yên', 'Khánh Hòa',
        'Ninh Thuận', 'Bình Thuận', 'Kon Tum', 'Gia Lai', 'Đắk Lắk', 'Đắk Nông', 'Lâm Đồng'
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
     * Calculate Distance & Shipping Options based on Giao Hàng Nhanh (GHN) API or Regional Distance Matrix
     */
    public function calculateShippingOptions(
        ?string $province,
        ?string $district = '',
        ?string $ward = '',
        ?string $street = '',
        float $subtotal = 0
    ): array {
        $originAddress = 'Trường ĐH Tài Nguyên Và Môi Trường Hà Nội, 41A Phú Diễn, Bắc Từ Liêm, Hà Nội';

        $fullDestination = trim(implode(', ', array_filter([$street, $ward, $district, $province])));
        if (empty($fullDestination)) {
            $fullDestination = 'Hà Nội';
            $province = 'Hà Nội';
        }

        $destinationDisplay = trim(implode(', ', array_filter([$ward, $district, $province])));
        if (empty($destinationDisplay)) {
            $destinationDisplay = 'Hà Nội';
        }
        $routeText = 'Kho Mật Ngọt Bear (Hà Nội) ➔ ' . $destinationDisplay;

        $isHanoiInner = $this->isHanoiInnerCity($province, $district, $ward);
        $provinceInfo = $this->getProvinceInfo($province);

        $usedGhn = false;
        $ghnFee = null;
        $ghnLeadtime = null;

        $ghnToken = config('services.ghn.api_token') ?: config('services.ghn.token');
        $ghnShopId = (int) config('services.ghn.shop_id');
        $ghnOriginDistrictId = (int) config('services.ghn.origin_district_id', 1482);
        $ghnOriginWardCode = (string) config('services.ghn.origin_ward_code', '11007');

        // 1. Try GHN API if token and shop ID are configured
        if (!empty($ghnToken) && !empty($ghnShopId)) {
            try {
                $ghnLocation = $this->resolveGhnLocation($province, $district, $ward, $street);
                if (!empty($ghnLocation['district_id'])) {
                    $toDistrictId = (int) $ghnLocation['district_id'];
                    $toWardCode = $ghnLocation['ward_code'] ?? null;

                    $ghnFee = $this->calculateGhnFee(
                        $ghnOriginDistrictId,
                        $ghnOriginWardCode,
                        $toDistrictId,
                        $toWardCode
                    );

                    if ($ghnFee !== null && $ghnFee > 0) {
                        $usedGhn = true;
                        $ghnLeadtime = $this->calculateGhnLeadtime(
                            $ghnOriginDistrictId,
                            $ghnOriginWardCode,
                            $toDistrictId,
                            $toWardCode
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('GHN API Calculation Exception: ' . $e->getMessage());
            }
        }

        // 2. Compute Fees for the 3 Methods
        if ($usedGhn && $ghnFee !== null) {
            $standardFee = (float) $ghnFee;
            $standardDays = $ghnLeadtime['standard_time'] ?? ($provinceInfo['days_standard'] ?? '1 - 2 ngày');

            $fastFee = $standardFee + 10000;
            $fastDays = $ghnLeadtime['fast_time'] ?? ($isHanoiInner ? 'Trong 24h' : ($provinceInfo['days_fast'] ?? '1 - 2 ngày'));
        } else {
            // Fallback to Regional Matrix Fees
            if ($isHanoiInner) {
                $standardFee = 22000;
                $standardDays = '1 - 2 ngày';
                $fastFee = 32000;
                $fastDays = 'Trong 24h';
            } else {
                $standardFee = (float) ($provinceInfo['fee_standard'] ?? 28000);
                $standardDays = $provinceInfo['days_standard'] ?? '1 - 2 ngày';
                $fastFee = (float) ($provinceInfo['fee_fast'] ?? 38000);
                $fastDays = $provinceInfo['days_fast'] ?? '1 ngày';
            }
        }

        // EXPRESS (Giao hàng hoả tốc) - STRICTLY Hanoi inner city
        $expressFee = 55000;
        $expressDays = '2 - 4 giờ';
        $expressAvailable = $isHanoiInner;
        $expressDisabledReason = $isHanoiInner ? '' : 'Chỉ áp dụng cho đơn giao tại khu vực nội thành Hà Nội.';

        return [
            'success' => true,
            'source' => $usedGhn ? 'GHN_API' : 'REGIONAL_MATRIX',
            'origin' => $originAddress,
            'destination' => $fullDestination,
            'route_text' => $routeText,
            'distance_km' => null,
            'duration_text' => $standardDays,
            'is_hanoi_inner' => $isHanoiInner,
            'options' => [
                'standard' => [
                    'id' => 'standard',
                    'name' => 'Giao hàng tiêu chuẩn',
                    'desc' => $usedGhn ? 'Vận chuyển thực tế bởi Giao Hàng Nhanh (GHN)' : 'Giao qua đối tác vận chuyển',
                    'fee' => $standardFee,
                    'time' => $standardDays,
                    'available' => true,
                ],
                'fast' => [
                    'id' => 'fast',
                    'name' => 'Giao hàng nhanh',
                    'desc' => $usedGhn ? 'Ưu tiên giao sớm qua GHN Express' : 'Ưu tiên xử lý trong ngày',
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
     * Resolve Customer Province, District, Ward to GHN IDs using cached master data
     */
    public function resolveGhnLocation(?string $province, ?string $district = '', ?string $ward = '', ?string $street = ''): ?array
    {
        $token = config('services.ghn.api_token') ?: config('services.ghn.token');
        $apiUrl = config('services.ghn.api_url', 'https://online-gateway.ghn.vn/shiip/public-api');
        if (empty($token) || empty($province)) {
            return null;
        }

        // 1. Fetch & Cache Provinces (24 hours)
        $provinces = Cache::remember('ghn_provinces', 86400, function () use ($apiUrl, $token) {
            try {
                $response = Http::withHeaders(['Token' => $token])
                    ->timeout(7)
                    ->get("{$apiUrl}/master-data/province");
                return $response->successful() ? ($response->json('data') ?? []) : [];
            } catch (\Throwable $e) {
                Log::warning('GHN master-data province fetch failed: ' . $e->getMessage());
                return [];
            }
        });

        if (empty($provinces)) {
            return null;
        }

        $provinceIdStr = $this->findBestLocationMatch($provinces, 'ProvinceName', 'ProvinceID', $province);
        $provinceId = $provinceIdStr ? (int) $provinceIdStr : null;

        if (!$provinceId) {
            return null;
        }

        // 2. Fetch & Cache Districts (24 hours)
        $districts = Cache::remember('ghn_districts_' . $provinceId, 86400, function () use ($apiUrl, $token, $provinceId) {
            try {
                $response = Http::withHeaders(['Token' => $token])
                    ->timeout(7)
                    ->get("{$apiUrl}/master-data/district", ['province_id' => $provinceId]);
                return $response->successful() ? ($response->json('data') ?? []) : [];
            } catch (\Throwable $e) {
                Log::warning('GHN master-data district fetch failed: ' . $e->getMessage());
                return [];
            }
        });

        if (empty($districts)) {
            return ['province_id' => $provinceId, 'district_id' => null, 'ward_code' => null];
        }

        $districtId = null;
        $wardCode = null;

        // 2.1. Direct match by $district if provided
        if (!empty($district)) {
            $districtIdStr = $this->findBestLocationMatch($districts, 'DistrictName', 'DistrictID', $district);
            if ($districtIdStr) {
                $districtId = (int) $districtIdStr;
            }
        }

        // 2.2. If district is not specified (e.g. 2-level address form), match $ward against district names
        // Example: "Xã Quốc Oai" -> Huyện Quốc Oai (2004)
        // Example: "Phường Cầu Giấy" -> Quận Cầu Giấy (1485)
        // Example: "Phường Sơn Tây" -> Thị xã Sơn Tây (1711)
        // Example: "Xã Ba Vì" -> Huyện Ba Vì (1803)
        // Example: "Phường Hà Đông" -> Quận Hà Đông (1542)
        if (!$districtId && !empty($ward)) {
            $districtIdStr = $this->findBestLocationMatch($districts, 'DistrictName', 'DistrictID', $ward);
            if ($districtIdStr) {
                $districtId = (int) $districtIdStr;
            }
        }

        // 2.3. Check if any district name is in $street or full address
        if (!$districtId && !empty($street)) {
            $streetClean = $this->cleanString($street);
            foreach ($districts as $d) {
                $dNorm = $this->normalizeLocationName($d['DistrictName'] ?? '');
                if (!empty($dNorm) && mb_strlen($dNorm) >= 3 && str_contains($streetClean, $dNorm)) {
                    $districtId = (int) $d['DistrictID'];
                    break;
                }
            }
        }

        // 2.4. Search which district owns this $ward across the districts of this province
        if (!$districtId && !empty($ward)) {
            foreach ($districts as $d) {
                $dId = (int) $d['DistrictID'];
                $wards = Cache::remember('ghn_wards_' . $dId, 86400, function () use ($apiUrl, $token, $dId) {
                    try {
                        $response = Http::withHeaders(['Token' => $token])
                            ->timeout(4)
                            ->get("{$apiUrl}/master-data/ward", ['district_id' => $dId]);
                        return $response->successful() ? ($response->json('data') ?? []) : [];
                    } catch (\Throwable $e) {
                        return [];
                    }
                });

                if (!empty($wards)) {
                    $matchedWardCode = $this->findBestLocationMatch($wards, 'WardName', 'WardCode', $ward);
                    if ($matchedWardCode) {
                        $districtId = $dId;
                        $wardCode = $matchedWardCode;
                        break;
                    }
                }
            }
        }

        // 2.5. Heuristic fallback: if in Hanoi and inner city, default to Cau Giay (1485), otherwise first district
        if (!$districtId && !empty($districts)) {
            if ($this->isHanoiInnerCity($province, $district, $ward)) {
                $cg = $this->findBestLocationMatch($districts, 'DistrictName', 'DistrictID', 'Cầu Giấy');
                $districtId = $cg ? (int) $cg : (int) $districts[0]['DistrictID'];
            } else {
                $districtId = (int) ($districts[0]['DistrictID'] ?? null);
            }
        }

        if (!$districtId) {
            return ['province_id' => $provinceId, 'district_id' => null, 'ward_code' => null];
        }

        // 3. Fetch & Cache Wards (if not already matched in Step 2.4)
        if (!$wardCode && !empty($ward)) {
            $wards = Cache::remember('ghn_wards_' . $districtId, 86400, function () use ($apiUrl, $token, $districtId) {
                try {
                    $response = Http::withHeaders(['Token' => $token])
                        ->timeout(7)
                        ->get("{$apiUrl}/master-data/ward", ['district_id' => $districtId]);
                    return $response->successful() ? ($response->json('data') ?? []) : [];
                } catch (\Throwable $e) {
                    Log::warning('GHN master-data ward fetch failed: ' . $e->getMessage());
                    return [];
                }
            });

            if (!empty($wards)) {
                $wardCode = $this->findBestLocationMatch($wards, 'WardName', 'WardCode', $ward);
            }
        }

        return [
            'province_id' => $provinceId,
            'district_id' => $districtId,
            'ward_code' => $wardCode
        ];
    }

    /**
     * Find best matching item from a list of GHN location items
     */
    protected function findBestLocationMatch(array $items, string $nameKey, string $idKey, ?string $target): ?string
    {
        if (empty($target) || empty($items)) {
            return null;
        }

        $targetNorm = $this->normalizeLocationName($target);
        $targetClean = $this->cleanString($target);

        // Pass 1: Exact match on normalized or clean name (or extensions)
        foreach ($items as $item) {
            $itemNorm = $this->normalizeLocationName($item[$nameKey] ?? '');
            $itemClean = $this->cleanString($item[$nameKey] ?? '');

            if ($itemNorm === $targetNorm || $itemClean === $targetClean || $itemClean === $targetNorm || $itemNorm === $targetClean) {
                return (string) $item[$idKey];
            }

            if (!empty($item['NameExtension']) && is_array($item['NameExtension'])) {
                foreach ($item['NameExtension'] as $ext) {
                    $extNorm = $this->normalizeLocationName($ext);
                    $extClean = $this->cleanString($ext);
                    if ($extNorm === $targetNorm || $extClean === $targetClean) {
                        return (string) $item[$idKey];
                    }
                }
            }
        }

        // Pass 2: If target is purely digits (e.g. "1", "2"), do not do loose substring matching
        if (is_numeric($targetNorm)) {
            foreach ($items as $item) {
                $itemNorm = $this->normalizeLocationName($item[$nameKey] ?? '');
                if ($itemNorm === $targetNorm) {
                    return (string) $item[$idKey];
                }
            }
            return null;
        }

        // Pass 3: Substring matching for named locations
        foreach ($items as $item) {
            $itemNorm = $this->normalizeLocationName($item[$nameKey] ?? '');
            if (!empty($itemNorm) && (str_contains($itemNorm, $targetNorm) || str_contains($targetNorm, $itemNorm))) {
                return (string) $item[$idKey];
            }
        }

        return null;
    }

    /**
     * Calculate GHN Shipping Fee via API
     */
    public function calculateGhnFee(int $fromDistrictId, string $fromWardCode, int $toDistrictId, ?string $toWardCode = null): ?float
    {
        $token = config('services.ghn.api_token') ?: config('services.ghn.token');
        $shopId = (int) config('services.ghn.shop_id');
        $apiUrl = config('services.ghn.api_url', 'https://online-gateway.ghn.vn/shiip/public-api');

        try {
            $payload = [
                'service_type_id' => 2, // Standard delivery
                'from_district_id' => $fromDistrictId,
                'from_ward_code' => $fromWardCode,
                'to_district_id' => $toDistrictId,
                'weight' => 500,
                'length' => 20,
                'width' => 15,
                'height' => 10,
            ];
            if (!empty($toWardCode)) {
                $payload['to_ward_code'] = (string) $toWardCode;
            }

            $response = Http::withHeaders([
                'Token' => $token,
                'ShopId' => $shopId,
                'Content-Type' => 'application/json',
            ])->timeout(8)->post("{$apiUrl}/v2/shipping-order/fee", $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['code'] ?? 0) === 200 && isset($data['data']['total'])) {
                    return (float) $data['data']['total'];
                }
            }
            Log::warning('GHN Fee API non-200 response: ' . $response->body());
        } catch (\Throwable $e) {
            Log::warning('GHN Fee API Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Calculate GHN Delivery Leadtime via API with graceful fallback
     */
    public function calculateGhnLeadtime(int $fromDistrictId, string $fromWardCode, int $toDistrictId, ?string $toWardCode = null): ?array
    {
        $token = config('services.ghn.api_token') ?: config('services.ghn.token');
        $shopId = (int) config('services.ghn.shop_id');
        $apiUrl = config('services.ghn.api_url', 'https://online-gateway.ghn.vn/shiip/public-api');

        try {
            $payload = [
                'from_district_id' => $fromDistrictId,
                'from_ward_code' => $fromWardCode,
                'to_district_id' => $toDistrictId,
                'service_id' => 53320,
            ];
            if (!empty($toWardCode)) {
                $payload['to_ward_code'] = (string) $toWardCode;
            }

            $response = Http::withHeaders([
                'Token' => $token,
                'ShopId' => $shopId,
                'Content-Type' => 'application/json',
            ])->timeout(2)->post("{$apiUrl}/v2/shipping-order/leadtime", $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['code'] ?? 0) === 200 && !empty($data['data']['leadtime_order'])) {
                    $order = $data['data']['leadtime_order'];
                    $fromDate = !empty($order['from_estimate_date']) ? date('d/m', strtotime($order['from_estimate_date'])) : null;
                    $toDate = !empty($order['to_estimate_date']) ? date('d/m', strtotime($order['to_estimate_date'])) : null;

                    if ($fromDate && $toDate) {
                        return [
                            'standard_time' => $fromDate === $toDate ? $fromDate : "{$fromDate} - {$toDate}",
                            'fast_time' => $fromDate
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Gracefully ignore leadtime error/timeout
        }

        return null;
    }

    /**
     * Normalize Location Name by removing common administrative prefixes
     */
    protected function normalizeLocationName(?string $str): string
    {
        $clean = $this->cleanString($str);
        $prefixes = [
            'thanh pho ', 'tp. ', 'tp ', 'tinh ',
            'quan ', 'huyen ', 'thi xa ', 'tx. ', 'tx ',
            'phuong ', 'p. ', 'p ', 'xa ', 'thi tran ', 'tt. ', 'tt '
        ];
        foreach ($prefixes as $prefix) {
            if (str_starts_with($clean, $prefix)) {
                $clean = substr($clean, strlen($prefix));
                break;
            }
        }
        return trim($clean);
    }

    protected function getProvinceInfo(?string $province): array
    {
        if (empty($province)) {
            return [
                'region' => 'NORTH_INNER',
                'days_standard' => '1 - 2 ngày',
                'days_fast' => 'Trong ngày',
                'fee_standard' => 22000,
                'fee_fast' => 32000,
            ];
        }

        $cleanProv = $this->cleanString($province);

        // Miền Bắc
        foreach ($this->northernProvinces as $prov) {
            if (str_contains($cleanProv, $this->cleanString($prov))) {
                return [
                    'region' => 'NORTH',
                    'days_standard' => '1 - 2 ngày',
                    'days_fast' => '1 ngày',
                    'fee_standard' => 28000,
                    'fee_fast' => 38000,
                ];
            }
        }

        // Miền Trung & Tây Nguyên
        foreach ($this->centralProvinces as $prov) {
            if (str_contains($cleanProv, $this->cleanString($prov))) {
                return [
                    'region' => 'CENTRAL',
                    'days_standard' => '2 - 3 ngày',
                    'days_fast' => '2 ngày',
                    'fee_standard' => 32000,
                    'fee_fast' => 48000,
                ];
            }
        }

        // Miền Nam (Tất cả các tỉnh thành còn lại)
        return [
            'region' => 'SOUTH',
            'days_standard' => '3 - 4 ngày',
            'days_fast' => '2 - 3 ngày',
            'fee_standard' => 38000,
            'fee_fast' => 48000,
        ];
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
