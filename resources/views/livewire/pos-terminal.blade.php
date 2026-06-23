<div class="flex flex-col lg:flex-row min-h-screen bg-gray-100 overflow-hidden"
     x-data="posTerminal()">
    {{-- ===================================================== --}}
    {{-- LEFT — Search + Quick Select                          --}}
    {{-- ===================================================== --}}
  <div class="flex flex-col flex-1 gap-3 p-3 overflow-hidden min-h-0">

        {{-- Search / Scan Bar --}}
        <div class="bg-white rounded-xl shadow-sm p-3">
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 9l4-4m0 0l4 4M7 5v14M21 15l-4 4m0 0l-4-4m4 4V5"/>
                    </svg>
                </span>
                <input
                    type="text"
                    wire:model.live.debounce.200ms="searchTerm"
                    placeholder="Scan barcode or type SKU / product name…"
                    class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    x-ref="scanInput"
                    @keydown.escape="$wire.showResults = false"
                />

                {{-- Search results dropdown --}}
                @if($showResults)
                <div class="absolute z-20 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                    @foreach($searchResults as $result)
                    <button
                        wire:click="selectFromSearch({{ $result['id'] }})"
                        class="w-full flex items-center justify-between px-4 py-3 hover:bg-indigo-50 text-left border-b border-gray-100 last:border-0"
                    >
                        <div>
                            <div class="text-sm font-medium text-gray-800">{{ $result['name'] }}</div>
                            <div class="text-xs text-gray-400">{{ $result['sku'] }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-indigo-600">Rs. {{ number_format($result['selling_price'], 2) }}</div>
                            <div class="text-xs text-gray-400">Stock: {{ $result['stock_quantity'] }}</div>
                        </div>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Quick Select Grid --}}
        <div class="bg-white rounded-xl shadow-sm flex-1 overflow-hidden flex flex-col p-3">

            {{-- Category tabs --}}
            @if(count($categories) > 0)
            <div class="flex gap-2 mb-3 flex-wrap">
                @foreach($categories as $index => $category)
                <button
                    wire:click="setActiveTab({{ $index }})"
                    class="px-3 py-1 rounded-full text-xs font-medium border transition
                        {{ $activeTab === $index
                            ? 'bg-indigo-600 text-white border-indigo-600'
                            : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300' }}"
                >
                    {{ $category['name'] }}
                </button>
                @endforeach
            </div>
            @endif

            {{-- Product tiles --}}
            <div class="grid grid-cols-4 gap-2 overflow-y-auto">
                @forelse($pinnedProducts as $product)
                <button
                    wire:click="addFromQuickSelect({{ $product['id'] }})"
                    class="flex flex-col items-center justify-center p-3 border border-gray-200 rounded-lg
                           hover:border-indigo-400 hover:bg-indigo-50 transition text-center
                           {{ $product['stock_quantity'] <= 0 ? 'opacity-40 cursor-not-allowed' : '' }}"
                    {{ $product['stock_quantity'] <= 0 ? 'disabled' : '' }}
                >
                    <span class="text-sm font-medium text-gray-800 leading-tight">{{ $product['name'] }}</span>
                    <span class="text-xs text-indigo-600 mt-1">Rs. {{ number_format($product['selling_price'], 2) }}</span>
                </button>
                @empty
                <div class="col-span-4 flex items-center justify-center h-24 text-sm text-gray-400">
                    No pinned items in this category. Pin products from the admin panel.
                </div>
                @endforelse
            </div>

        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- RIGHT — Cart + Payment                                --}}
    {{-- ===================================================== --}}
    <div class="w-80 flex flex-col bg-white shadow-lg h-screen shrink-0">

        {{-- Cart header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Cart
                @if(count($cart) > 0)
                <span class="ml-1 text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full">{{ count($cart) }}</span>
                @endif
            </h2>
            @if(count($cart) > 0)
            <button wire:click="clearCart" class="text-xs text-red-400 hover:text-red-600">Clear</button>
            @endif
        </div>

        {{-- Customer strip --}}
        <div class="px-4 py-2 border-b border-gray-100 bg-gray-50">
            @if($customerId)
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-500">Customer: <span class="font-medium text-gray-800">{{ $customerName }}</span></span>
                <button wire:click="detachCustomer" class="text-red-400 hover:text-red-600">✕</button>
            </div>
            @else
            <input
                type="text"
                wire:model.live.debounce.300ms="customerSearch"
                placeholder="Search customer by phone…"
                class="w-full text-xs px-2 py-1.5 border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-indigo-300"
            />
            @if(strlen($customerSearch) >= 3)
            <div class="mt-1 border border-gray-200 rounded bg-white shadow text-xs">
                @foreach(\App\Models\Customer::where('phone', 'like', "%{$customerSearch}%")->limit(4)->get() as $c)
                <button wire:click="attachCustomer({{ $c->id }}, '{{ $c->name }}')"
                        class="w-full text-left px-3 py-2 hover:bg-indigo-50 border-b border-gray-100 last:border-0">
                    <span class="font-medium">{{ $c->name }}</span> — {{ $c->phone }}
                </button>
                @endforeach
            </div>
            @endif
            @endif
        </div>

        {{-- Cart items --}}
        <div class="flex-1 overflow-y-auto px-4 py-2 space-y-2">
            @forelse($cart as $index => $item)
            <div class="flex items-start gap-2 py-2 border-b border-gray-50">

                {{-- Dot: how it was added --}}
                <span class="mt-1.5 w-2 h-2 rounded-full flex-shrink-0
                    {{ $item['added_via'] === 'barcode_scan' ? 'bg-emerald-400' :
                      ($item['added_via'] === 'quick_select' ? 'bg-amber-400' : 'bg-blue-400') }}">
                </span>

                <div class="flex-1 min-w-0">
                    <div class="text-xs font-medium text-gray-800 truncate">{{ $item['name'] }}</div>
                    <div class="text-xs text-gray-400">Rs. {{ number_format($item['price'], 2) }}</div>
                </div>

                {{-- Qty controls --}}
                <div class="flex items-center gap-1">
                    <button wire:click="decrementQty({{ $index }})"
                            class="w-6 h-6 flex items-center justify-center rounded border border-gray-200 text-gray-500 hover:bg-gray-100 text-xs">−</button>
                    <span class="w-6 text-center text-xs font-medium">{{ $item['qty'] }}</span>
                    <button wire:click="incrementQty({{ $index }})"
                            class="w-6 h-6 flex items-center justify-center rounded border border-gray-200 text-gray-500 hover:bg-gray-100 text-xs">+</button>
                </div>

                <div class="text-xs font-semibold text-gray-800 min-w-[52px] text-right">
                    Rs. {{ number_format($item['line_total'], 2) }}
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center h-40 text-gray-300 text-sm">
                <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 19a1 1 0 100 2 1 1 0 000-2zm8 0a1 1 0 100 2 1 1 0 000-2z"/>
                </svg>
                Cart is empty
            </div>
            @endforelse
        </div>

        {{-- Totals --}}
        <div class="px-4 py-3 border-t border-gray-100 space-y-1">
            <div class="flex justify-between text-xs text-gray-500">
                <span>Subtotal</span>
                <span>Rs. {{ number_format($subtotal, 2) }}</span>
            </div>
            @if($discountTotal > 0)
            <div class="flex justify-between text-xs text-green-600">
                <span>Discount</span>
                <span>− Rs. {{ number_format($discountTotal, 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between text-base font-bold text-gray-800 pt-1 border-t border-gray-100">
                <span>Total</span>
                <span>Rs. {{ number_format($grandTotal, 2) }}</span>
            </div>
        </div>

        {{-- Charge button --}}
        <div class="px-4 pb-4">
            <button
                wire:click="openPayment"
                class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition
                       {{ empty($cart) ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ empty($cart) ? 'disabled' : '' }}
            >
                Charge Rs. {{ number_format($grandTotal, 2) }}
            </button>
        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- Payment Modal                                         --}}
    {{-- ===================================================== --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">

            <h3 class="text-lg font-semibold text-gray-800 mb-4">Complete payment</h3>

            {{-- Amount due --}}
            <div class="bg-indigo-50 rounded-xl p-4 text-center mb-4">
                <div class="text-xs text-indigo-400 mb-1">Amount due</div>
                <div class="text-3xl font-bold text-indigo-700">Rs. {{ number_format($grandTotal, 2) }}</div>
            </div>

            {{-- Payment method --}}
            <div class="grid grid-cols-2 gap-2 mb-4">
                @foreach(['cash' => 'Cash', 'card' => 'Card', 'transfer' => 'Transfer', 'loyalty_points' => 'Points'] as $value => $label)
                <button
                    wire:click="$set('paymentMethod', '{{ $value }}')"
                    class="py-2 rounded-lg border text-sm font-medium transition
                        {{ $paymentMethod === $value
                            ? 'bg-indigo-600 text-white border-indigo-600'
                            : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300' }}"
                >
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- Tendered amount --}}
            <div class="mb-3">
                <label class="text-xs text-gray-500 mb-1 block">Amount tendered</label>
                <input
                    type="number"
                    wire:model.live="tenderedAmount"
                    placeholder="0.00"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    step="0.01"
                />
            </div>

            {{-- Change --}}
            @if((float)$tenderedAmount >= $grandTotal && (float)$tenderedAmount > 0)
            <div class="bg-emerald-50 rounded-lg px-4 py-2 flex justify-between text-sm mb-3">
                <span class="text-emerald-600">Change</span>
                <span class="font-bold text-emerald-700">Rs. {{ number_format($changeAmount, 2) }}</span>
            </div>
            @endif

            {{-- Note --}}
            <div class="mb-4">
                <input
                    type="text"
                    wire:model="paymentNote"
                    placeholder="Note (optional)"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-300"
                />
            </div>

            {{-- Actions --}}
            <div class="flex gap-2">
                <button wire:click="closePayment"
                        class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="completeSale"
                        class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition">
                    Complete sale
                </button>
            </div>

        </div>
    </div>
    @endif

    {{-- Toast notifications --}}
    <div
        x-data="{ show: false, message: '', type: 'error' }"
        x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl shadow-lg text-sm font-medium text-white"
        :class="type === 'error' ? 'bg-red-500' : 'bg-emerald-500'"
        style="display:none"
    >
        <span x-text="message"></span>
    </div>

</div>

@push('scripts')
<script>
function posTerminal() {
    return {
        // Auto-focus scan input whenever the page is idle
        init() {
            document.addEventListener('click', (e) => {
                if (!e.target.closest('input, button, select')) {
                    this.$refs.scanInput?.focus();
                }
            });
        }
    }
}
</script>
@endpush
