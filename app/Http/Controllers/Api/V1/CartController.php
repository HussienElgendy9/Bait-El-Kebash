<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CartItemRequest;
use App\Http\Resources\V1\CartResource;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Services\Money;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::with('category')->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $total = 0;
        $available = true;
        $items = [];
        foreach ($cart as $id => $line) {
            $product = $products->get($id);
            $cents = $product ? Money::cents($product->unit_price) * (int) $line['qty'] : null;
            $available = $available && $product !== null;
            $total += $cents ?? 0;
            $items[] = ['product_id' => (int) $id, 'product' => $product ? new ProductResource($product) : null, 'quantity' => (int) $line['qty'], 'available' => $product !== null, 'line_total' => $cents === null ? null : Money::decimal($cents)];
        }

        return new CartResource(['items' => $items, 'total' => $available ? Money::decimal($total) : null]);
    }

    public function store(CartItemRequest $request)
    {
        $product = Product::findOrFail($request->integer('product_id'));
        $quantity = ($request->session()->get('cart', [])[$product->id]['qty'] ?? 0) + $request->integer('quantity');

        return $this->save($request, $product, $quantity);
    }

    public function update(CartItemRequest $request, Product $product)
    {
        return $this->save($request, $product, $request->integer('quantity'));
    }

    private function save(Request $request, Product $product, int $quantity)
    {
        $cart = $request->session()->get('cart', []);
        if ($quantity > 1000 || (! isset($cart[$product->id]) && count($cart) >= 100)) {
            throw ValidationException::withMessages(['quantity' => ['Cart limits are 100 products and 1000 units per product.']]);
        }
        $cart[$product->id] = ['id' => $product->id, 'name' => $product->name, 'category_id' => $product->category_id, 'qty' => $quantity, 'unit_price' => $product->unit_price, 'price' => Money::decimal(Money::cents($product->unit_price) * $quantity), 'image' => $product->image];
        $request->session()->put('cart', $cart);
        $request->session()->forget('checkout_key');

        return $this->show($request);
    }

    public function destroyItem(Request $request, int $product)
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product]);
        $request->session()->put('cart', $cart);
        $request->session()->forget('checkout_key');

        return $this->show($request);
    }

    public function destroy(Request $request)
    {
        $request->session()->forget(['cart', 'total_price', 'checkout_key']);

        return response()->noContent();
    }
}
