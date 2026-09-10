@extends('layouts.app')

@section('content')
<div class="flex justify-center max-w-7xl mx-auto px-8 py-8">
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">

<<<<<<< Updated upstream
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
=======
<div class="min-h-screen bg-[#fcf9f8] text-[#1b1c1c]">

    <div class="fixed inset-0 -z-10 opacity-[0.04]"
         style="background-image: radial-gradient(#004225 1px, transparent 1px); background-size: 24px 24px;">
    </div>

    <div class="mx-auto flex min-h-screen w-full max-w-[1600px]">

       
>>>>>>> Stashed changes

                    </tr>

<<<<<<< Updated upstream

                </tbody>

            </table>
        </div>
=======
        {{-- MAIN CONTENT --}}
        <main class="min-w-0 flex-1 p-5 lg:p-10">

            <header class="relative mb-10 flex flex-col justify-between gap-6 border-b border-[#e4e2e1] pb-6 md:flex-row md:items-end">

                <div class="absolute bottom-0 right-0 h-px w-32 bg-[#004225]"></div>

                <div>
                    <p class="text-sm font-bold tracking-wider text-[#8b5e3c]">
                        بيت الكباش
                    </p>

                    <h1 class="mt-2 text-4xl font-bold text-[#002a15] lg:text-5xl">
                        نظرة عامة
                    </h1>

                    <p class="mt-3 text-gray-500">
                        تابع أداء المتجر والطلبات والمنتجات من مكان واحد.
                    </p>
                </div>
            </header>


            {{-- STATISTICS --}}
            <section class="mb-10 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">

                <div class="rounded-[24px] bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#ece2c7] text-[#004225]">
                        <i class="fa-solid fa-users text-lg"></i>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm font-bold text-gray-500">إجمالي المستخدمين</p>
                        <p class="mt-2 text-4xl font-bold text-[#002a15]">
                            {{ number_format($users) }}
                        </p>
                    </div>
                </div>


                <div class="rounded-[24px] bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f4bb92] text-[#543012]">
                        <i class="fa-solid fa-receipt text-lg"></i>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm font-bold text-gray-500">إجمالي الطلبات</p>
                        <p class="mt-2 text-4xl font-bold text-[#002a15]">
                            {{ number_format($orders) }}
                        </p>
                    </div>
                </div>

                {{-- COMPLETED --}}
                <div class="rounded-[24px] bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#b4f0c7]/50 text-[#004225]">
                        <i class="fa-solid fa-circle-check text-lg"></i>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm font-bold text-gray-500">
                            الطلبات المكتملة
                        </p>

                        <p class="mt-2 text-4xl font-bold text-[#002a15]">
                            {{ number_format($completedOrders) }}
                        </p>
                    </div>
                </div>

                {{-- PENDING --}}
                <div class="rounded-[24px] bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#f4bb92] text-[#543012]">
                        <i class="fa-solid fa-clock text-lg"></i>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm font-bold text-gray-500">
                            الطلبات قيد الانتظار
                        </p>

                        <p class="mt-2 text-4xl font-bold text-[#002a15]">
                            {{ number_format($pendingOrders) }}
                        </p>
                    </div>
                </div>


                {{-- CANCELLED --}}
                <div class="rounded-[24px] bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-sm font-bold text-gray-500">
                                الطلبات الملغية
                            </p>

                            <p class="mt-3 text-4xl font-bold text-red-600">
                                {{ number_format($cancelledOrders) }}
                            </p>
                        </div>

                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 text-xl text-red-600">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>

                    </div>
                </div>


                <div class="rounded-[24px] bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#b4f0c7]/50 text-[#004225]">
                        <i class="fa-solid fa-box text-lg"></i>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm font-bold text-gray-500">إجمالي المنتجات</p>
                        <p class="mt-2 text-4xl font-bold text-[#002a15]">
                            {{ number_format($products) }}
                        </p>
                    </div>
                </div>


                <div class="rounded-[24px] bg-[#002a15] p-6 text-white shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#004225] text-[#b4f0c7]">
                        <i class="fa-solid fa-layer-group text-lg"></i>
                    </div>

                    <div class="mt-6">
                        <p class="text-sm font-bold text-[#98d4ac]">إجمالي الأقسام</p>
                        <p class="mt-2 text-4xl font-bold">
                            {{ number_format($categories) }}
                        </p>
                    </div>
                </div>

            </section>


            {{-- CHARTS --}}
            <section class="mb-10 grid grid-cols-1 gap-6 xl:grid-cols-3">

                {{-- SALES --}}
                <div class="overflow-hidden rounded-[32px] bg-white p-6 shadow-sm lg:p-8 xl:col-span-2">

                    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

                        <div>
                            <h2 class="text-3xl font-bold text-[#002a15]">
                                المبيعات
                            </h2>

                            <p class="mt-2 text-sm text-gray-500">
                                إجمالي المبيعات خلال آخر 6 شهور.
                            </p>
                        </div>

                        <div class="rounded-full bg-[#f0eded] px-4 py-2 text-sm font-bold text-[#004225]">
                            آخر 6 شهور
                        </div>
                    </div>

                    <div class="relative h-[350px] w-full">
                        <canvas id="salesChart"></canvas>
                    </div>

                </div>


                {{-- CATEGORIES --}}
                <div class="relative overflow-hidden rounded-[32px] bg-[#ece2c7] p-6 shadow-sm lg:p-8">

                    <div class="relative">
                        <h2 class="text-3xl font-bold text-[#002a15]">
                            الأقسام
                        </h2>

                        <p class="mt-2 text-sm text-[#004225]/70">
                            توزيع المنتجات حسب القسم.
                        </p>
                    </div>

                    <div class="relative mx-auto mt-8 h-[280px] max-w-[280px]">
                        <canvas id="categoryChart"></canvas>
                    </div>

                </div>

            </section>


            {{-- QUICK OVERVIEW --}}
            <section class="overflow-hidden rounded-[32px] bg-white shadow-sm">

                <div class="border-b border-[#e4e2e1] p-6 lg:p-8">
                    <h2 class="text-3xl font-bold text-[#002a15]">
                        ملخص المتجر
                    </h2>

                    <p class="mt-2 text-gray-500">
                        نظرة سريعة على بيانات بيت الكباش.
                    </p>
                </div>

                <div class="grid grid-cols-1 divide-y divide-[#e4e2e1] md:grid-cols-2 md:divide-x md:divide-y-0">

                    <div class="p-6 lg:p-8">
                        <p class="text-sm text-gray-500">
                            متوسط المنتجات لكل قسم
                        </p>

                        <p class="mt-3 text-3xl font-bold text-[#004225]">
                            @if($categories > 0)
                                {{ number_format($products / $categories, 1) }}
                            @else
                                0
                            @endif
                        </p>
                    </div>

                    <div class="p-6 lg:p-8">
                        <p class="text-sm text-gray-500">
                            إجمالي المنتجات المتاحة
                        </p>

                        <p class="mt-3 text-3xl font-bold text-[#004225]">
                            {{ number_format($products) }}
                        </p>
                    </div>

                </div>

            </section>

        </main>
