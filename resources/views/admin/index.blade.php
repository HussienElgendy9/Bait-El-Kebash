@extends('layouts.app')

@section('content')
<div class="flex justify-center max-w-7xl mx-auto px-8 py-8">
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">

        <div class="px-6 py-4 border-b">
            <h2 class="text-2xl font-bold text-gray-800">
                Dashboard
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="py-4 min-w-full divide-y divide-gray-200 ">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Users' number
                        </th>

                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Orders' number
                        </th>

                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Categories' number
                        </th>

                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Product's number
                        </th>

                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white ">
                    <tr>
                        <td>{{ $users }}</td>
                        <td>{{ $order }}</td>
                        <td>{{ $category }}</td>
                        <td>{{ $product }}</td>

                    </tr>


                </tbody>

            </table>
        </div>

    </div>

</div>
{{-- <div class="flex justify-center max-w-7xl mx-auto px-8 py-8">
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">

        <div class="px-6 py-4 border-b">
            <h2 class="text-2xl font-bold text-gray-800">
                Category
            </h2>
            <a href="{{ route('admin.categories.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Add</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            ID
                        </th>

                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Name
                        </th>

                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Email
                        </th>

                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Phone
                        </th>

                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">

                    @foreach ($users as $user)
                        <tr class="hover:bg-gray-50 transition duration-200">

                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                {{ $user->id }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                {{ $user->name }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ $user->email }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ $user->phone_number }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">

                                <div class="flex justify-center gap-2">

                                    <a href=""
                                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                        Edit
                                    </a>

                                    <a href=""
                                       class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                        Delete
                                    </a>

                                </div>

                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>
        </div>

    </div>

</div> --}}
@endsection