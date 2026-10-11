@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-[#fcf9f8] p-5 text-[#1b1c1c] lg:p-10">

    <div class="mx-auto max-w-[1400px]">

        {{-- HEADER --}}
        <div class="mb-10 border-b border-[#e4e2e1] pb-6">

            <p class="text-sm font-bold tracking-wider text-[#8b5e3c]">
                بيت الكباش
            </p>

            <h1 class="mt-2 text-4xl font-bold text-[#002a15]">
                المستخدمين
            </h1>

            <p class="mt-3 text-gray-500">
                إدارة ومتابعة حسابات المستخدمين.
            </p>

        </div>


        {{-- TABLE --}}
        <div class="overflow-hidden rounded-[24px] bg-white shadow-sm">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[900px]">

                    {{-- HEADER --}}
                    <thead>

                        <tr class="border-b border-[#e4e2e1] bg-[#f6f3f2]">

                            <th class="px-6 py-5 text-right text-sm font-bold text-gray-500">
                                المستخدم
                            </th>

                            <th class="px-6 py-5 text-right text-sm font-bold text-gray-500">
                                البريد الإلكتروني
                            </th>

                            <th class="px-6 py-5 text-right text-sm font-bold text-gray-500">
                                رقم الهاتف
                            </th>

                            <th class="px-6 py-5 text-right text-sm font-bold text-gray-500">
                                العنوان
                            </th>

                            <th class="px-6 py-5 text-center text-sm font-bold text-gray-500">
                                الإجراءات
                            </th>

                        </tr>

                    </thead>


                    {{-- BODY --}}
                    <tbody>

                        @forelse($users as $user)

                            <tr class="group border-b border-[#e4e2e1]/60 transition hover:bg-[#fcf9f8]">

                                {{-- USER --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center gap-4">

                                        {{-- AVATAR --}}
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#ece2c7] font-bold text-[#004225]">

                                            {{ mb_substr($user->name, 0, 1) }}

                                        </div>


                                        <div>

                                            <p class="font-bold text-[#002a15]">
                                                {{ $user->name }}
                                            </p>

                                            <p class="mt-1 text-xs text-gray-400">
                                                #{{ $user->id }}
                                            </p>

                                        </div>

                                    </div>

                                </td>


                                {{-- EMAIL --}}
                                <td class="px-6 py-5 text-sm text-gray-600">

                                    {{ $user->email }}

                                </td>


                                {{-- PHONE --}}
                                <td class="px-6 py-5">

                                    @if($user->phone_number)

                                        <span class="font-medium text-[#002a15]">
                                            {{ $user->phone_number }}
                                        </span>

                                    @else

                                        <span class="text-sm text-gray-400">
                                            غير متوفر
                                        </span>

                                    @endif

                                </td>


                                {{-- ADDRESS --}}
                                <td class="max-w-xs px-6 py-5">

                                    @if($user->address)

                                        <p class="line-clamp-2 text-sm text-gray-500">
                                            {{ $user->address }}
                                        </p>

                                    @else

                                        <span class="text-sm text-gray-400">
                                            غير متوفر
                                        </span>

                                    @endif

                                </td>


                                {{-- ACTIONS --}}
                                <td class="px-6 py-5">

                                    <div class="flex items-center justify-center gap-2">


                                        {{-- VIEW --}}
                                        <a
                                            href="{{ route('admin.users.show', $user->id) }}"
                                            class="flex h-10 w-10 items-center justify-center rounded-full text-[#004225] transition hover:bg-[#b4f0c7]"
                                            title="عرض المستخدم"
                                        >
                                            <i class="fa-solid fa-eye"></i>
                                        </a>


                                        {{-- EDIT --}}
                                        <a
                                            href="{{ route('admin.users.edit', $user->id) }}"
                                            class="flex h-10 w-10 items-center justify-center rounded-full text-blue-600 transition hover:bg-blue-50"
                                            title="تعديل المستخدم"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>


                                        {{-- DELETE --}}
                                        <form
                                            action="{{ route('admin.users.destroy', $user->id) }}"
                                            method="POST"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')"
                                                class="flex h-10 w-10 items-center justify-center rounded-full text-red-500 transition hover:bg-red-50"
                                                title="حذف المستخدم"
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
                                            <i class="fa-solid fa-users"></i>
                                        </div>

                                        <h3 class="mt-5 text-xl font-bold text-[#002a15]">
                                            لا يوجد مستخدمين
                                        </h3>

                                        <p class="mt-2 text-sm text-gray-500">
                                            لم يتم العثور على أي حسابات مستخدمين.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}
            @if(method_exists($users, 'links'))

                <div class="border-t border-[#e4e2e1] bg-[#fcf9f8] px-6 py-4">
                    {{ $users->links() }}
                </div>

            @endif

        </div>

    </div>

</div>

@endsection