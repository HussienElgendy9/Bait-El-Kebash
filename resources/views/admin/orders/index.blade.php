@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-[#fcf9f8] text-[#1b1c1c]">

    <div class="mx-auto max-w-[1280px] px-5 py-10 lg:px-10">

        {{-- HEADER --}}
        <div class="mb-10 flex flex-col justify-between gap-5 border-b border-[#c0c9c0]/30 pb-6 sm:flex-row sm:items-end">

            <div>
                <h1 class="text-4xl font-bold text-[#002a15]">
                    الطلبات
                </h1>

                <p class="mt-2 text-gray-500">
                    إدارة ومتابعة جميع طلبات العملاء.
                </p>
            </div>

        </div>


        {{-- TABLE --}}
        <div class="overflow-hidden rounded-xl bg-white shadow-md">

            <div class="overflow-x-auto">

                <table class="w-full text-right">

                    {{-- TABLE HEADER --}}
                    <thead class="border-b border-[#717971]/10 bg-[#f6f3f2]">

                        <tr>
                            <th class="px-6 py-4 text-sm font-semibold text-[#404942]">
                                رقم الطلب
                            </th>

                            <th class="px-6 py-4 text-sm font-semibold text-[#404942]">
                                العميل
                            </th>

                            <th class="px-6 py-4 text-sm font-semibold text-[#404942]">
                                الحالة
                            </th>

                            <th class="px-6 py-4 text-sm font-semibold text-[#404942]">
                                التاريخ
                            </th>

                            <th class="px-6 py-4 text-left text-sm font-semibold text-[#404942]">
                                الإجراءات
                            </th>
                        </tr>

                    </thead>


                    {{-- TABLE BODY --}}
                    <tbody>

                        @forelse($orders as $order)

                            <tr class="group border-b border-[#717971]/5 transition hover:bg-[#fcf9f8]">

                                {{-- ID --}}
                                <td class="px-6 py-5 font-medium text-[#002a15]">
                                    #{{ $order->id }}
                                </td>


                                {{-- CUSTOMER --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center gap-3">

                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#ece2c7] font-bold text-[#004225]">
                                            {{ mb_substr($order->user->name, 0, 1) }}
                                        </div>

                                        <span class="font-medium text-[#1b1c1c]">
                                            {{ $order->user->name }}
                                        </span>

                                    </div>

                                </td>


                                {{-- STATUS --}}
                                <td class="px-6 py-5">

                                    @if($order->status === 'completed')

                                        <span class="inline-flex items-center gap-2 rounded-full bg-[#004225]/10 px-3 py-1 text-xs font-semibold text-[#004225]">
                                            <span class="h-2 w-2 rounded-full bg-[#004225]"></span>
                                            مكتمل
                                        </span>

                                    @elseif($order->status === 'pending')

                                        <span class="inline-flex items-center gap-2 rounded-full bg-[#f4bb92]/30 px-3 py-1 text-xs font-semibold text-[#543012]">
                                            <span class="h-2 w-2 rounded-full bg-[#f4bb92]"></span>
                                            قيد الانتظار
                                        </span>

                                    @elseif($order->status === 'cancelled')

                                        <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                                            ملغي
                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                            {{ $order->status }}
                                        </span>

                                    @endif

                                </td>


                                {{-- DATE --}}
                                <td class="px-6 py-5 text-sm text-[#404942]">
                                    {{ $order->created_at->format('Y/m/d') }}

                                    <span class="mr-2 text-xs text-gray-400">
                                        {{ $order->created_at->format('h:i A') }}
                                    </span>
                                </td>


                                {{-- ACTIONS --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center justify-end gap-2">

                                        {{-- EDIT --}}
                                        <a
                                            href="{{ route('admin.orders.edit', $order->id) }}"
                                            class="flex h-10 w-10 items-center justify-center rounded-full text-[#404942] transition hover:bg-[#ece2c7] hover:text-[#004225]"
                                            title="تعديل"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>


                                        {{-- DELETE --}}
                                        <form
                                            action="{{ route('admin.orders.destroy', $order->id) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                onclick="return confirm('Delete this order?')"
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

                                <td colspan="5" class="px-6 py-16 text-center text-gray-500">

                                    <i class="fa-solid fa-receipt mb-4 block text-4xl text-gray-300"></i>

                                    <p>
                                        لا توجد طلبات حتى الآن.
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