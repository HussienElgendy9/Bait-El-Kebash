<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CheckoutRequest;
use App\Http\Requests\V1\PaginationRequest;
use App\Http\Resources\V1\OrderResource;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(PaginationRequest $request)
    {
        return OrderResource::collection($request->user()->orders()->with('orderitems')->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))->orderByDesc('id')->paginate($request->integer('per_page', 12))->withQueryString());
    }

    public function store(CheckoutRequest $request, CheckoutService $service)
    {
        $order = $service->create($request->user(), $request->validated('items'), $request->validated('idempotency_key'));

        return (new OrderResource($order))->response()->setStatusCode($order->wasRecentlyCreated ? 201 : 200);
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        return new OrderResource($order->load('orderitems'));
    }
}
