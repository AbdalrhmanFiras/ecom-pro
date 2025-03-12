<?php

namespace App\Http\Controllers\API;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderItemResource;

use App\Models\Order;
use App\Models\Product;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\OrderStoreVaildate;
use App\Http\Requests\UpdateStoreVaildate;
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



            return new OrderResource($order);


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
        $order->load('items');
        return new OrderResource($order);


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStoreVaildate $request, Order $order)
    {
        try {
            // تأكد أن المستخدم المصدق عليه هو صاحب الطلب
            if ($order->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }


            $total = 0;

            // إذا كان الطلب يحتوي على items
            if ($request->has('items') && is_array($request->items)) {
                // حذف العناصر الحالية للطلب
                $order->items()->delete();

                // إضافة العناصر الجديدة
                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) {
                        return response()->json(['message' => 'Product not found for product_id: ' . $item['product_id']], 404);
                    }

                    // إنشاء عنصر جديد
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                    ]);

                    // حساب التكلفة الإجمالية
                    $total += $product->price * $item['quantity'];
                }

                // تحديث التكلفة الإجمالية للطلب
                $order->update(['total' => $total]);
            }

            // إرجاع الطلب المحدث مع العناصر باستخدام OrderResource
            return new OrderResource($order->load('items'));
        } catch (ValidationException $e) {
            // معالجة أخطاء التحقق
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // معالجة الأخطاء العامة
            return response()->json([
                'message' => 'An error occurred while updating the order.',
                'error' => $e->getMessage(),
            ], 500);
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
