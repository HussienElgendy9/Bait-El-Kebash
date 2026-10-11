@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-[#fcf9f8] p-5 text-[#1b1c1c] lg:p-10">

    <div class="mx-auto max-w-[1400px]">

        {{-- HEADER --}}
        <div class="mb-10 flex flex-col justify-between gap-5 border-b border-[#e4e2e1] pb-6 md:flex-row md:items-end">

            <div>
                <p class="text-sm font-bold tracking-wider text-[#8b5e3c]">
                    بيت الكباش
                </p>

                <h1 class="mt-2 text-4xl font-bold text-[#002a15]">
                    المنتجات
                </h1>

                <p class="mt-3 text-gray-500">
                    إدارة منتجات متجر بيت الكباش.
                </p>
            </div>


            <a href="{{ route('admin.products.create') }}"
               class="flex items-center justify-center gap-2 rounded-xl bg-[#002a15] px-6 py-3 font-bold text-white transition hover:bg-[#004225]">

                <i class="fa-solid fa-plus"></i>

                إضافة منتج جديد
            </a>

        </div>


        {{-- TABLE --}}
        <div class="overflow-hidden rounded-[24px] bg-white shadow-sm">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[900px]">

                    {{-- TABLE HEADER --}}
                    <thead>

                        <tr class="border-b border-[#e4e2e1] bg-[#f6f3f2]">

                            <th class="px-6 py-5 text-right text-sm font-bold text-gray-500">
                                المنتج
                            </th>

                            <th class="px-6 py-5 text-right text-sm font-bold text-gray-500">
                                القسم
                            </th>

                            <th class="px-6 py-5 text-right text-sm font-bold text-gray-500">
                                السعر
                            </th>

                            <th class="px-6 py-5 text-right text-sm font-bold text-gray-500">
                                الوصف
                            </th>

                            <th class="px-6 py-5 text-center text-sm font-bold text-gray-500">
                                الإجراءات
                            </th>

                        </tr>

                    </thead>


                    {{-- TABLE BODY --}}
                    <tbody>

                        @forelse($products as $product)

                            <tr class="group border-b border-[#e4e2e1]/60 transition hover:bg-[#fcf9f8]">

                                {{-- PRODUCT --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center gap-4">

                                        <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-[#e4e2e1]">

                                            <img
                                                src="{{ $product->image
                                                    ? Storage::url($product->image)
                                                    : asset('images/meat.png') }}"
                                                alt="{{ $product->name }}"
                                                class="h-full w-full object-cover"
                                            >

                                        </div>


                                        <div>

                                            <p class="font-bold text-[#002a15]">
                                                {{ $product->name }}
                                            </p>

                                            <p class="mt-1 text-xs text-gray-400">
                                                #{{ $product->id }}
                                            </p>

                                        </div>

                                    </div>

                                </td>


                                {{-- CATEGORY --}}
                                <td class="px-6 py-5">

                                    <span class="inline-flex rounded-full bg-[#ece2c7] px-3 py-1 text-sm font-bold text-[#655e49]">

                                        {{ $product->category->name ?? 'بدون قسم' }}

                                    </span>

                                </td>


                                {{-- PRICE --}}
                                <td class="px-6 py-5">

                                    <p class="font-bold text-[#002a15]">

                                        {{ number_format($product->unit_price, 2) }}

                                        <span class="text-sm font-normal text-gray-500">
                                            ج.م
                                        </span>

                                    </p>

                                    <p class="mt-1 text-xs text-gray-400">
                                        لكل {{ $product->unit }}
                                    </p>

                                </td>


                                {{-- DESCRIPTION --}}
                                <td class="max-w-xs px-6 py-5">

                                    <p class="line-clamp-2 text-sm text-gray-500">

                                        {{ $product->description ?: 'لا يوجد وصف' }}

                                    </p>

                                </td>


                                {{-- ACTIONS --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center justify-center gap-2">


                                        {{-- EDIT --}}
                                        <a
                                            href="{{ route('admin.products.edit', $product->id) }}"
                                            class="flex h-10 w-10 items-center justify-center rounded-full text-[#004225] transition hover:bg-[#b4f0c7]"
                                            title="تعديل المنتج"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>


                                        {{-- DELETE --}}
                                        <form
                                            action="{{ route('admin.products.destroy', $product->id) }}"
                                            method="POST"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')"
                                                class="flex h-10 w-10 items-center justify-center rounded-full text-red-500 transition hover:bg-red-50"
                                                title="حذف المنتج"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="px-6 py-16 text-center">

                                    <div class="mx-auto flex max-w-sm flex-col items-center">

                                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#ece2c7] text-2xl text-[#004225]">

                                            <i class="fa-solid fa-box-open"></i>

                                        </div>

                                        <h3 class="mt-5 text-xl font-bold text-[#002a15]">
                                            لا توجد منتجات
                                        </h3>

                                        <p class="mt-2 text-sm text-gray-500">
                                            لم يتم إضافة أي منتجات حتى الآن.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}
            @if(method_exists($products, 'links'))

                <div class="border-t border-[#e4e2e1] bg-[#fcf9f8] px-6 py-4">

                    {{ $products->links() }}

                </div>

            @endif

        </div>

    </div>

</div>

@endsection