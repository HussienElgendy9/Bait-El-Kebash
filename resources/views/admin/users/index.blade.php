@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-8">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Products</h1>

        {{-- <a href="{{ route('admin.products.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg transition">
            + Add Product
        </a> --}}
    </div>

    <div class="overflow-x-auto bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">

        <table class="w-full">

            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Phone Number</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Address</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>

            <tbody>

                @forelse($users as $user)

                    <tr class="border-b border-gray-200 hover:bg-gray-50">

                        <td class="px-6 py-4">
                            {{ $user->id }}
                        </td>

                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $user->name }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $user->email }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $user->phone_number }}
                        </td>

                        <td class="px-6 py-4 max-w-xs truncate">
                            {{ $user->address }}
                        </td>

                        <td class="px-6 py-4">

                            <div class="flex justify-center gap-2">

                                <a href="{{ route('admin.users.show', $user->id) }}"
                                   class="bg-green-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm">
                                    View
                                </a>
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm">
                                    Edit
                                </a>

                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
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