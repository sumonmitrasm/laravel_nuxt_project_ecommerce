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
    public function index(Product $product): JsonResponse
    {
        $reviews = ProductReview::with('user:id,name')
            ->where('product_id', $product->id)->where('status', 'approved')
            ->latest()->get();

        return response()->json([
            'reviews' => $reviews,
            'average' => round((float) $reviews->avg('rating'), 1),
            'total' => $reviews->count(),
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
