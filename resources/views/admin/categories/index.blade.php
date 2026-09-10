@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-[#fcf9f8] text-[#1b1c1c]">

    <div class="mx-auto max-w-[1280px] px-5 py-10 lg:px-10">

        {{-- HEADER --}}
        <div class="mb-10 flex flex-col justify-between gap-5 border-b border-[#c0c9c0]/30 pb-6 sm:flex-row sm:items-end">

            <div>
                <h1 class="text-4xl font-bold text-[#002a15]">
                    الأقسام
                </h1>

                <p class="mt-2 text-gray-500">
                    إدارة أقسام المنتجات في متجر بيت الكباش.
                </p>
            </div>

            {{-- ADD CATEGORY --}}
            <a
                href="{{ route('admin.categories.create') }}"
                class="flex items-center justify-center gap-2 rounded-lg bg-[#002a15] px-6 py-3 font-bold text-white transition hover:bg-[#004225] hover:shadow-lg"
            >
                <i class="fa-solid fa-plus"></i>
                إضافة قسم
            </a>

        </div>


        {{-- TABLE --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-md">

            <div class="overflow-x-auto">

                <table class="w-full text-right">

                    {{-- TABLE HEADER --}}
                    <thead class="border-b border-[#717971]/10 bg-[#f6f3f2]">

                        <tr>
                            <th class="px-6 py-4 text-sm font-semibold text-[#404942]">
                                الرقم
                            </th>

                            <th class="px-6 py-4 text-sm font-semibold text-[#404942]">
                                اسم القسم
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#404942]">
                                الإجراءات
                            </th>
                        </tr>

                    </thead>


                    {{-- TABLE BODY --}}
                    <tbody>

                        @forelse($categories as $category)

                            <tr class="group border-b border-[#717971]/5 transition hover:bg-[#fcf9f8]">

                                {{-- ID --}}
                                <td class="px-6 py-5 font-medium text-[#002a15]">
                                    #{{ $category->id }}
                                </td>


                                {{-- CATEGORY NAME --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center gap-3">

                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#ece2c7] text-[#004225]">
                                            <i class="fa-solid fa-layer-group"></i>
                                        </div>

                                        <span class="font-medium text-[#1b1c1c]">
                                            {{ $category->name }}
                                        </span>

                                    </div>

                                </td>


                                {{-- ACTIONS --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center justify-end gap-2">

                                        {{-- EDIT --}}
                                        <a
                                            href="{{ route('admin.categories.edit', $category->id) }}"
                                            class="flex h-10 w-10 items-center justify-center rounded-full text-[#404942] transition hover:bg-[#ece2c7] hover:text-[#004225]"
                                            title="تعديل"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>


                                        {{-- DELETE --}}
                                        <form
                                            action="{{ route('admin.categories.destroy', $category->id) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                onclick="return confirm('Delete this category?')"
                                                class="flex h-10 w-10 items-center justify-center rounded-full text-[#404942] transition hover:bg-red-100 hover:text-red-600"
                                                title="حذف"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="3" class="px-6 py-16 text-center text-gray-500">

                                    <i class="fa-solid fa-layer-group mb-4 block text-4xl text-gray-300"></i>

                                    <p>
                                        لا توجد أقسام حتى الآن.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection