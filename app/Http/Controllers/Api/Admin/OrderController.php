<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // $orders = Order::all();
        // $orders = Order::with('user')->get();
        $orders = Order::with('user')->latest()->paginate(10);

        return response()->json([
            'orders' => $orders,
        ]);
    }
    public function show(Order $order){
        $order->load([
            'user',
            'orderItems.product',
            ]);
        return response()->json([
            'order' => $order,
        ]);
    }
    public function update(Request $request, Order $order){
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,processing,completed,cancelled'],
        ]);

        $currentStatus = $order->status;
        $newStatus = $validated['status'];

        $allowedTranstions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if(!in_array($newStatus, $allowedTranstions[$currentStatus])){

            return response()->json([
                'message' => "Invalid status transition from '$currentStatus' to '$newStatus'.",
            ], 400);
        };

        $order->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'order' => $order,
        ], 200);
    }
}
