<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequset;
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
    public function index(FilterRequset $request)
    {
        // add Rating Filter , Filter by Delivery Options , Filter by Warranty
        $query = Auth::user()->products();

        if ($request->has('search')) {//filter by name 
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('Categoery')) {//filter by Categoery 
            $query->where('Categoery', 'like', '%' . $request->category_id . '%');
        }

        if ($request->has('min_price')) {//filter by min_price 
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {//filter by max_price 
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('min_price') && ($request->has('max_price'))) {//filter by Range

            $query->whereBetween('price', [$request->min_price, $request->max_price]);
        }

        if ($request->has('equal')) {//filter by equal
            $query->where('price', '=', $request->equal);
        }

        if ($request->has('Availability')) {//filter by stock
            $query->where('stock', '>', '0');
        }

        // Get all matching products (without pagination)
        $products = $query->get();
        // i cant return the query directly in resourece , i use mapinto to make the query shape look like resourece and i return in json  
        // Use mapInto() on the collection 
        $mappedProducts = $products->mapInto(ProductResource::class);

        // Return the mapped products
        return response()->json($mappedProducts);
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
