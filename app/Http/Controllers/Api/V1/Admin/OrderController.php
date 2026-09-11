<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\OrderItemRequest;
use App\Http\Requests\V1\OrderStatusRequest;
use App\Http\Requests\V1\PaginationRequest;
use App\Http\Resources\V1\OrderItemResource;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Services\OrderEditor;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(PaginationRequest $request)
    {
        return OrderResource::collection(Order::with('orderitems')->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))->orderByDesc('id')->paginate($request->integer('per_page', 12))->withQueryString());
    }

    public function show(Order $order)
    {
        Gate::authorize('update', $order);

        return new OrderResource($order->load('orderitems'));
    }

    public function update(OrderStatusRequest $request, Order $order)
    {
        Gate::authorize('update', $order);
        $order->update($request->validated());

        return new OrderResource($order->load('orderitems'));
    }

    public function updateItem(OrderItemRequest $request, Order $order, int $item, OrderEditor $editor)
    {
        Gate::authorize('update', $order);

        return new OrderItemResource($editor->updateItem($order, $item, $request->validated()));
    }
}
