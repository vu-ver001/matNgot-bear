<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Display checkout page with selected cart items.
     */
    public function index(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $selectedItemIds = $request->input('selected_items', []);

        if (empty($selectedItemIds)) {
            return redirect()->route('customer.cart.index')->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.');
        }

        $userId = auth()->id() ?? \App\Models\User::where('role', 'CUSTOMER')->first()?->id ?? 1;

        $cartItems = CartItem::where('user_id', $userId)
            ->whereIn('id', $selectedItemIds)
            ->with(['product.images'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('customer.cart.index')->with('error', 'Sản phẩm đã chọn không hợp lệ hoặc đã bị xóa.');
        }

        // Calculate subtotal
        $subtotal = $cartItems->sum(function ($item) {
            $price = $item->product->sale_price ?? $item->product->price;
            return $price * $item->quantity;
        });

        $shippingFee = 30000; // Mặc định 30.000đ

        return view('customer.checkout.index', compact('cartItems', 'subtotal', 'shippingFee'));
    }

    /**
     * Process checkout and create order.
     */
    public function process(Request $request)
    {
        // TODO: Will be fully implemented in Order Creation step
    }
}

