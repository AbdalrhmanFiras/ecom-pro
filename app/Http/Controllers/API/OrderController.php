<?php

namespace App\Http\Controllers\API;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderItemResource;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\OrderStoreVaildate;
use App\Http\Requests\UpdateStoreVaildate;
use App\Enums\OrderStatus;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with('items')->where('user_id', auth()->id())->get();
        return OrderResource::collection($orders);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderStoreVaildate $request)
    {            // Validate the request data


        // Create the order

        $order = Order::create([// the main two / out the item
            'user_id' => Auth::id(),
            'total' => 0,
            'status' => OrderStatus::PENDING->value, // Default status

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


        }
        $order->update(['total' => $total]);

        return new OrderResource($order);



    }






    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $order->load('items');
        return new OrderResource($order);


    }







    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStoreVaildate $request, Order $order)
    {
        // Ensure the authenticated user is the owner of the order
        if ($order->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate the request
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $total = 0;

            // Get the product IDs from the request
            $requestProductIds = collect($request->items)->pluck('product_id')->toArray();

            // Remove items that are not in the request
            $order->items()->whereNotIn('product_id', $requestProductIds)->delete();

            // Loop through the items in the request
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                // Check if the item already exists in the order
                $existingItem = $order->items()->where('product_id', $item['product_id'])->first();

                if ($existingItem) {
                    // Update the existing item
                    $existingItem->update([
                        'quantity' => $item['quantity'],
                        'price' => $product->price, // Update price in case it changed
                    ]);
                } else {
                    // Create a new item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                    ]);
                }

                // Calculate the total cost
                $total += $product->price * $item['quantity'];
            }

            // Update the total cost of the order
            $order->update(['total' => $total]);

            // Commit the transaction
            DB::commit();

            // Eager load the order items relationship
            $order->load('items');

            // Return the updated order with its items
            return new OrderResource($order);

        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while updating the order.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        if ($order->user_id !== Auth::id())
            return response()->json(['message' => 'Unauthorized'], 403);

        $order->items()->delete();
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);

    }
}
