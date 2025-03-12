<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreVaildate;
use App\Http\Requests\ProductUpdateVaildate;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user()->products;

        return ProductResource::collection($user);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreVaildate $request)
    {
        $user_id = Auth::user()->id;//get current ID
        $storevaildate = $request->validated();
        $storevaildate['user_id'] = $user_id;
        $product = Product::create($storevaildate);
        return response()->json([
            'Product' => new ProductResource($product),
            'message' => 'Create Product Successfully'
        ], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product = Product::find($product);//search by id
        if (!$product) {
            abort(404, 'This product not found. Please verify the ID.');
        }
        return Productresource::collection($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateVaildate $request, Product $product)
    {
        $user_id = Auth::user()->id;

        if ($user_id != $product->user_id) {
            return response()->json('Not found', 404); // Respond with a 404 if the user is not the owner
        }

        $product->update($request->validated());

        return response()->json([
            'Product' => new ProductResource($product),
            'message' => 'Updated Product Successfully'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $user_id = Auth::user()->id;
        if ($user_id != $product->user_id) {
            return response()->json('Product Not found', 404);
        }
        $product->delete();
        return response()->json([
            'message' => 'the product deleted successfully',
            'product' => new ProductResource($product),
        ]);
    }
}
