<?php

namespace App\Http\Controllers\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        return view('admin.orders.index',compact('orders'));
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
        $order = Order::with([
            'user',
            'orderitem',
            ])->findOrFail($id);
                dd($order->id, $order->orderitem);

        return view('admin.orders.show',compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $order = Order::with('orderitems.product')->findOrFail($id);
            $products = Product::all();
            
            // dd($order->id, $order->orderitem);

        return view('admin.orders.edit',compact('order','products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            // 'order_id'=> 'required|exists:orders,id',
            'product_id'=> 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:pending,completed,cancelled',
        ]);
 
        $item=OrderItem::findOrFail($id);
        $order_id=$item->order->id;
        $order = Order::with('orderitems')->findOrFail($order_id);
        $order->update([
            'status'=>$request->status,
        ]);

        $product = Product::findOrFail($request->product_id);

        $item = OrderItem::findOrFail($id);
        $product = Product::findOrFail($request->product_id);

        if ($item->product_id == $product->id) {
            // Same product → keep the original unit price
            $unitPrice = $item->unit_price;
        } else {
            // Different product → use the new product's current price
            $unitPrice = $product->unit_price;
        }

        $item->update([
            'product_id'=>$product->id,
            'quantity'=>$request->quantity,
            'unit_price'=>$unitPrice,
            'price_snapshot'=>$unitPrice*$request->quantity,
        ]);
        $item->refresh();
        return redirect()->route('admin.orders.index');
    }


    //this update function makes u change the product, the quantity and the status
    // public function update(Request $request, string $id)
    // {
    //     $request->validate([
    //         // 'order_id'=> 'required|exists:orders,id',
    //         'product_id'=> 'required|exists:products,id',
    //         'quantity' => 'required|integer|min:1',
    //         'status' => 'required|in:pending,completed,cancelled',
    //     ]);
 
    //     $item=OrderItem::findOrFail($id);
    //     $order_id=$item->order->id;
    //     $order = Order::with('orderitems')->findOrFail($order_id);
    //     $order->update([
    //         'status'=>$request->status,
    //     ]);

    //     $product = Product::findOrFail($request->product_id);

    //     $item = OrderItem::findOrFail($id);
    //     $product = Product::findOrFail($request->product_id);

    //     if ($item->product_id == $product->id) {
    //         // Same product → keep the original unit price
    //         $unitPrice = $item->unit_price;
    //     } else {
    //         // Different product → use the new product's current price
    //         $unitPrice = $product->unit_price;
    //     }

    //     $item->update([
    //         'product_id'=>$product->id,
    //         'quantity'=>$request->quantity,
    //         'unit_price'=>$unitPrice,
    //         'price_snapshot'=>$unitPrice*$request->quantity,
    //     ]);
    //     $item->refresh();
    //     return redirect()->route('admin.orders.index');
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
