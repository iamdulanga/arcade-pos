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

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <input type="text"
               wire:model.live.debounce.200ms="search"
               placeholder="Search name, phone, email…"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-64"/>

        <button wire:click="openCreate"
                class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New customer
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Customer</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Phone</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Purchases</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total spent</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Points</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($customers as $customer)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600 flex-shrink-0">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">{{ $customer->name }}</div>
                                @if($customer->address)
                                <div class="text-xs text-gray-400 truncate max-w-xs">{{ $customer->address }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $customer->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $customer->email ?? '—' }}</td>
                    <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $customer->sales_count }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-800">
                        Rs. {{ number_format($customer->sales_sum_total_amount ?? 0, 2) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2 py-0.5 bg-amber-50 text-amber-600 text-xs font-semibold rounded-full">
                            {{ $customer->loyalty_points }} pts
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <button wire:click="openHistory({{ $customer->id }})"
                                    class="text-xs text-gray-400 hover:text-indigo-600 font-medium transition">
                                History
                            </button>
                            <button wire:click="openEdit({{ $customer->id }})"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</button>
                            <button wire:click="delete({{ $customer->id }})"
                                    wire:confirm="Delete {{ $customer->name }}?"
                                    class="text-xs text-red-400 hover:text-red-600 font-medium">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">
                        No customers yet. Customers are created at the POS counter during a sale.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($customers->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">

            <h3 class="text-base font-semibold text-gray-800 mb-5">
                {{ $editingId ? 'Edit customer' : 'New customer' }}
            </h3>

            <div class="space-y-4">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Full name <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="name" placeholder="e.g. Kamal Perera"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                        <input type="text" wire:model="phone" placeholder="07X-XXXXXXX"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                        @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" wire:model="email" placeholder="customer@email.com"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                        @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                    <textarea wire:model="address" rows="2" placeholder="Customer address…"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active" id="cust_is_active"
                           class="rounded border-gray-300 text-indigo-600"/>
                    <label for="cust_is_active" class="text-sm text-gray-600">Active</label>
                </div>

            </div>

            <div class="flex gap-2 mt-6">
                <button wire:click="closeForm"
                        class="flex-1 py-2 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="save"
                        class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">
                    {{ $editingId ? 'Save changes' : 'Create customer' }}
                </button>
            </div>

        </div>
    </div>
    @endif

    {{-- Purchase history modal --}}
    @if($showHistory)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6">

            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-semibold text-gray-800">Purchase history</h3>
                    <p class="text-xs text-gray-400">{{ $historyCustomerName }}</p>
                </div>
                <button wire:click="closeHistory" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($history as $sale)
                <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-semibold text-indigo-600">{{ $sale->invoice_number }}</span>
                            <span class="text-xs text-gray-400">{{ $sale->sold_at->format('d M Y') }}</span>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">
                            {{ $sale->items->count() }} {{ Str::plural('item', $sale->items->count()) }}
                            @foreach($sale->payments as $payment)
                            · {{ ucfirst($payment->method) }}
                            @endforeach
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <span class="font-semibold text-gray-800 text-sm">Rs. {{ number_format($sale->total_amount, 2) }}</span>
                        <button
                            onclick="window.open('{{ route('sales.receipt', $sale->id) }}', 'receipt', 'width=480,height=700,scrollbars=yes,resizable=yes')"
                            class="text-xs text-indigo-500 hover:text-indigo-700 font-medium">
                            Receipt
                        </button>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-sm text-gray-300">No purchases yet.</div>
                @endforelse
            </div>

        </div>
    </div>
    @endif

</div>
