<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function index(Request $request, Product $product): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 10), 1), 20);
        $query = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('status', 'approved');
        $summary = (clone $query)->selectRaw('COUNT(*) as total, AVG(rating) as average')->first();
        $ratingCounts = (clone $query)->selectRaw('rating, COUNT(*) as total')->groupBy('rating')->pluck('total', 'rating');
        $reviews = $query->with('user:id,name')->latest()->paginate($perPage);

        return response()->json([
            'reviews' => $reviews->items(),
            'average' => round((float) $summary->average, 1),
            'total' => (int) $summary->total,
            'rating_counts' => $ratingCounts,
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'min:3', 'max:150', 'regex:/^[\pL\pN\s.,!?()\x27-]+$/u'],
            'comment' => ['required', 'string', 'min:10', 'max:2000', 'regex:/^[\pL\pN\s.,!?()\x27"\-\r\n]+$/u'],
        ], [
            'title.regex' => 'Review title cannot contain @, # or other special characters.',
            'comment.regex' => 'Review cannot contain @, # or other special characters.',
        ]);

        $alreadyExists = ProductReview::where('product_id', $product->id)
            ->where('user_id', $request->user()->id)->exists();
        if ($alreadyExists) return response()->json(['message' => 'You have already reviewed this product.'], 422);

        $verified = OrderItem::where('product_id', $product->id)
            ->whereHas('order', fn ($query) => $query->where('user_id', $request->user()->id)->where('order_status', 'delivered'))
            ->exists();

        ProductReview::create($data + [
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'verified_purchase' => $verified,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Your review was submitted and is waiting for approval.']);
    }
}
