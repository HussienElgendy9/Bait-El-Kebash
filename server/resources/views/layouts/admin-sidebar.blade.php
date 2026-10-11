 {{-- SIDEBAR --}}
<!DOCTYPE html>
<html dir="rtl" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
            <aside class="hidden w-[280px] shrink-0 flex-col overflow-hidden bg-[#002a15] text-white lg:flex">

                <div class="relative p-8">
                    <div class="absolute -right-16 -top-16 h-32 w-32 rounded-full bg-[#004225] opacity-60 blur-3xl"></div>

                    <div class="relative flex items-center gap-4">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#ece2c7] text-2xl font-bold text-[#002a15]">
                            ب
                        </div>

                        <div>
                            <h2 class="text-xl font-bold">بيت الكباش</h2>
                            <p class="mt-1 text-[10px] uppercase tracking-[0.2em] text-[#98d4ac]">
                                لوحة الإدارة
                            </p>
                        </div>
                    </div>
                </div>

                <nav class="flex flex-1 flex-col gap-2 px-4 py-6">

                    <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-4 rounded-xl bg-[#004225]/70 px-4 py-3 text-[#b4f0c7] transition">
                        <i class="fa-solid fa-chart-line w-5 text-center"></i>
                        <span class="font-bold">لوحة التحكم</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-4 rounded-xl px-4 py-3 text-white/70 transition hover:bg-[#004225]/40 hover:text-[#b4f0c7]">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span class="font-bold">المستخدمين</span>
                    </a>

                    <a href="{{ route('admin.categories.index') }}"
                    class="flex items-center gap-4 rounded-xl px-4 py-3 text-white/70 transition hover:bg-[#004225]/40 hover:text-[#b4f0c7]">
                        <i class="fa-solid fa-layer-group w-5 text-center"></i>
                        <span class="font-bold">الأقسام</span>
                    </a>

                    <a href="{{ route('admin.products.index') }}"
                    class="flex items-center gap-4 rounded-xl px-4 py-3 text-white/70 transition hover:bg-[#004225]/40 hover:text-[#b4f0c7]">
                        <i class="fa-solid fa-box w-5 text-center"></i>
                        <span class="font-bold">المنتجات</span>
                    </a>

                    <a href="{{ route('admin.orders.index') }}"
                    class="flex items-center gap-4 rounded-xl px-4 py-3 text-white/70 transition hover:bg-[#004225]/40 hover:text-[#b4f0c7]">
                        <i class="fa-solid fa-receipt w-5 text-center"></i>
                        <span class="font-bold">الطلبات</span>
                    </a>

                    <div class="mx-4 my-4 border-t border-[#004225]"></div>

                    <a href="{{ route('store.index') }}"
                    class="mt-auto flex items-center gap-4 rounded-xl px-4 py-3 text-white/70 transition hover:bg-[#004225]/40 hover:text-[#b4f0c7]">
                        <i class="fa-solid fa-store w-5 text-center"></i>
                        <span class="font-bold">عرض المتجر</span>
                    </a>

                </nav>
            </aside>
    </body>
 </html>
        