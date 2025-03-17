<?php

namespace App\Http\Controllers\API;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Pest\Plugins\Only;

class ReviewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'commit' => 'required|string',
            'rating' => 'required|integer|between:1,5'
        ]);

        $review = Review::create([
            'product_id' => $request->product_id,
            'user_id' => auth()->id(),
            'commit' => $request->commit,
            'rating' => $request->rating
        ]);


        return response()->json(
            [
                'message' => 'review Added successfully',
                'review' => $review
            ],
            200
        );

    }

    /**
     * Display the specified resource.
     */
    public function show($productID)
    {
        $product_review = Review::where('product_id', $productID)->get();
        return response()->json($product_review);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'commit' => 'sometimes|string|min:12',
            'rating' => 'sometimes|integer|between:1,5'
        ]);

        $review = Review::findOrFail($id);

        if ($review->user_id != Auth::id())
            return response()->json(['message' => 'Unauthorized'], 403);


        $review->update($request->only('commit', 'rating'));

        return response()->json($review);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $review = Review::findOrFail($id);

        if ($review->user_id != Auth::id())
            return response()->json(['message' => 'Unauthorized'], 403);


        $review->delete();
        return response()->json(['message' => 'Review deleted successfully']);
    }
}
