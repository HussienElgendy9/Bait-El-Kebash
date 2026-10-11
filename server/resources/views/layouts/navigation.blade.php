<nav x-data="{ open: false }" class="bg-brand-beige w-full">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-24">
            {{-- <div class="flex">
                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div> --}}

            <!-- Logo -->
            <div class="shrink-0 flex items-center ">
                    <a href="{{ route('store.home') }}">
                        {{-- <x-application-logo class="block h-9 w-auto fill-current text-gray-800" /> --}}
                        <img class='block h-24 w-auto fill-current' src="{{ asset('images/logo2.svg') }}" alt="">
                    </a>
            </div>
            <div class="hidden lg:flex items-center justify-evenly text-brand-green font-hacen text-xl font-extrabold gap-8">
                    <a href="" class="relative group transition duration-300 ease-in-out hover:text-green-700">
                        الرئيسية            
                        <span class="absolute right-0 -bottom-1 h-0.5 w-0 bg-brand-green transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="" class="relative group transition duration-300 ease-in-out hover:text-green-700">
                        المنتجات
                        <span class="absolute right-0 -bottom-1 h-0.5 w-0 bg-brand-green transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="" class="relative group transition duration-300 ease-in-out hover:text-green-700">
                        عن المزرعة
                        <span class="absolute right-0 -bottom-1 h-0.5 w-0 bg-brand-green transition-all duration-300 group-hover:w-full"></span>
                    </a>
                    <a href="" class="relative group transition duration-300 ease-in-out hover:text-green-700">
                        تواصل معنا
                        <span class="absolute right-0 -bottom-1 h-0.5 w-0 bg-brand-green transition-all duration-300 group-hover:w-full"></span>
                    </a>
            </div>
            
            @if (Auth::user())       
            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-8">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                @if (Auth::user())
                                <div>{{ Auth::user()->name }}</div>
                                
                                @endif

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                            
                        </x-slot>
                    </x-dropdown>
                    {{-- Cart --}}
                    <div class="hidden lg:flex items-center">
                        <a href="{{ route('cart.index') }}"class="relative inline-block group">
                            <i class="  cart-icon fa-solid fa-cart-shopping">
                                <span class=" flex items-center justify-center h-5 w-5 absolute -top-3 -right-3 group-hover:-top-5 transition-all duration-200 rounded-full border-2 border-[#15803d] bg-brand-beige text-xs font-semibold text-brand-green">{{ array_sum(array_column(session('cart', []), 'qty')) }}</span>
                            </i>
                        </a>
                    </div>
            </div>
            @else
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>احجز دلوقتي!</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('login')">
                                {{ __('Login') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="get" action="{{ route('register') }}">
                                @csrf

                                <x-dropdown-link :href="route('register')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Sign in') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

            @endif

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('store.home')" :active="request()->routeIs('store.home')">
                الرئيسية
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('store.index')" :active="request()->routeIs('products.*')">
                المنتجات
            </x-responsive-nav-link>

            <x-responsive-nav-link href="#">
                عن المزرعة
            </x-responsive-nav-link>

            <x-responsive-nav-link href="#">
                تواصل معنا
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                @if (Auth::user())
                            <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                @endif
            </div>
            @if (Auth::user())  
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                    <x-responsive-nav-link :href="route('cart.index')">
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-cart-shopping text-brand-green"></i>
                                <span>السلة</span>
                            </div>

                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-green text-xs font-semibold text-white">
                                {{ array_sum(array_column(session('cart', []), 'qty')) }}
                            </span>
                        </div>
                    </x-responsive-nav-link>
                </div>
            @else
            <div class="mt-3 space-y-1">
                {{-- <x-slot name="content"> --}}
                    <x-dropdown-link :href="route('login')">
                         {{ __('Login') }}
                    </x-dropdown-link>

                    <!-- Authentication -->
                    <form method="get" action="{{ route('register') }}">
                        @csrf
                        <x-dropdown-link :href="route('register')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Sign in') }}
                        </x-dropdown-link>
                    </form>
                {{-- </x-slot> --}}
            </div>
            @endif
        </div>
    </div>
</nav>
