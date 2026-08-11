@extends('layouts.app')

@section('content')
<div class=" flex  items-center mt-3 p-5">
    @foreach ($order->orderitems as $item)
    <div class="bg-white  items-center rounded-lg shadow-md p-8">
        {{-- {{ $order->orderitem->count() }} --}}
                {{-- <h1>HELLO</h1> --}}

        {{-- {{ $order->orderitem->count() }} --}}
        {{-- <h1 class="text-2xl font-bold text-gray-800 mb-6">
            
            {{ $order->orderitem->count() }}
            Edit Product no.{{ $item->product->name }}
        </h1> --}}
<h2>Order Item ID: {{ $item->id }}</h2>
<h3>Product ID: {{ $item->product_id }}</h3>
<h3>Product: {{ $item->product->name }}</h3>
<hr>
        <form action="{{ route('admin.orders.update',$item->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <!-- Product Name -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Product Name
                </label>
                <select
                    name="product_id"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">

                    @foreach($products as $product)
                        
                        <option value="{{ $product->id }}"
                            {{ $product->id == $item->product_id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <!-- Category -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Status
                </label>
                @php
                    $statuses = ['pending', 'completed', 'cancelled'];
                @endphp
                <select
                    name="status"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                    
                    @foreach($statuses as $status)
                    {{-- learn wt tis does --}}
                        <option value="{{ $status }}"
                            {{ $order->status == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach

                </select>
            </div>

            <!-- Quantity -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Quantity
                </label>
                <input
                    type="number"
                    name="quantity"
                    step="0.01"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                    placeholder="0.00"
                    value="{{ $item->quantity }}">
            </div>
            {{-- Unit Price --}}
            {{-- <input type="number" name="unit_price" value="{{ $item->unit_price }}" readonly hidden > --}}
            {{-- <input type="number" name="order_id" value="{{ $order->id }}" readonly hidden > --}}
            {{-- Total Price --}}
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    {{ $item->price_snapshot }}:Total Price
                </label>
            </div>

            <!-- Submit -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full bg-red-600 text-white py-2.5 rounded-md font-medium hover:bg-red-700 transition">
                    Order edited
                </button>
            </div>

        </form>
    </div>
    @endforeach
</div>
@endsection