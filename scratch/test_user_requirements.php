<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test 1: Home page has no top-announcement
$responseHome = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle(
    Illuminate\Http\Request::create('/', 'GET')
);
$homeContent = $responseHome->getContent();
$hasTopAnnouncementHome = strpos($homeContent, 'top-announcement') !== false;
echo "[TEST 1 - Home Top Bar] Contains top-announcement: " . ($hasTopAnnouncementHome ? "FAIL (found)" : "PASS (removed)") . "\n";

// Test 2: Products page STILL has top-announcement (should only be removed from home page)
$responseShop = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle(
    Illuminate\Http\Request::create('/products', 'GET')
);
$shopContent = $responseShop->getContent();
$hasTopAnnouncementShop = strpos($shopContent, 'top-announcement') !== false;
echo "[TEST 2 - Other Pages Top Bar] Products contains top-announcement: " . ($hasTopAnnouncementShop ? "PASS (kept)" : "FAIL (missing)") . "\n";

// Test 3: Admin product create page
$user = App\Models\User::where('role', 'ADMIN')->first();
if ($user) {
    Auth::login($user);
    $responseCreate = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle(
        Illuminate\Http\Request::create('/admin/products/create', 'GET')
    );
    $createContent = $responseCreate->getContent();

    echo "[TEST 3 - Admin Create Status] " . $responseCreate->getStatusCode() . "\n";

    // Check unified button
    $hasUnifiedBtn = strpos($createContent, 'Tạo nhanh / Áp dụng hàng loạt') !== false;
    echo "[TEST 4 - Unified Button] Found button 'Tạo nhanh / Áp dụng hàng loạt': " . ($hasUnifiedBtn ? "PASS" : "FAIL") . "\n";

    // Check old drawer removed
    $hasOldDrawer = strpos($createContent, 'generator-drawer') !== false;
    echo "[TEST 5 - Old Drawer Removed] Old drawer removed: " . (!$hasOldDrawer ? "PASS" : "FAIL") . "\n";

    // Check combined-bulk-modal present
    $hasCombinedModal = strpos($createContent, 'combined-bulk-modal') !== false;
    echo "[TEST 6 - Combined Modal] Has combined-bulk-modal: " . ($hasCombinedModal ? "PASS" : "FAIL") . "\n";

    // Check initial variants have NO prefilled values
    $hasPreFilledSize = strpos($createContent, "size: '35cm'") !== false;
    $hasPreFilledPrice = strpos($createContent, "price: 450000") !== false;
    $hasPreFilledStock = strpos($createContent, "stock_quantity: 20") !== false;
    echo "[TEST 7 - No Pre-filled Variant Values] size 35cm: " . (!$hasPreFilledSize ? "PASS" : "FAIL") . ", price 450000: " . (!$hasPreFilledPrice ? "PASS" : "FAIL") . ", stock 20: " . (!$hasPreFilledStock ? "PASS" : "FAIL") . "\n";

    // Check placeholders exist in modal and table inputs
    $hasSizesPlaceholder = strpos($createContent, '30cm, 40cm') !== false;
    $hasColorsPlaceholder = strpos($createContent, 'Nâu socola, Vàng bơ') !== false;
    $hasPricePlaceholder = strpos($createContent, '550.000') !== false;
    $hasStockPlaceholder = strpos($createContent, '20') !== false;
    echo "[TEST 8 - Direct Placeholders] Sizes: " . ($hasSizesPlaceholder ? "PASS" : "FAIL") . ", Colors: " . ($hasColorsPlaceholder ? "PASS" : "FAIL") . ", Price: " . ($hasPricePlaceholder ? "PASS" : "FAIL") . ", Stock: " . ($hasStockPlaceholder ? "PASS" : "FAIL") . "\n";

    // Check image sync handler
    $hasImageSync = strpos($createContent, 'sourceVariantUid') !== false;
    echo "[TEST 9 - Variant Image Sync] Has sourceVariantUid sync: " . ($hasImageSync ? "PASS" : "FAIL") . "\n";

    // Admin Product Edit
    $prod = App\Models\Product::first();
    if ($prod) {
        $responseEdit = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle(
            Illuminate\Http\Request::create('/admin/products/' . $prod->id . '/edit', 'GET')
        );
        $editContent = $responseEdit->getContent();
        $hasEditUnifiedBtn = strpos($editContent, 'Tạo nhanh / Áp dụng hàng loạt') !== false;
        $hasEditModal = strpos($editContent, 'combined-bulk-modal') !== false;
        $hasEditSync = strpos($editContent, 'sourceVariantUid') !== false;
        echo "[TEST 10 - Admin Edit Page] Unified Btn: " . ($hasEditUnifiedBtn ? "PASS" : "FAIL") . ", Modal: " . ($hasEditModal ? "PASS" : "FAIL") . ", Image Sync: " . ($hasEditSync ? "PASS" : "FAIL") . "\n";
    }

    // Admin Products Index layout test
    $responseIndex = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle(
        Illuminate\Http\Request::create('/admin/products', 'GET')
    );
    echo "[TEST 11 - Admin Index Status] " . $responseIndex->getStatusCode() . "\n";
}
