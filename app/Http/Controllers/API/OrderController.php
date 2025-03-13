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
use App\Notifications\OrderShippedNotification;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{




    public function markAsDelivered(Request $request, Order $order)
    {
        // Ensure the order belongs to the authenticated user (or admin)
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate the request data
        try {
            $request->validate([
                'delivery_confirmation' => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        // Ensure the order is in SHIPPED status before marking as DELIVERED
        if ($order->status !== OrderStatus::SHIPPED) {
            return response()->json([
                'message' => 'Order cannot be marked as delivered because it is not in the shipped state.',
            ], 400);
        }

        // Start a transaction
        DB::beginTransaction();

        try {
            // Update the status to DELIVERED
            $order->update([
                'status' => OrderStatus::DELIVERED->value,
                'delivery_confirmation' => $request->delivery_confirmation,
            ]);

            // Log the status change
            Log::info('Order delivered', [
                'order_id' => $order->id,
                'status' => OrderStatus::DELIVERED->value,
                'delivery_confirmation' => $request->delivery_confirmation,
            ]);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Order status updated to DELIVERED.',
                'order' => new OrderResource($order->load('items')),
            ]);
        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollBack();

            // Log the error
            Log::error('Failed to mark order as delivered', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred while updating the order status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    public function markAsShipped(Request $request, Order $order)
    {




        // Log order details


        // Ensure the order belongs to the authenticated user (or admin)
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        \Log::info('Checking Order Status:', [
            'expected' => OrderStatus::PROCESSING->value,
            'actual' => $order->status,
            'order_id' => $order->id
        ]);
        // Validate the request data (e.g., tracking information)
        try {
            $request->validate([
                'tracking_number' => 'required|string|max:255',
                'carrier' => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
        $order->refresh();

        // Ensure the order is in a state that can be shipped
        if ($order->status !== OrderStatus::PROCESSING) {
            return response()->json([
                'message' => 'Order cannot be confirmed because it is not in the pending state.',
            ], 400);
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the status to SHIPPED and add tracking information
            $order->update([
                'status' => OrderStatus::SHIPPED->value,
                'tracking_number' => $request->tracking_number,
                'carrier' => $request->carrier,
            ]);

            // Log the status change
            Log::info('Order shipped', [
                'order_id' => $order->id,
                'status' => OrderStatus::SHIPPED->value,
                'tracking_number' => $request->tracking_number,
                'carrier' => $request->carrier,
            ]);

            // Send notifications to the customer and admin
            // Optionally, notify the admin as well
            // Admin::first()->notify(new OrderShippedNotification($order));

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Order status updated to SHIPPED.',
                'order' => new OrderResource($order->load('items')),
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            // Log the error
            Log::error('Failed to ship order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred while updating the order status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
















    public function confirmOrder(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Log order details
        \Log::info('Checking Order Status:', [
            'expected' => OrderStatus::PENDING->value,
            'actual' => $order->status,
            'order_id' => $order->id
        ]);

        // Refresh to get the latest order status
        $order->refresh();

        if ($order->status !== OrderStatus::PENDING) {
            return response()->json([
                'message' => 'Order cannot be confirmed because it is not in the pending state.',
            ], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id); // Use product_id instead of $item->product

                if (!$product) {
                    DB::rollBack();
                    return response()->json(['message' => 'Product not found.'], 400);
                }

                if ($product->stock < $item->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Product out of stock: ' . $product->name,
                    ], 400);
                }

                // Reduce stock
                $product->decrement('stock', $item->quantity);
            }

            // Process the payment
            $paymentSuccess = $this->processPayment($order);

            if (!$paymentSuccess) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Payment failed.',
                ], 400);
            }

            // Update the order status
            $order->update(['status' => OrderStatus::PROCESSING->value]);

            DB::commit();

            return response()->json([
                'message' => 'Order confirmed and status updated to PROCESSING.',
                'order' => new OrderResource($order->load('items')),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while confirming the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    private function processPayment(Order $order): bool
    {
        return true;
    }





























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
