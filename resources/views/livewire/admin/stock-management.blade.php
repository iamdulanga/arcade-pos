<div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filters --}}
    <div class="flex items-center gap-3 flex-wrap mb-5">
        <input type="text"
               wire:model.live.debounce.200ms="search"
               placeholder="Search product or SKU…"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-52"/>

        <select wire:model.live="filterCategory"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <option value="">All categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStock"
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <option value="">All stock levels</option>
            <option value="low">Low stock</option>
            <option value="out">Out of stock</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Product</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Category</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Stock</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Reorder at</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $product->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $product->sku }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-base font-bold
                            {{ $product->stock_quantity === 0
                                ? 'text-red-600'
                                : ($product->isLowStock() ? 'text-amber-500' : 'text-gray-800') }}">
                            {{ $product->stock_quantity }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-xs text-gray-400">{{ $product->reorder_level }}</td>
                    <td class="px-4 py-3">
                        @if($product->stock_quantity === 0)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Out of stock
                        </span>
                        @elseif($product->isLowStock())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span> Low stock
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> OK
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-3">
                            <button wire:click="openHistory({{ $product->id }})"
                                    class="text-xs text-gray-400 hover:text-indigo-600 font-medium transition">
                                History
                            </button>
                            <button wire:click="openAdjust({{ $product->id }})"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                Adjust stock
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">No products found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($products->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    {{-- Adjust stock modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">

            <h3 class="text-base font-semibold text-gray-800 mb-1">Adjust stock</h3>
            <p class="text-xs text-gray-400 mb-5">{{ $selectedProductName }}</p>

            {{-- Current stock --}}
            <div class="bg-gray-50 rounded-xl px-4 py-3 flex items-center justify-between mb-5">
                <span class="text-xs text-gray-500">Current stock</span>
                <span class="text-2xl font-bold text-gray-800">{{ $currentStock }}</span>
            </div>

            <div class="space-y-4">

                {{-- Type --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Adjustment type</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([
                            'stock_in'   => ['label' => 'Stock in',   'color' => 'emerald'],
                            'stock_out'  => ['label' => 'Stock out',  'color' => 'blue'],
                            'damage'     => ['label' => 'Damage',     'color' => 'red'],
                            'correction' => ['label' => 'Correction', 'color' => 'gray'],
                        ] as $value => $opt)
                        <button wire:click="$set('adjustmentType', '{{ $value }}')"
                                class="py-2 rounded-lg border text-xs font-medium transition
                                    {{ $adjustmentType === $value
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300' }}">
                            {{ $opt['label'] }}
                        </button>
                        @endforeach
                    </div>

                    <p class="text-xs text-gray-400 mt-2">
                        @if($adjustmentType === 'stock_in') Adding stock received from supplier.
                        @elseif($adjustmentType === 'stock_out') Removing stock manually (not a sale).
                        @elseif($adjustmentType === 'damage') Stock lost due to damage or expiry.
                        @else Correcting a stock count error.
                        @endif
                    </p>
                </div>

                {{-- Quantity --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Quantity</label>
                    <input type="number" wire:model="quantity" min="1"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    @error('quantity') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

                    {{-- Preview --}}
                    @if($quantity > 0)
                    <p class="text-xs text-gray-400 mt-1">
                        Stock will change from
                        <span class="font-semibold text-gray-700">{{ $currentStock }}</span>
                        →
                        <span class="font-semibold {{ in_array($adjustmentType, ['stock_out','damage']) && ($currentStock - $quantity) < 0 ? 'text-red-500' : 'text-indigo-600' }}">
                            {{ in_array($adjustmentType, ['stock_out', 'damage'])
                                ? $currentStock - $quantity
                                : $currentStock + $quantity }}
                        </span>
                    </p>
                    @endif
                </div>

                {{-- Reference --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Reference <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" wire:model="reference" placeholder="e.g. PO-2024-001"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>

                {{-- Note --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Note <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" wire:model="note" placeholder="Brief reason for adjustment"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>

            </div>

            <div class="flex gap-2 mt-6">
                <button wire:click="closeForm"
                        class="flex-1 py-2 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="save"
                        class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">
                    Save adjustment
                </button>
            </div>

        </div>
    </div>
    @endif

    {{-- History modal --}}
    @if($showHistory)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6">

            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-semibold text-gray-800">Stock history</h3>
                    <p class="text-xs text-gray-400">{{ $historyProductName }}</p>
                </div>
                <button wire:click="closeHistory" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($history as $adj)
                <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
                    {{-- Change indicator --}}
                    <span class="w-12 text-right text-xs font-bold flex-shrink-0
                        {{ $adj->quantity_change > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $adj->quantity_change > 0 ? '+' : '' }}{{ $adj->quantity_change }}
                    </span>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-gray-700 capitalize">
                                {{ str_replace('_', ' ', $adj->type) }}
                            </span>
                            @if($adj->reference)
                            <span class="text-xs text-gray-400 font-mono">{{ $adj->reference }}</span>
                            @endif
                        </div>
                        @if($adj->note)
                        <div class="text-xs text-gray-400">{{ $adj->note }}</div>
                        @endif
                        <div class="text-xs text-gray-400 mt-0.5">
                            {{ $adj->created_at->format('d M Y h:i A') }} · {{ $adj->user->name }}
                        </div>
                    </div>

                    <div class="text-right flex-shrink-0 text-xs text-gray-400">
                        <div>{{ $adj->quantity_before }} → <span class="font-semibold text-gray-700">{{ $adj->quantity_after }}</span></div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-sm text-gray-300">No stock history yet.</div>
                @endforelse
            </div>

        </div>
    </div>
    @endif

</div>
