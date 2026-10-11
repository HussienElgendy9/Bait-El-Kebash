<?php

namespace App\Http\Controllers\Store;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//chatgpt recommendation
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\Notification;

class CartController extends Controller
{
    private array $cart;
    // private $user;
    public function __construct(){
                // session()->forget('cart');

        $this->cart = session()->get('cart',[]);
        
                // $this->user=Auth::user();

    }
    public function index(){
        $cart = $this->cart;
        return view('store.cart',compact('cart'));
                        // return view('store.cart');

    }
    public function add(Request $request){
        // $this->user=Auth::user();
        // dd($request);
        // session()->forget('cart');
        $request->validate([
            'id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            ]);

        $product = Product::findOrFail($request->id);

        if(isset($this->cart[$product->id])){
                $this->cart[$product->id]['qty']+=$request->qty;
                $this->cart[$product->id]['price']+=($product->unit_price * $request->qty);
        }
        else{
            $this->cart[$product->id]=[
                'id'=>$product->id,
                'name'=>$product->name,
                'category_id'=>$product->category_id,
                'qty'=>$request->qty,
                'unit_price'=>$product->unit_price,
                'price'=>$product->unit_price * $request->qty,
                'image'=>$product->image,
            ];
        }
        session()->put('cart', $this->cart);
        session()->save();
        $this->total_price();
        // dd(session()->all());

        return redirect()->back()->with('success', 'Product added to cart.');
    }
    public function decrease(Request $request){
        $request->validate([
            'id' => 'required|exists:products,id',
            // 'qty' => 'required|integer|min:1',
        ]);
        $product = Product::findOrFail($request->id);

        if(isset($this->cart[$product->id])){
            if($this->cart[$product->id]['qty']>1){
                $this->cart[$product->id]['qty']-=1;
                $this->cart[$product->id]['price']=($product->unit_price * $this->cart[$product->id]['qty']);
                }
        }
        session()->put('cart', $this->cart);
        $this->total_price();

        return redirect()->back();
    }
    public function remove(Request $request){
        $request->validate([
            'id' => 'required|exists:products,id',
            // 'qty' => 'required|integer|min:1',
        ]);
        unset($this->cart[$request->id]);

        session()->put('cart', $this->cart);

        $this->total_price();

        return redirect()->back();
    }
    public function clear(Request $request){
        session()->forget('cart');
        return redirect()->back();

    }
    private function total_price(){
        // $count = count(session('cart',[]));
        $price=0;
        foreach (session('cart') as $cart){
            $price +=$cart['price'];
        }
        session()->put('total_price', $price);

    }

    public function checkout(){
        if (empty($this->cart)) {
        return redirect()->back()->with('error', 'Your cart is empty.');
    }
        $order = DB::transaction(function () {

        $order = Order::create([
            'user_id' => Auth::id(),
            'status'  => 'pending',
        ]);

        $this->order($order->id);
        return $order;

        });
        session()->forget('cart');
        session()->forget('total_price');

        $order->load(['user', 'orderItems.product']);

$admins = User::where('role', 'admin')->get();

Notification::send($admins, new NewOrderNotification($order));

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
                'unit_price' => $item['unit_price'],

                // 'product_id'=>$this->$item['id'],
                // 'quantity'=>$this->$item['qty'],
                // 'price_snapshot'=>$this->$item['price'],
            ]);
        }
    }
}
