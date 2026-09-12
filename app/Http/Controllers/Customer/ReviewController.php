<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Chuyển hướng tới trang Đánh giá sản phẩm theo đơn hàng (ReviewKT)
     */
    public function create(Request $request, Order $order)
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        return redirect()->route('customer.reviews.index', [
            'order_id' => $order->id,
            'tab' => 'pending',
        ]);
    }
}
