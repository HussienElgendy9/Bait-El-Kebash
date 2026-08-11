@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-8">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Products</h1>

        <a href="{{ route('admin.products.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg transition">
            + Add Product
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">

        <table class="w-full">

            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Image</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Category</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Price</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Description</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>

            <tbody>

                @forelse($products as $product)

                    <tr class="border-b border-gray-200 hover:bg-gray-50">

                        <td class="px-6 py-4">
                            {{ $product->id }}
                        </td>

                        <td class="px-6 py-4">
                            <img
                                src="{{ $product->image ? Storage::url($product->image) : asset('images/meat.png') }}"
                                alt="{{ $product->name }}"
                                class="w-16 h-16 object-cover rounded-lg border border-gray-300">
                        </td>

                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $product->name }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $product->category->name }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $product->unit_price }} ج.م
                        </td>

                        <td class="px-6 py-4 max-w-xs truncate">
                            {{ $product->description }}
                        </td>

                        <td class="px-6 py-4">

                            <div class="flex justify-center gap-2">

                                <a href="{{ route('admin.products.edit', $product->id) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm">
                                    Edit
                                </a>

                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        onclick="return confirm('Delete this product?')"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm">
                                        Delete
                                    </button>
                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="text-center py-8 text-gray-500">
                            No products found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>
@endsection