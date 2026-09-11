<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //
    public function index(Request $request)
    {
        $orders = $request->user()
            ->order()
            ->with('orderitems.product')
            ->latest()
            ->paginate(12);

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function store(Request $request, CheckoutService $service)
    {
        $validated = $request->validate(CheckoutService::rules() + ['idempotency_key' => ['sometimes', 'string', 'max:100']]);
        $order = $service->create($request->user(), $validated['items'], $validated['idempotency_key'] ?? null);

        return response()->json(['message' => 'Order created successfully.', 'order' => $order->load('orderitems.product')], $order->wasRecentlyCreated ? 201 : 200);
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
