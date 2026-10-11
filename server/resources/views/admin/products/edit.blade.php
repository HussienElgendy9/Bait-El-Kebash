@extends('layouts.app')

@section('content')
<div class=" flex justify-center mt-3 p-5">
    <div class="bg-white rounded-lg shadow-md p-8">

        <h1 class="text-2xl font-bold text-gray-800 mb-6">
            Edit Product no.{{ $product->id }}
        </h1>

        <form action="{{ route('admin.products.update',$product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <!-- Product Name -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Product Name
                </label>
                <input
                    type="text"
                    name="name"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                    placeholder="Enter product name"
                    value="{{ $product->name }}">
            </div>

            <!-- Category -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Category
                </label>
                <select
                    name="cat_id"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">

                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">
                            {{ $category->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <!-- Price -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Unit Price
                </label>
                <input
                    type="number"
                    name="unit_price"
                    step="0.01"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                    placeholder="0.00"
                    value="{{ $product->unit_price }}">
            </div>

            <!-- Description -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Description
                </label>
                <textarea
                    name="description"
                    rows="4"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                    placeholder="Write a description...">{{ $product->description }}</textarea>
            </div>

            <!-- Image -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Image
                </label>
                <input type="file" name="image" >
            </div>

            <!-- Submit -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full bg-red-600 text-white py-2.5 rounded-md font-medium hover:bg-red-700 transition">
                    Save Product
                </button>
            </div>

        </form>

    </div>
</div>
@endsection