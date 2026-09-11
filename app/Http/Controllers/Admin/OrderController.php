<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $orders = Order::all();
        // $order = Order::with('users')->all(); wrong
        $orders = Order::with('user')->get();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with('user', 'orderitems.product')->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function edit(string $id)
    {
        //
        $order = Order::with('orderitems.product')->findOrFail($id);
        $products = Product::all();

        // dd($order->id, $order->orderitem);

        return view('admin.orders.edit', compact('order', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate(['status' => 'required|in:pending,completed,cancelled']);
        Order::findOrFail($id)->update($data);

        return redirect()->route('admin.orders.index');
    }

    public function updateItem(Request $request, Order $order, int $item, OrderEditor $editor)
    {
        $data = $request->validate(['product_id' => 'required|integer|exists:products,id', 'quantity' => 'required|integer|min:1|max:1000', 'status' => 'required|in:pending,completed,cancelled']);
        DB::transaction(function () use ($order, $item, $data, $editor) {
            $editor->updateItem($order, $item, $data);
            $order->update(['status' => $data['status']]);
        });

        return redirect()->route('admin.orders.index');
    }

    public function destroy(string $id)
    {
        abort(409, 'Order history cannot be deleted.');
    }
}
