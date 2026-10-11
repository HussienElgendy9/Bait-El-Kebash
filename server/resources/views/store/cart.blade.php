@extends('layouts.app')

@section('content')
<div class="bg-gradient-to-b from-brand-beige via-brand-offwhite to-brand-green">
    <table class="  w-full border-collapse border-2  border-brand-green ">
        <thead class="">
            <tr>
                <th class="border-2  border-brand-green p-2">Image</th>
                <th class="border-2  border-brand-green p-2">Product</th>
                <th class="border-2  border-brand-green p-2">Category ID</th>
                <th class="border-2  border-brand-green p-2">Quantity</th>
                <th class="border-2  border-brand-green p-2">Price</th>
                <th class="border-2  border-brand-green p-2">Actions</th>
            </tr>
        </thead>
    
        <tbody>
            @forelse($cart as $item)
                <tr>
                    <td class="border-2  border-brand-green p-2">
                        <img src="{{ asset('storage/' . $item['image']) }}"
                             class="w-20 h-20 object-cover">
                    </td>
    
                    <td class="border-2  border-brand-green p-2">
                        {{ $item['name'] }}
                    </td>
    
                    <td class="border-2  border-brand-green p-2">
                        {{ $item['category_id'] }}
                    </td>
    
                    <td class="border-2  border-brand-green p-2">
                        {{ $item['qty'] }}
                    </td>
    
                    <td class="border-2  border-brand-green p-2">
                        {{ number_format($item['price'], 2) }} EGP
                    </td>
                    <td class="border-2  border-brand-green p-2">
                        <form action="{{ route('cart.remove') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $item['id'] }}">
                            
                            <button type="submit">Remove</button>
                        </form>
    
                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $item['id'] }}">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit">Inc</button>
                        </form>
    
                        <form action="{{ route('cart.decrease') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $item['id'] }}">
                            <button type="submit">Dec</button>
                        </form>
                    </td>
                </tr>
                
            @empty
                <tr>
                    <td colspan="5" class="text-center p-4">
                        Your cart is empty.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
                <tr class=" font-bold">
                    <td colspan="4" class="border-2  border-brand-green p-3 text-right">
                        Total Price
                    </td>
                    <td class="border-2  border-brand-green p-3">
                        {{ session('total_price') }} EGP
                    </td>
                    <td class="border-2  border-brand-green p-3">
                        <form action="{{ route('cart.checkout') }}" method="POST">
                            @csrf
                            <button type="submit">Confirm Order</button>
                    </form>
                    </td>
                </tr>
            </tfoot>
    </table>

</div>
@endsection