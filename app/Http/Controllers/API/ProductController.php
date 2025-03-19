<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterRequset;
use App\Http\Requests\ProductStoreVaildate;
use App\Http\Requests\ProductUpdateVaildate;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FilterRequset $request)
    {
        $messages = [];
        // add Rating Filter ; later soon 
        $query = Product::query();

        if ($request->has('search')) {//filter by name 
            $query->where('name', 'like', '%' . $request->search . '%');
            $messages['search'] = ' no product found matching the name: ' . $request->search;
        }

        if ($request->has('category_id')) { // Filter by Category (Fixed Key)
            $query->where('category', 'like', '%' . $request->category_id . '%');
        }


        if ($request->has('min_price')) {//filter by min_price 
            $query->where('price', '>=', $request->min_price);
            $messages['min_price'] = 'no product found with a minimum price of :' . $request->min_price;
        }

        if ($request->has('max_price')) {//filter by max_price 
            $query->where('price', '<=', $request->max_price);
            $messages['max_price'] = 'no product found with a maximum price of :' . $request->max_price;
        }

        if ($request->has('price_range') && ($request->has('max_price'))) {//filter by Range

            $query->whereBetween('price', [$request->min_price, $request->max_price]);
            $messages['price_range'] = 'no product found with a price range' . $request->min_price . 'to' . $request->max_price;
        }

        if ($request->has('equal')) {//filter by equal
            $query->where('price', '=', $request->equal);
            $messages['equal'] = 'no product found equal' . $request->equal;
        }

        if ($request->has('Availability')) {//filter by stock
            $query->where('stock', '>', '0');

        }

        if ($request->has('warranty')) {
            $query->where('warranty', 'like', $request->warranty);
            $messages['warranty'] = 'no product have ' . $request->warranty . ' warranty';
        }

        // Filter by Rating
        if ($request->has('rating')) {
            $messages['rating'] = 'no product found with this rating range';
            $query->whereHas('reviews', function ($q) use ($request) {
                $q->where('rating', '=', $request->rating); // Filter by rating

            });
        }

        if ($request->has('max_rating')) {
            $messages['max_rating'] = 'no product found with this rating range';

            $query->whereHas('reviews', function ($q) use ($request) {
                $q->where('rating', '>', $request->max_rating);
            });

        }

        if ($request->has('min_rating')) {
            $messages['min_rating'] = 'no product found with this rating range';

            $query->whereHas('reviews', function ($q) use ($request) {
                $q->where('rating', '<', $request->min_rating);
            });

        }


        // Get all matching products (without pagination)
        $products = $query->get();


        if ($products->isEmpty()) {
            $repones = [];
            foreach ($messages as $key => $message) {
                if ($request->has($key)) {
                    $repones[$key] = $message;
                }
            }

            return response()->json([

                'errors' => $repones
            ], 404);
        }

        // i cant return the query directly in resourece , i use mapinto to make the query shape look like resourece and i return in json  
        // Use mapInto() on the collection 

        // Return the mapped products
        $mappedProducts = $products->mapInto(ProductResource::class);

        return response()->json($mappedProducts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreVaildate $request)
    {
        if (Gate::denies('create', Product::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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
        if (Gate::denies('update', Product::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
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

        if (Gate::denies('delete', Product::class)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
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
