<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product', 'order']);

        if ($request->filled('is_hidden')) {
            $query->where('is_hidden', $request->boolean('is_hidden'));
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->paginate(15);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function destroy(Review $review)
    {
        $review->update(['is_hidden' => !$review->is_hidden]);

        $status = $review->is_hidden ? 'ẩn' : 'hiện';

        return redirect()->back()->with('success', "Đã {$status} đánh giá thành công.");
    }
}
