<div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
        {{ session('error') }}
    </div>
    @endif

    {{-- Filters --}}
    <div class="flex items-center gap-3 flex-wrap mb-5">

        <input type="text"
               wire:model.live.debounce.200ms="search"
               placeholder="Search invoice or customer…"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-56"/>

        <input type="date"
               wire:model.live="filterDate"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>

        <select wire:model.live="filterStatus"
                class="w-48 pr-5 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <option value="">All statuses</option>
            <option value="completed">Completed</option>
            <option value="voided">Voided</option>
        </select>

        @if($filterDate)
        <button wire:click="$set('filterDate', '')" class="text-xs text-gray-400 hover:text-red-500">Clear date</button>
        @endif

    </div>

    {{-- Summary bar --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
            <div class="text-xs text-gray-400 mb-0.5">Today's sales</div>
            <div class="text-lg font-bold text-gray-800">
                Rs. {{ number_format(\App\Models\Sale::completed()->today()->sum('total_amount'), 2) }}
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
            <div class="text-xs text-gray-400 mb-0.5">Today's transactions</div>
            <div class="text-lg font-bold text-gray-800">
                {{ \App\Models\Sale::completed()->today()->count() }}
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
            <div class="text-xs text-gray-400 mb-0.5">This month</div>
            <div class="text-lg font-bold text-gray-800">
                Rs. {{ number_format(\App\Models\Sale::completed()->thisMonth()->sum('total_amount'), 2) }}
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Invoice</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Date & Time</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Cashier</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Customer</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Items</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Payment</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($sales as $sale)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <span class="font-mono text-xs font-semibold text-indigo-600">{{ $sale->invoice_number }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        <div>{{ $sale->sold_at->format('d M Y') }}</div>
                        <div class="text-gray-400">{{ $sale->sold_at->format('h:i A') }}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $sale->user->name }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $sale->customer?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600 text-center">{{ $sale->items->count() }}</td>
                    <td class="px-4 py-3">
                        @foreach($sale->payments as $payment)
                        <span class="inline-block px-1.5 py-0.5 text-xs rounded font-medium
                            {{ $payment->method === 'cash' ? 'bg-green-50 text-green-700' :
                              ($payment->method === 'card' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ ucfirst($payment->method) }}
                        </span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-800 text-sm">
                        Rs. {{ number_format($sale->total_amount, 2) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $sale->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sale->status === 'completed' ? 'bg-emerald-500' : 'bg-red-400' }}"></span>
                            {{ ucfirst($sale->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Print receipt in popup --}}
                            <button
                                onclick="window.open('{{ route('sales.receipt', $sale->id) }}', 'receipt', 'width=480,height=700,scrollbars=yes,resizable=yes')"
                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                Receipt
                            </button>

                            {{-- Void sale --}}
                            @if($sale->status === 'completed')
                            <button wire:click="void({{ $sale->id }})"
                                    wire:confirm="Void {{ $sale->invoice_number }}? Stock will be restored."
                                    class="text-xs text-red-400 hover:text-red-600 font-medium">
                                Void
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-10 text-center text-sm text-gray-400">
                        No sales found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($sales->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $sales->links() }}
        </div>
        @endif
    </div>

</div>
