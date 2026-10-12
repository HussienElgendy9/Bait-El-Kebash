<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    //
    public function index(Request $request){
        $orders = $request->user()
        ->orders()
        ->with('orderItems.product')
        ->latest()
        ->paginate(12);

        //  return response()->json([
        //     'orders' => $orders,
        // ]);
        return OrderResource::collection($orders);
    }
    public function store(Request $request){
        $validated = $request->validate([
            'items' => ['required','array','min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')->where('is_available', true),],
            'items.*.quantity'=>['required','integer','min:1','max:100'],
        ]);
        $order = DB::transaction(function () use ($validated, $request) {

                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'status' => 'pending',
                    'address' => $request->user()->address,
                    'phone_number' =>$request->user()->phone_number,
                ]);
                
                $ids = collect($validated['items'])->pluck('product_id');

                $products = Product::findMany($ids)->keyBy('id');
                
                foreach ($validated['items'] as $item) {

                    // $product = Product::findOrFail($item['product_id']);
                    $product = $products[$item['product_id']];

                    $quantity = $item['quantity'];
                    $unitPrice = $product->unit_price;
                    $priceSnapshot = $unitPrice * $quantity;

                    $order->orderItems()->create([
                        'product_id' => $product->id,
                        'unit_price' => $unitPrice,
                        'quantity' => $quantity,
                        'price_snapshot' => $priceSnapshot,
                    ]);
                }

                return $order;
            });

            $order->load('user','orderItems.product');

            $admins = User::whereIn('role', ['admin', 'super_admin'])->get();

            foreach ($admins as $admin) {
                $admin->notify(new NewOrderNotification($order));
            }

            return response()->json([
                'message' => 'Order created successfully.',
                'order' => new OrderResource($order),
                // 'order' => $order,
            ], 201);
    }
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $order->load('orderItems.product');

        // return response()->json([
        //     'order' => $order,
        // ]);
        return new OrderResource($order);
    }
    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending orders can be canceled.',
            ], 422);
        }

        $order->update(['status' => 'cancelled']);

        $order->load('orderItems.product');
        
        return response()->json([
            'message' => 'Order canceled successfully.',
            'order' => new OrderResource($order),
        ], 200);
    }
}