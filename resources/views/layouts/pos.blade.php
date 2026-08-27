<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS Terminal — Arcade POS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
    </style>
</head>
<body class="antialiased bg-gray-100">

    {{-- Top bar — fixed height 40px --}}
    <div style="height: 40px;" class="flex items-center justify-between px-4 bg-white border-b border-gray-200 text-sm flex-shrink-0">
        <div class="flex items-center gap-3">
            <span class="font-bold text-indigo-600 text-sm">Arcade POS</span>
            <span class="text-gray-400 text-xs">{{ now()->format('D, d M Y  H:i') }}</span>
        </div>
        <div class="flex items-center gap-4 text-xs text-gray-500">
            <span>{{ auth()->user()->name }}</span>
            <a href="{{ route('dashboard') }}" class="hover:text-indigo-600">← Dashboard</a>
            <form method="POST" action="/logout" class="inline">
                @csrf
                <button type="submit" class="hover:text-red-500">Logout</button>
            </form>
        </div>
    </div>

    {{-- Remaining height exactly --}}
    <div style="height: calc(100vh - 40px); overflow: hidden;">
        {{ $slot }}
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
