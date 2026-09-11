<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    //
    public function index(Request $request){
        $orders = $request->user()
        ->order()
        ->with('orderitems.product')
        ->latest()
        ->paginate(12);

         return response()->json([
            'orders' => $orders,
        ]);
    }
    public function store(Request $request){
        $validated = $request->validate([
            'items' => ['required','array','min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'=>['required','integer','min:1'],
        ]);
        $order = DB::transaction(function () use ($validated, $request) {

                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'status' => 'pending',
                ]);

                foreach ($validated['items'] as $item) {

                    $product = Product::findOrFail($item['product_id']);

                    $quantity = $item['quantity'];
                    $unitPrice = $product->unit_price;
                    $priceSnapshot = $unitPrice * $quantity;

                    $order->orderitems()->create([
                        'product_id' => $product->id,
                        'unit_price' => $unitPrice,
                        'quantity' => $quantity,
                        'price_snapshot' => $priceSnapshot,
                    ]);
                }

                return $order;
            });

            $order->load('orderitems.product');

            return response()->json([
                'message' => 'Order created successfully.',
                'order' => $order,
            ], 201);
    }
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $order->load('orderitems.product');

        return response()->json([
            'order' => $order,
        ]);
    }
}