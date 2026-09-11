<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
<<<<<<< Updated upstream
use Illuminate\Http\Request;
//chatgpt recommendation
use Illuminate\Support\Facades\DB;
=======
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
// chatgpt recommendation
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
>>>>>>> Stashed changes

class CartController extends Controller
{
    private array $cart;

    // private $user;
    public function __construct()
    {
        // session()->forget('cart');

        $this->cart = session()->get('cart', []);

        // $this->user=Auth::user();

    }

    public function index()
    {
        $this->cart = session('cart', []);
        $cart = $this->cart;

        return view('store.cart', compact('cart'));
        // return view('store.cart');

    }

    public function add(Request $request)
    {
        $this->cart = session('cart', []);
        session()->forget('checkout_key');
        // $this->user=Auth::user();
        // dd($request);
        // session()->forget('cart');
        $request->validate([
            'id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1|max:1000',
        ]);

        $product = Product::findOrFail($request->id);

        if (($this->cart[$product->id]['qty'] ?? 0) + $request->integer('qty') > 1000 || (! isset($this->cart[$product->id]) && count($this->cart) >= 100)) {
            throw ValidationException::withMessages(['qty' => ['Cart limit exceeded.']]);
        }

        if (isset($this->cart[$product->id])) {
            $this->cart[$product->id]['qty'] += $request->qty;
            $this->cart[$product->id]['price'] += ($product->unit_price * $request->qty);
        } else {
            $this->cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'qty' => $request->qty,
                'unit_price' => $product->unit_price,
                'price' => $product->unit_price * $request->qty,
                'image' => $product->image,
            ];
        }
        session()->put('cart', $this->cart);
        session()->save();
        $this->total_price();
        // dd(session()->all());

        return redirect()->back()->with('success', 'Product added to cart.');
    }

    public function decrease(Request $request)
    {
        $this->cart = session('cart', []);
        session()->forget('checkout_key');
        $request->validate([
            'id' => 'required|exists:products,id',
            // 'qty' => 'required|integer|min:1',
        ]);
        $product = Product::findOrFail($request->id);

        if (isset($this->cart[$product->id])) {
            if ($this->cart[$product->id]['qty'] > 1) {
                $this->cart[$product->id]['qty'] -= 1;
                $this->cart[$product->id]['price'] = ($product->unit_price * $this->cart[$product->id]['qty']);
            }
        }
        session()->put('cart', $this->cart);
        $this->total_price();

        return redirect()->back();
    }

    public function remove(Request $request)
    {
        $this->cart = session('cart', []);
        session()->forget('checkout_key');
        $request->validate([
            'id' => 'required|exists:products,id',
            // 'qty' => 'required|integer|min:1',
        ]);
        unset($this->cart[$request->id]);

        session()->put('cart', $this->cart);

        $this->total_price();

        return redirect()->back();
    }

    public function clear(Request $request)
    {
        session()->forget(['cart', 'total_price', 'checkout_key']);

        return redirect()->back();

    }

    private function total_price()
    {
        // $count = count(session('cart',[]));
        $price = 0;
        foreach (session('cart') as $cart) {
            $price += $cart['price'];
        }
        session()->put('total_price', $price);

    }

<<<<<<< Updated upstream
    public function checkout(){
        if (empty($this->cart)) {
        return redirect()->back()->with('error', 'Your cart is empty.');
    }
        DB::transaction(function () {

        $order = Order::create([
            'user_id' => Auth::id(),
            'status'  => 'pending',
        ]);

        $this->order($order->id);

        });
        session()->forget('cart');
        session()->forget('total_price');

        return redirect()->route('store.home')
        ->with('success', 'Order placed successfully.');
    }
    private function order($order_id){
        foreach ($this->cart as $item){
            OrderItem::create([
                'order_id'=>$order_id,
                'product_id'=>$item['id'],
                'quantity'=>$item['qty'],
                'price_snapshot'=>$item['price'],
                // 'product_id'=>$this->$item['id'],
                // 'quantity'=>$this->$item['qty'],
                // 'price_snapshot'=>$this->$item['price'],
            ]);
=======
    public function checkout(CheckoutService $service)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->back()->with('error', 'Your cart is empty.');
>>>>>>> Stashed changes
        }
        $key = session('checkout_key', (string) Str::uuid());
        session()->put('checkout_key', $key);
        session()->save();
        $items = array_values(array_map(fn ($item) => ['product_id' => $item['id'], 'quantity' => $item['qty']], $cart));
        $service->create(Auth::user(), $items, $key);
        session()->forget(['cart', 'total_price', 'checkout_key']);

        return redirect()->route('store.home')->with('success', 'Order placed successfully.');
    }
}
