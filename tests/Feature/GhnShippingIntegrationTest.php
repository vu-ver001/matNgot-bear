<?php

namespace Tests\Feature;

use App\Services\ShippingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GhnShippingIntegrationTest extends TestCase
{
    protected ShippingService $shippingService;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->shippingService = app(ShippingService::class);
    }

    /**
     * Setup mock HTTP responses for GHN API
     */
    protected function mockGhnApiSuccess(): void
    {
        Http::fake([
            '*/master-data/province' => Http::response([
                'code' => 200,
                'message' => 'Success',
                'data' => [
                    ['ProvinceID' => 201, 'ProvinceName' => 'Hà Nội', 'NameExtension' => ['Ha Noi', 'HN']],
                    ['ProvinceID' => 202, 'ProvinceName' => 'Hồ Chí Minh', 'NameExtension' => ['TP. Hồ Chí Minh', 'TP.HCM', 'Sài Gòn']],
                    ['ProvinceID' => 203, 'ProvinceName' => 'Đà Nẵng', 'NameExtension' => ['Da Nang']],
                ]
            ], 200),

            '*/master-data/district?province_id=201' => Http::response([
                'code' => 200,
                'data' => [
                    ['DistrictID' => 1485, 'ProvinceID' => 201, 'DistrictName' => 'Quận Cầu Giấy', 'NameExtension' => ['Cầu Giấy']],
                    ['DistrictID' => 1482, 'ProvinceID' => 201, 'DistrictName' => 'Quận Bắc Từ Liêm', 'NameExtension' => ['Bắc Từ Liêm']],
                ]
            ], 200),

            '*/master-data/district?province_id=202' => Http::response([
                'code' => 200,
                'data' => [
                    ['DistrictID' => 1442, 'ProvinceID' => 202, 'DistrictName' => 'Quận 1', 'NameExtension' => ['Q1']],
                    ['DistrictID' => 1454, 'ProvinceID' => 202, 'DistrictName' => 'Quận 12', 'NameExtension' => ['Q12']],
                ]
            ], 200),

            '*/master-data/district?province_id=203' => Http::response([
                'code' => 200,
                'data' => [
                    ['DistrictID' => 1444, 'ProvinceID' => 203, 'DistrictName' => 'Quận Hải Châu', 'NameExtension' => ['Hải Châu']],
                ]
            ], 200),

            '*/master-data/ward?district_id=1485' => Http::response([
                'code' => 200,
                'data' => [
                    ['WardCode' => '1A0602', 'DistrictID' => 1485, 'WardName' => 'Phường Dịch Vọng Hậu', 'NameExtension' => ['Dịch Vọng Hậu']],
                ]
            ], 200),

            '*/master-data/ward?district_id=1442' => Http::response([
                'code' => 200,
                'data' => [
                    ['WardCode' => '20101', 'DistrictID' => 1442, 'WardName' => 'Phường Bến Nghé', 'NameExtension' => ['Bến Nghé']],
                ]
            ], 200),

            '*/master-data/ward?district_id=1444' => Http::response([
                'code' => 200,
                'data' => [
                    ['WardCode' => '20308', 'DistrictID' => 1444, 'WardName' => 'Phường Thạch Thang', 'NameExtension' => ['Thạch Thang']],
                ]
            ], 200),

            '*/v2/shipping-order/fee' => Http::response([
                'code' => 200,
                'message' => 'Success',
                'data' => [
                    'total' => 21000,
                    'service_fee' => 21000,
                ]
            ], 200),

            '*/v2/shipping-order/leadtime' => Http::response([
                'code' => 200,
                'message' => 'Success',
                'data' => [
                    'leadtime' => 1789232399,
                    'leadtime_order' => [
                        'from_estimate_date' => '2026-09-12T16:59:59Z',
                        'to_estimate_date' => '2026-09-13T16:59:59Z'
                    ]
                ]
            ], 200),
        ]);
    }

    /**
     * Test GHN location resolution for provinces, districts, and wards
     */
    public function test_resolves_ghn_location_accurately(): void
    {
        $this->mockGhnApiSuccess();

        // 1. Hanoi - Cau Giay - Dich Vong Hau
        $hn = $this->shippingService->resolveGhnLocation('Hà Nội', 'Cầu Giấy', 'Phường Dịch Vọng Hậu');
        $this->assertNotNull($hn);
        $this->assertEquals(201, $hn['province_id']);
        $this->assertEquals(1485, $hn['district_id']);
        $this->assertEquals('1A0602', $hn['ward_code']);

        // 2. TP.HCM - Quan 1 - Ben Nghe (Testing exact match avoiding Quan 12)
        $hcm = $this->shippingService->resolveGhnLocation('TP. Hồ Chí Minh', 'Quận 1', 'Phường Bến Nghé');
        $this->assertNotNull($hcm);
        $this->assertEquals(202, $hcm['province_id']);
        $this->assertEquals(1442, $hcm['district_id']);
        $this->assertEquals('20101', $hcm['ward_code']);
    }

    /**
     * Test calculateShippingOptions returns GHN live rates and leadtimes
     */
    public function test_calculate_shipping_options_returns_ghn_rates(): void
    {
        $this->mockGhnApiSuccess();

        $result = $this->shippingService->calculateShippingOptions('Hà Nội', 'Cầu Giấy', 'Phường Dịch Vọng Hậu', '123 Cầu Giấy');

        $this->assertTrue($result['success']);
        $this->assertEquals('GHN_API', $result['source']);
        $this->assertTrue($result['is_hanoi_inner']);
        $this->assertArrayHasKey('options', $result);
        $this->assertArrayHasKey('standard', $result['options']);
        $this->assertArrayHasKey('fast', $result['options']);
        $this->assertArrayHasKey('express', $result['options']);

        // Standard fee from GHN
        $this->assertEquals(21000, $result['options']['standard']['fee']);
        $this->assertEquals(31000, $result['options']['fast']['fee']);
        $this->assertEquals(55000, $result['options']['express']['fee']);
        $this->assertTrue($result['options']['standard']['available']);
        $this->assertStringContainsString('Giao Hàng Nhanh', $result['options']['standard']['desc']);

        // Express should be available for inner Hanoi
        $this->assertTrue($result['options']['express']['available']);
    }

    /**
     * Test calculateShippingOptions outside Hanoi disables Express and calculates regional rates
     */
    public function test_calculate_shipping_options_outside_hanoi_disables_express(): void
    {
        $this->mockGhnApiSuccess();

        $result = $this->shippingService->calculateShippingOptions('Đà Nẵng', 'Hải Châu', 'Phường Thạch Thang', '123 Lê Duẩn');

        $this->assertTrue($result['success']);
        $this->assertEquals('GHN_API', $result['source']);
        $this->assertFalse($result['is_hanoi_inner']);
        $this->assertFalse($result['options']['express']['available']);
        $this->assertNotEmpty($result['options']['express']['disabled_reason']);
        $this->assertEquals(21000, $result['options']['standard']['fee']);
    }

    /**
     * Test calculateShippingOptions fallback when GHN API fails or times out
     */
    public function test_calculate_shipping_options_falls_back_gracefully_when_ghn_fails(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $result = $this->shippingService->calculateShippingOptions('Hà Nội', 'Cầu Giấy', 'Phường Dịch Vọng Hậu', '123 Cầu Giấy');

        $this->assertTrue($result['success']);
        $this->assertEquals('REGIONAL_MATRIX', $result['source']);
        $this->assertEquals(22000, $result['options']['standard']['fee']);
        $this->assertEquals(32000, $result['options']['fast']['fee']);
        $this->assertTrue($result['options']['express']['available']);
    }

    /**
     * Test calculateShippingOptions fallback when location is unknown
     */
    public function test_calculate_shipping_options_falls_back_gracefully_on_unknown(): void
    {
        $result = $this->shippingService->calculateShippingOptions('Tỉnh Chưa Từng Có', 'Huyện Vô Danh', 'Xã Bí Mật');

        $this->assertTrue($result['success']);
        $this->assertEquals('REGIONAL_MATRIX', $result['source']);
        $this->assertGreaterThan(0, $result['options']['standard']['fee']);
    }

    /**
     * Test resolving district from ward name when 2-level form is used (without district field)
     */
    public function test_resolves_district_from_ward_when_district_is_not_passed(): void
    {
        $this->mockGhnApiSuccess();

        // When district is empty string, it should match 'Quận Cầu Giấy' from ward 'Phường Cầu Giấy'
        $location = $this->shippingService->resolveGhnLocation('Hà Nội', '', 'Phường Cầu Giấy');
        $this->assertNotNull($location);
        $this->assertEquals(1485, $location['district_id']);
    }
}

