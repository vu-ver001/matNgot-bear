<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductPublicController extends Controller
{
    /**
     * Danh sách sản phẩm (Search, Filter, Sort, Phân trang) cho Khách hàng.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('status', 'ACTIVE')
            ->with([
                'category:id,name',
                'images' => function ($q) {
                    $q->orderByDesc('is_primary')->orderBy('sort_order', 'asc');
                },
            ]);

        // 1. Tìm kiếm từ khóa thông minh (Không phân biệt hoa/thường, hỗ trợ cả không dấu và gần đúng)
        if ($request->filled('search')) {
            $keyword = trim($request->input('search'));
            $keywordNoAccent = self::removeVietnameseAccents($keyword);
            $words = preg_split('/\s+/', $keyword, -1, PREG_SPLIT_NO_EMPTY);

            // Tìm kiếm các ID khớp theo cả tiếng Việt không dấu
            $unaccentMatchedIds = Product::where('status', 'ACTIVE')->get()->filter(function ($p) use ($keywordNoAccent, $words) {
                $nameNoAccent = self::removeVietnameseAccents($p->name);
                $descNoAccent = self::removeVietnameseAccents($p->description ?? '');
                if (str_contains($nameNoAccent, $keywordNoAccent) || str_contains($descNoAccent, $keywordNoAccent)) {
                    return true;
                }
                if (count($words) > 1) {
                    $allMatch = true;
                    foreach ($words as $w) {
                        $wNoAccent = self::removeVietnameseAccents($w);
                        if (!str_contains($nameNoAccent, $wNoAccent)) {
                            $allMatch = false;
                            break;
                        }
                    }
                    if ($allMatch) return true;
                }
                return false;
            })->pluck('id')->toArray();

            $query->where(function ($q) use ($keyword, $words, $unaccentMatchedIds) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%")
                  ->orWhereHas('category', function ($cq) use ($keyword) {
                      $cq->where('name', 'like', "%{$keyword}%");
                  });

                if (!empty($unaccentMatchedIds)) {
                    $q->orWhereIn('id', $unaccentMatchedIds);
                }

                if (count($words) > 1) {
                    $q->orWhere(function ($subQ) use ($words) {
                        foreach ($words as $word) {
                            $subQ->where('name', 'like', "%{$word}%");
                        }
                    });
                }
            });
        }

        // Lọc theo voucher áp dụng
        if ($request->filled('voucher')) {
            $voucher = Voucher::where('code', $request->input('voucher'))
                ->with(['categories', 'products'])
                ->first();

            if ($voucher) {
                if ($voucher->apply_scope === 'CATEGORY') {
                    $catIds = $voucher->categories->pluck('id')->toArray();
                    $query->whereIn('category_id', $catIds);
                } elseif ($voucher->apply_scope === 'PRODUCT') {
                    $prodIds = $voucher->products->pluck('id')->toArray();
                    $query->whereIn('id', $prodIds);
                }
            }
        }

        // 2. Lọc theo danh mục
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // 3. Lọc theo khoảng giá bán thực tế (Ăn khớp cả sản phẩm cha lẫn từng sản phẩm con variants)
        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;
        $effectivePriceSql = 'CASE WHEN sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price THEN sale_price ELSE price END';

        if ($minPrice !== null || $maxPrice !== null) {
            $query->where(function ($q) use ($minPrice, $maxPrice, $effectivePriceSql) {
                // Thỏa mãn theo giá sản phẩm cha
                $q->where(function ($parentQ) use ($minPrice, $maxPrice, $effectivePriceSql) {
                    if ($minPrice !== null) {
                        $parentQ->whereRaw("({$effectivePriceSql}) >= ?", [$minPrice]);
                    }
                    if ($maxPrice !== null) {
                        $parentQ->whereRaw("({$effectivePriceSql}) <= ?", [$maxPrice]);
                    }
                });

                // HOẶC thỏa mãn theo giá của bất kỳ biến thể con (product_variants) nào
                $q->orWhereHas('variants', function ($vq) use ($minPrice, $maxPrice) {
                    $variantEffectivePrice = 'CASE WHEN sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price THEN sale_price ELSE price END';
                    if ($minPrice !== null) {
                        $vq->whereRaw("({$variantEffectivePrice}) >= ?", [$minPrice]);
                    }
                    if ($maxPrice !== null) {
                        $vq->whereRaw("({$variantEffectivePrice}) <= ?", [$maxPrice]);
                    }
                });
            });
        }

        // 4. Lọc theo kích thước (Size) - Khớp cả sản phẩm cha lẫn sản phẩm con variants trong CSDL
        $sizeInput = $request->filled('size') ? trim($request->input('size')) : null;
        $sizeRange = $request->filled('size_range') ? trim($request->input('size_range')) : null;
        $minSize = $request->filled('size_min') ? (int) $request->input('size_min') : null;
        $maxSize = $request->filled('size_max') ? (int) $request->input('size_max') : null;

        // Parse range nếu truyền size_range dạng "25-40", "45-60", "60-120", "120-180"
        if ($sizeRange && preg_match('/^(\d+)\s*-\s*(\d+)$/', $sizeRange, $rm)) {
            $minSize = (int) $rm[1];
            $maxSize = (int) $rm[2];
        }

        if ($minSize !== null || $maxSize !== null || ($sizeInput !== null && $sizeInput !== '')) {
            // Lấy tất cả distinct sizes có trong DB để so khớp chính xác
            $allDbSizes = \App\Models\ProductVariant::distinct()->pluck('size')
                ->merge(\App\Models\Product::distinct()->pluck('size'))
                ->filter()
                ->unique()
                ->values();

            $matchedSizes = [];

            if ($sizeInput !== null && $sizeInput !== '') {
                $inputCm = self::parseSizeToCm($sizeInput);
                foreach ($allDbSizes as $s) {
                    $cm = self::parseSizeToCm($s);
                    if ($cm !== null && $inputCm !== null && $cm === $inputCm) {
                        $matchedSizes[] = $s;
                    } elseif (stripos($s, $sizeInput) !== false) {
                        $matchedSizes[] = $s;
                    }
                }
                $matchedSizes[] = $sizeInput;
                $matchedSizes[] = $sizeInput . 'cm';
            } elseif ($minSize !== null && $maxSize !== null) {
                foreach ($allDbSizes as $s) {
                    $cm = self::parseSizeToCm($s);
                    if ($cm !== null && $cm >= $minSize && $cm <= $maxSize) {
                        $matchedSizes[] = $s;
                    }
                }
            }

            $matchedSizes = array_unique(array_filter($matchedSizes));

            $query->where(function ($q) use ($matchedSizes, $sizeInput) {
                if (!empty($matchedSizes)) {
                    $q->whereIn('size', $matchedSizes)
                      ->orWhereHas('variants', function ($vq) use ($matchedSizes) {
                          $vq->whereIn('size', $matchedSizes);
                      });
                }
                if ($sizeInput) {
                    $q->orWhere('size', 'like', "%{$sizeInput}%")
                      ->orWhereHas('variants', function ($vq) use ($sizeInput) {
                          $vq->where('size', 'like', "%{$sizeInput}%");
                      });
                }
            });
        }

        // 5. Lọc theo màu sắc (color)
        if ($request->filled('color')) {
            $query->where('color', 'like', '%' . trim($request->input('color')) . '%');
        }

        // 6. Lọc theo chất liệu (material)
        if ($request->filled('material')) {
            $query->where('material', 'like', '%' . trim($request->input('material')) . '%');
        }

        // 7. Lọc còn hàng (in_stock)
        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }

        // 8. Sắp xếp (Sort)
        $defaultSort = $request->filled('category_id') ? 'best_seller' : 'latest';
        $sort = $request->input('sort', $defaultSort);
        match ($sort) {
            'price_asc'   => $query->orderByRaw("({$effectivePriceSql}) ASC"),
            'price_desc'  => $query->orderByRaw("({$effectivePriceSql}) DESC"),
            'best_seller' => $query->orderByDesc('sold_count'),
            default       => $query->orderByDesc('created_at'),
        };


        // Phân trang (mặc định 12 sản phẩm/trang)
        $perPage = (int) $request->input('per_page', 12);
        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * Chi tiết sản phẩm kèm category, images, avg_rating, reviews_count và is_in_stock.
     */
    public function show(int|string $id): JsonResponse
    {
        $product = Product::query()
            ->where('id', $id)
            ->where('status', 'ACTIVE')
            ->with([
                'category',
                'images' => function ($q) {
                    $q->orderBy('sort_order', 'asc');
                },
                'variants' => function ($q) {
                    $q->where('status', 'ACTIVE')->orderBy('price', 'asc');
                },
            ])
            ->withAvg(['reviews as avg_rating' => function ($q) {
                $q->where('is_hidden', false);
            }], 'rating')
            ->withCount(['reviews' => function ($q) {
                $q->where('is_hidden', false);
            }])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.',
            ], 404);
        }

        // Bổ sung các thuộc tính tính toán tiện lợi
        $productArray = $product->toArray();
        $productArray['avg_rating'] = $product->avg_rating ? round((float) $product->avg_rating, 1) : 5.0;
        $productArray['is_in_stock'] = $product->stock_quantity > 0;

        return response()->json([
            'success' => true,
            'data'    => $productArray,
        ]);
    }

    /**
     * Lấy 8 sản phẩm nổi bật / bán chạy nhất để hiển thị Trang chủ.
     */
    public function featured(): JsonResponse
    {
        $products = Product::query()
            ->where('status', 'ACTIVE')
            ->with([
                'category:id,name',
                'images' => function ($q) {
                    $q->orderByDesc('is_primary')->orderBy('sort_order', 'asc');
                },
            ])
            ->orderByDesc('sold_count')
            ->take(8)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }

    /**
     * Gợi ý tìm kiếm tức thì trên header (Live Search Suggestions / Autocomplete).
     * Tìm kiếm không phân biệt in hoa / thường, có dấu / không dấu và gần đúng.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $keyword = trim($request->input('q', $request->input('search', '')));
        if (empty($keyword) || mb_strlen($keyword, 'UTF-8') < 1) {
            return response()->json([
                'success' => true,
                'data'    => [],
            ]);
        }

        $keywordLower = mb_strtolower($keyword, 'UTF-8');
        $keywordNoAccent = self::removeVietnameseAccents($keyword);
        $words = preg_split('/\s+/', $keyword, -1, PREG_SPLIT_NO_EMPTY);

        // Lấy danh sách sản phẩm đang kinh doanh
        $allActive = Product::where('status', 'ACTIVE')
            ->with([
                'category:id,name',
                'images' => fn($q) => $q->orderByDesc('is_primary')->orderBy('sort_order', 'asc'),
                'variants' => fn($q) => $q->where('status', 'ACTIVE')->orderBy('price', 'asc'),
            ])
            ->get();

        $matched = $allActive->map(function ($p) use ($keyword, $keywordLower, $keywordNoAccent, $words) {
            $name = $p->name ?? '';
            $desc = $p->description ?? '';
            $catName = $p->category->name ?? '';
            
            $nameLower = mb_strtolower($name, 'UTF-8');
            $nameNoAccent = self::removeVietnameseAccents($name);
            $descNoAccent = self::removeVietnameseAccents($desc);
            $catNoAccent = self::removeVietnameseAccents($catName);

            $score = 0;

            // 1. Tên bắt đầu bằng từ khóa
            if (str_starts_with($nameLower, $keywordLower) || str_starts_with($nameNoAccent, $keywordNoAccent)) {
                $score += 150;
            }

            // 2. Tên chứa nguyên vẹn từ khóa
            if (str_contains($nameLower, $keywordLower)) {
                $score += 100;
            } elseif (str_contains($nameNoAccent, $keywordNoAccent)) {
                $score += 80;
            }

            // 3. Tên khớp từng từ
            if (count($words) > 1) {
                $wordsMatchedCount = 0;
                foreach ($words as $w) {
                    $wLower = mb_strtolower($w, 'UTF-8');
                    $wNoAccent = self::removeVietnameseAccents($w);
                    if (str_contains($nameLower, $wLower) || str_contains($nameNoAccent, $wNoAccent)) {
                        $wordsMatchedCount++;
                    }
                }
                if ($wordsMatchedCount === count($words)) {
                    $score += 70;
                } elseif ($wordsMatchedCount > 0) {
                    $score += $wordsMatchedCount * 15;
                }
            }

            // 4. Danh mục khớp
            if (str_contains($catNoAccent, $keywordNoAccent) || mb_stripos($catName, $keyword) !== false) {
                $score += 40;
            }

            // 5. Mô tả khớp
            if (str_contains($descNoAccent, $keywordNoAccent) || mb_stripos($desc, $keyword) !== false) {
                $score += 20;
            }

            // 6. Biến thể màu sắc / size khớp
            if ($p->variants) {
                foreach ($p->variants as $v) {
                    if (mb_stripos($v->color, $keyword) !== false || str_contains(self::removeVietnameseAccents($v->color), $keywordNoAccent)) {
                        $score += 15;
                        break;
                    }
                }
            }

            $p->search_score = $score;
            return $p;
        })->filter(fn($p) => $p->search_score > 0)
          ->sortByDesc('search_score')
          ->take(8)
          ->values();

        $results = $matched->map(function ($p) {
            $primaryImg = $p->images->firstWhere('is_primary', true) ?? $p->images->first();
            $effectivePrice = ($p->sale_price && $p->sale_price > 0 && $p->sale_price < $p->price) ? $p->sale_price : $p->price;
            
            return [
                'id'              => $p->id,
                'name'            => $p->name,
                'price'           => (float) $p->price,
                'sale_price'      => $p->sale_price ? (float) $p->sale_price : null,
                'effective_price' => (float) $effectivePrice,
                'min_price'       => (float) $effectivePrice,
                'category'        => $p->category?->name,
                'category_name'   => $p->category?->name,
                'image_url'       => $primaryImg?->image_url ?? asset('images/home-hero-banner.png'),
                'thumbnail'       => $primaryImg?->image_url ?? asset('images/home-hero-banner.png'),
                'url'             => route('products.show', $p->id),
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $results->count(),
            'data'    => $results,
        ]);
    }

    /**
     * Bỏ dấu tiếng Việt phục vụ tìm kiếm gần đúng.
     */
    public static function removeVietnameseAccents(string $str): string
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/u", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/u", 'y', $str);
        $str = preg_replace("/(đ)/u", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/u", 'a', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/u", 'e', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/u", 'i', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/u", 'o', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/u", 'u', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/u", 'y', $str);
        $str = preg_replace("/(Đ)/u", 'd', $str);
        return mb_strtolower($str, 'UTF-8');
    }

    /**
     * Quy đổi chuỗi kích thước sang centimet (cm) để lọc chuẩn xác.
     */
    public static function parseSizeToCm(?string $sizeStr): ?int
    {
        if (!$sizeStr) return null;
        $str = mb_strtolower(trim($sizeStr), 'UTF-8');
        
        if (preg_match('/(\d+)\s*m\s*(\d*)/u', $str, $m)) {
            $meter = (int) $m[1];
            $dec = $m[2] !== '' ? (int) $m[2] : 0;
            if ($dec > 0 && $dec < 10) {
                return $meter * 100 + $dec * 10;
            }
            return $meter * 100 + $dec;
        }
        
        if (preg_match('/(\d+)/u', $str, $m)) {
            return (int) $m[1];
        }
        
        return null;
    }
}
