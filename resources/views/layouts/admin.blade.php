<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} — Arcade POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="antialiased bg-gray-50 text-gray-800">

    <div class="flex h-screen overflow-hidden">

        {{-- ======================== SIDEBAR ======================== --}}
        <aside class="w-56 flex-shrink-0 bg-white border-r border-gray-200 flex flex-col">

            {{-- Logo --}}
            <div class="h-14 flex items-center px-5 border-b border-gray-100">
                <span class="font-bold text-indigo-600 text-base">Arcade POS</span>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5 text-sm">

                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition
                      {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M3 12l9-9 9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('pos') }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    POS Terminal
                </a>

                <div class="pt-3 pb-1 px-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Inventory</span>
                </div>

                <a href="{{ route('admin.categories.index') }}"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition
                      {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Categories
                </a>

                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition
                      {{ request()->routeIs('admin.products.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                    </svg>
                    Products
                </a>

                <a href="{{ route('admin.stock.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition
                      {{ request()->routeIs('admin.stock.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    Stock
                </a>

                <a href="{{ route('admin.suppliers.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition
                      {{ request()->routeIs('admin.suppliers.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Suppliers
                </a>

                <div class="pt-3 pb-1 px-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sales</span>
                </div>

                <a href="{{ route('admin.sales.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition
                      {{ request()->routeIs('admin.sales.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Sales History
                </a>

                <div class="pt-3 pb-1 px-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Settings</span>
                </div>

                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition
                        {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Users
                </a>

                <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition
                      {{ request()->routeIs('admin.customers.*') ? 'bg-indigo-50 text-indigo-700 font-medium' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 20h5v-2a4 4 0 00-5-5M9 20H4v-2a4 4 0 015-5m6-7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Customers
                </a>

            </nav>

            {{-- User / logout --}}
            <div class="border-t border-gray-100 p-3">
                <div class="flex items-center gap-2 px-2 py-1.5">
                    <div
                        class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600 flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-medium text-gray-800 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <form method="POST" action="/logout" class="mt-1">
                    @csrf
                    <button type="submit"
                        class="w-full text-left flex items-center gap-2 px-3 py-1.5 text-xs text-gray-500 hover:text-red-500 rounded-lg hover:bg-red-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>

        </aside>

        {{-- ======================== MAIN ======================== --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- Top bar --}}
            <header class="h-14 flex-shrink-0 bg-white border-b border-gray-200 flex items-center justify-between px-6">
                <h1 class="text-sm font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>
                <span class="text-xs text-gray-400">{{ now()->format('D, d M Y') }}</span>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-6">
                {{ $slot }}
            </main>

        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>