>>>>>>> Stashed changes

    </div>

</div>
<<<<<<< Updated upstream
{{-- <div class="flex justify-center max-w-7xl mx-auto px-8 py-8">
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
=======


{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    console.log('Dashboard script started');

    // Check Chart.js
    console.log('Chart:', typeof Chart);

    // PHP data
    const salesLabels = @json($salesLabels);
    const salesData = @json($salesData);
    const pendingData = @json($pendingOrders);
    const cancelledData = @json($cancelledOrders);
    const categoryLabels = @json($categoryLabels);
    const categoryData = @json($categoryData);

    console.log({
        salesLabels,
        salesData,
        categoryLabels,
        categoryData
    });

    // Get canvases
    const salesCanvas = document.getElementById('salesChart');
    const categoryCanvas = document.getElementById('categoryChart');

    console.log('Sales canvas:', salesCanvas);
    console.log('Category canvas:', categoryCanvas);

    // SALES CHART
    if (salesCanvas && typeof Chart !== 'undefined') {

        new Chart(salesCanvas, {
            type: 'bar',

            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'المبيعات بالجنيه',
                    data: salesData,
                    backgroundColor: '#004225',
                    borderColor: '#002a15',
                    borderWidth: 2,
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        console.log('Sales chart created');

    } else {
        console.error('Could not create sales chart');
    }


    // CATEGORY CHART
    if (categoryCanvas && typeof Chart !== 'undefined') {

        new Chart(categoryCanvas, {
            type: 'doughnut',

            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryData,

                    backgroundColor: [
                        '#004225',
                        '#8b5e3c',
                        '#98d4ac',
                        '#f4bb92',
                        '#002a15',
                        '#d4c5a1'
                    ],

                    borderWidth: 3,
                    borderColor: '#ece2c7'
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        console.log('Category chart created');

    } else {
        console.error('Could not create category chart');
    }

});
</script>

>>>>>>> Stashed changes

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