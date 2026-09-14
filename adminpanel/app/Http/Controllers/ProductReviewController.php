<?php

namespace App\Http\Controllers;

use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;

class ProductReviewController extends Controller
{
    public function index()
    {
        $reviews = ProductReview::with(['product:id,product_name', 'user:id,name'])->latest()->paginate(20);
        return view('admin.review.index', compact('reviews'));
    }

    public function status(ProductReview $review, string $status)
    {
        abort_unless(in_array($status, ['approved', 'rejected'], true), 404);
        $review->update(['status' => $status]);

        if (request()->ajax()) {
            return response()->json(['message' => 'Review status updated.']);
        }

        return back()->with('success_message', 'Review status updated.');
    }

    public function destroy(ProductReview $review)
    {
        $review->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Review deleted.']);
        }

        return back()->with('success_message', 'Review deleted.');
    }
}
