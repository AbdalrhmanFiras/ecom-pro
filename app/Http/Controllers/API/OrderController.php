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

        $request->validate([// inside the item
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'itmes.*.quantity' => 'required|integer|min:1'
        ]);
        // Create the order

        $order = Order::create([// the main two / out the item
            'user_id' => Auth::id(),
            'total' => 0
        ]);

        $total = 0;
        // Add items to the order
        // البحث عن المنتج في قاعدة البيانات

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);

            $orderitem = OrderItem::create([//inside the item
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price
            ]);

            $total += $product->price * $item['quantity']; // outside 

            $order->update(['total' => $total]);



            return response()->json($order->load('items'), 201);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order->load('items'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {

        try {
            // Ensure the authenticated user can only update their own orders
            if ($order->user_id !== Auth::id())
                return response()->json(['message' => 'Unauthorized'], 403);


            // Validate the request data
            $request->validate([
                'items' => 'sometimes|array',
                'items.*.product_id' => 'sometimes|exists:products,id',
                'items.*.quantity' => 'sometimes|integer|min:1',
            ]);

            $total = 0;

            // Update or add items to the order
            if ($request->has('items')) {
                // Delete existing order items
                $order->items()->delete();

                // Add new items
                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);

                    // Create order item
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                    ]);

                    // Calculate the total
                    $total += $product->price * $item['quantity'];
                }

                // Update the order total
                $order->update(['total' => $total]);
            }

            // Return the updated order with its items
            return response()->json($order->load('items'));
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'message' => 'An error occurred while updating the order.',
                'error' => $e->getMessage(),
            ], 500);
        }

    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
