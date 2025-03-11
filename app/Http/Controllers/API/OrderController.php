<?php

namespace App\Http\Controllers\API;
use App\Models\Order;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with('items')->where('user_id', auth()->id())->get();
        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {            // Validate the request data

        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'itmes.*.quantity' => 'required|integer|min:1'
        ]);
        // Create the order

        $order = Order::create([
            'user_id' => Auth::id(),
            'total' => 0
        ]);

        $total = 0;
        // Add items to the order

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);

            $orderitem = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price
            ]);

            $total += $product->price * $item['quantity'];

            $order->update(['total' => $total]);



            return response()->json($order->load('items'), 201);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
