@extends('layouts.app')

@section('content')
<div class=" flex justify-center mt-3 p-5">
    <div class="bg-white rounded-lg shadow-md p-8">

        <h1 class="text-2xl font-bold text-gray-800 mb-6">
            Add New Category
        </h1>

        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Product Name -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">
                    Category Name
                </label>
                <input
                    type="text"
                    name="name"
                    class="w-full border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
                    placeholder="Enter product name">
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