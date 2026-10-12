<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // $orders = Order::all();
        // $orders = Order::with('user')->get();
        // $orders = Order::with('user')->latest()->paginate(10);

         $request->validate([
            'status' => ['nullable', 'in:pending,processing,completed,cancelled'],
        ]);
        $query = Order::with([
            'user',
            'orderItems.product',
        ]);
        if($request->status){
            $query->where('status', $request->status);
        }
        if($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
            $q->where('id', $search)
              ->orWhereHas('user', function ($u) use ($search) {
                  $u->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
                });
            });
        }
        $orders = $query->latest()->paginate(10);
        return OrderResource::collection($orders);
        // return response()->json([
        //     'orders' => $orders,
        // ]);
    }
    public function show(Order $order){
        $order->load([
            'user',
            'orderItems.product',
            ]);
            return new OrderResource($order);
        // return response()->json([
        //     'order' => $order,
        // ]);
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
            ], 422);
        };

        $order->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Order status updated successfully.',
            'order' => new OrderResource($order),
        ], 200);
    }
}
