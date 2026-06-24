<div class="space-y-6" style="font-variant-numeric: tabular-nums;">

    {{-- ── Greeting bar ── --}}
    <div class="flex items-end justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-500 mb-0.5">
                {{ now()->format('l, d F Y') }}
            </p>
            <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                @if(now()->hour < 12) Good morning @elseif(now()->hour < 17) Good afternoon @else Good evening @endif,
                {{ explode(' ', auth()->user()->name)[0] }}.
            </h2>
        </div>
        <a href="{{ route('pos') }}"
           class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition shadow-sm shadow-indigo-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 7H6a2 2 0 00-2 2v9a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Open POS
        </a>
    </div>

    {{-- ── Stat cards ── --}}
    <div class="grid grid-cols-4 gap-4">

        {{-- Today's revenue — hero card --}}
        <div class="col-span-2 bg-gray-900 rounded-2xl p-6 relative overflow-hidden">
            {{-- background accent --}}
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-indigo-600 opacity-20 rounded-full blur-2xl"></div>
            <div class="absolute -right-2 -bottom-8 w-24 h-24 bg-indigo-400 opacity-10 rounded-full blur-xl"></div>

            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400 mb-3">Today's revenue</p>
            <p class="text-4xl font-bold text-white leading-none mb-1">
                Rs. {{ number_format($todayRevenue, 2) }}
            </p>
            <p class="text-sm text-gray-400 mt-3">
                from <span class="text-white font-semibold">{{ $todayCount }}</span>
                {{ Str::plural('transaction', $todayCount) }} today
            </p>
        </div>

        {{-- Avg sale --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Avg. sale today</p>
            <p class="text-3xl font-bold text-gray-900 leading-none">
                Rs. {{ number_format($todayAverage, 0) }}
            </p>
            <p class="text-xs text-gray-400 mt-3">per transaction</p>
        </div>

        {{-- Month revenue --}}
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">This month</p>
            <p class="text-3xl font-bold text-gray-900 leading-none">
                Rs. {{ number_format($monthRevenue, 0) }}
            </p>
            <p class="text-xs text-gray-400 mt-3">{{ now()->format('F Y') }}</p>
        </div>

    </div>

    {{-- ── Middle row ── --}}
    <div class="grid grid-cols-5 gap-4">

        {{-- Recent sales --}}
        <div class="col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50">
                <h3 class="text-sm font-semibold text-gray-800">Recent sales</h3>
                <a href="{{ route('admin.sales.index') }}"
                   class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View all →</a>
            </div>

            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentSales as $sale)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3">
                            <span class="font-mono text-xs font-semibold text-indigo-600">{{ $sale->invoice_number }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400">
                            {{ $sale->sold_at->format('h:i A') }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                            {{ $sale->customer?->name ?? 'Walk-in' }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            @foreach($sale->payments as $payment)
                            <span class="inline-block px-1.5 py-0.5 text-xs rounded font-medium
                                {{ $payment->method === 'cash' ? 'bg-emerald-50 text-emerald-700' :
                                  ($payment->method === 'card' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                                {{ ucfirst($payment->method) }}
                            </span>
                            @endforeach
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-800 text-sm">
                            Rs. {{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button
                                onclick="window.open('{{ route('sales.receipt', $sale->id) }}', 'receipt', 'width=480,height=700,scrollbars=yes,resizable=yes')"
                                class="text-xs text-gray-400 hover:text-indigo-600 transition">
                                Receipt
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-300">
                            No sales today yet. <a href="{{ route('pos') }}" class="text-indigo-500 hover:underline">Open the POS</a> to start billing.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Right column --}}
        <div class="col-span-2 flex flex-col gap-4">

            {{-- Stock health --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Stock health</h3>
                    <a href="{{ route('admin.products.index') }}"
                       class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Manage →</a>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-red-50 rounded-xl p-3 text-center">
                        <div class="text-2xl font-bold text-red-600">{{ $outOfStock }}</div>
                        <div class="text-xs text-red-400 mt-0.5">Out of stock</div>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-3 text-center">
                        <div class="text-2xl font-bold text-amber-600">{{ $lowStockProducts->count() }}</div>
                        <div class="text-xs text-amber-400 mt-0.5">Low stock</div>
                    </div>
                </div>

                {{-- Low stock list --}}
                @if($lowStockProducts->count() > 0)
                <div class="space-y-2">
                    @foreach($lowStockProducts as $product)
                    <div class="flex items-center justify-between py-1.5 border-b border-gray-50 last:border-0">
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-gray-700 truncate">{{ $product->name }}</div>
                            <div class="text-xs text-gray-400">{{ $product->category?->name }}</div>
                        </div>
                        <span class="flex-shrink-0 ml-2 text-xs font-bold px-2 py-0.5 rounded-full
                            {{ $product->stock_quantity === 0
                                ? 'bg-red-100 text-red-600'
                                : 'bg-amber-100 text-amber-600' }}">
                            {{ $product->stock_quantity }} left
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4 text-xs text-gray-300">
                    All products are well stocked.
                </div>
                @endif
            </div>

            {{-- Quick links --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Quick actions</h3>
                <div class="space-y-1.5">
                    <a href="{{ route('admin.products.index') }}"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-gray-50 text-sm text-gray-600 hover:text-gray-900 transition">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                            </svg>
                        </span>
                        Add new product
                    </a>
                    <a href="{{ route('admin.categories.index') }}"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-gray-50 text-sm text-gray-600 hover:text-gray-900 transition">
                        <span class="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </span>
                        Manage categories
                    </a>
                    <a href="{{ route('admin.sales.index') }}"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-gray-50 text-sm text-gray-600 hover:text-gray-900 transition">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </span>
                        View sales history
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>
