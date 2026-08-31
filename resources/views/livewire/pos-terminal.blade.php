<div class="flex overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100" style="height: calc(100vh - 40px);"
    x-data="posTerminal()">

    {{-- ===================================================== --}}
    {{-- LEFT — Search + Quick Select --}}
    {{-- ===================================================== --}}
    <div class="flex flex-col flex-1 gap-2 p-2 overflow-hidden min-w-0">

        {{-- Search / Scan Bar --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-2.5 flex-shrink-0">
            <div class="relative">
                <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <span class="w-6 h-6 rounded-md bg-indigo-50 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                        </svg>
                    </span>
                </span>
                <input type="text" wire:model.live.debounce.200ms="searchTerm"
                    placeholder="Scan barcode or type SKU / product name…"
                    class="w-full pl-11 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition"
                    x-ref="scanInput" @keydown.escape="$wire.set('showResults', false)" />
                <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 4v1m6 11h2m-2 0a2 2 0 100-4h-2a2 2 0 00-2 2m2 2v-2m-9-9h.01M6 8h.01M4 4h4v4H4V4zm12 0h4v4h-4V4zM4 16h4v4H4v-4z" />
                    </svg>
                </span>

                {{-- Search results dropdown --}}
                @if($showResults)
                    <div
                        class="absolute z-20 top-full left-0 right-0 mt-1.5 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden max-h-56 overflow-y-auto">
                        @foreach($searchResults as $result)
                            <button wire:click="selectFromSearch({{ $result['id'] }})"
                                class="w-full flex items-center justify-between px-3 py-2.5 hover:bg-indigo-50 text-left border-b border-gray-50 last:border-0 transition">
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-800 truncate">{{ $result['name'] }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $result['sku'] }}</div>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <div class="text-sm font-semibold text-indigo-600">Rs.
                                        {{ number_format($result['selling_price'], 2) }}
                                    </div>
                                    <div class="text-xs text-gray-400">Stock: {{ $result['stock_quantity'] }}</div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick Select Grid --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col p-2.5 overflow-hidden"
            style="flex: 1 1 0; min-height: 0;">

            {{-- Category tabs --}}
            @if(count($categories) > 0)
                <div class="flex gap-1.5 mb-2.5 flex-wrap flex-shrink-0">
                    @foreach($categories as $index => $category)
                            <button wire:click="setActiveTab({{ $index }})" class="px-3.5 py-1.5 rounded-full text-xs font-semibold border transition
                                                {{ $activeTab === $index
                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm shadow-indigo-200'
                        : 'bg-white text-gray-500 border-gray-200 hover:border-indigo-300 hover:text-indigo-600' }}">
                                {{ $category['name'] }}
                            </button>
                    @endforeach
                </div>
            @endif

            {{-- Product tiles — scrolls internally --}}
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 overflow-y-auto content-start"
                style="flex: 1 1 0; min-height: 0;">
                @forelse($pinnedProducts as $product)
                    <button wire:click="addFromQuickSelect({{ $product['id'] }})" @class([
                        'group relative flex flex-col items-center justify-center gap-0.5 p-2 rounded-xl text-center h-[70px] transition',
                        'border-2 border-red-300 bg-red-50/60 opacity-60 cursor-not-allowed' => $product['stock_quantity'] <= 0,
                        'border border-gray-150 bg-gray-50/50 hover:border-indigo-400 hover:bg-indigo-50 hover:shadow-md active:scale-95 cursor-pointer' => $product['stock_quantity'] > 0,
                    ]) {{ $product['stock_quantity'] <= 0 ? 'disabled' : '' }}>
                        <span
                            class="text-xs font-semibold text-gray-700 leading-tight line-clamp-2 group-hover:text-indigo-700">{{ $product['name'] }}</span>
                        <span class="text-xs text-indigo-600 font-bold">Rs.
                            {{ number_format($product['selling_price'], 2) }}</span>
                    </button>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center h-32 text-sm text-gray-300">
                        <span class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mb-2">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </span>
                        No pinned items yet. Pin products from the admin panel.
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    {{-- ===================================================== --}}
    {{-- RIGHT — Cart + Payment --}}
    {{-- ===================================================== --}}
    <div class="flex-shrink-0 flex flex-col bg-white shadow-xl border-l border-gray-100" style="width: 300px;">

        {{-- Cart header --}}
        <div
            class="flex items-center justify-between px-3.5 py-3 border-b border-gray-100 flex-shrink-0 bg-gradient-to-r from-gray-900 to-gray-800">
            <h2 class="font-semibold text-white text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Cart
                @if(count($cart) > 0)
                    <span
                        class="text-xs bg-indigo-500 text-white px-1.5 py-0.5 rounded-full font-bold">{{ count($cart) }}</span>
                @endif
            </h2>
            @if(count($cart) > 0)
                <button wire:click="clearCart" class="text-xs text-gray-400 hover:text-red-400 transition">Clear
                    all</button>
            @endif
        </div>

        {{-- Customer strip --}}
        <div class="px-3 py-2 border-b border-gray-100 bg-gray-50 flex-shrink-0">
            @if($customerId)
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center gap-1.5 text-gray-500">
                        <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="font-medium text-gray-800">{{ $customerName }}</span>
                    </span>
                    <button wire:click="detachCustomer" class="text-red-400 hover:text-red-600 ml-2">✕</button>
                </div>
            @else
                <div class="relative">
                    <svg class="w-3.5 h-3.5 text-gray-300 absolute left-2 top-1/2 -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="customerSearch"
                        placeholder="Search customer by phone…"
                        class="w-full text-xs pl-7 pr-2 py-1.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-indigo-300" />
                </div>
                @if(strlen($customerSearch) >= 3)
                    <div class="mt-1 border border-gray-200 rounded-lg bg-white shadow-lg text-xs max-h-28 overflow-y-auto">
                        @foreach(\App\Models\Customer::where('phone', 'like', "%{$customerSearch}%")->limit(4)->get() as $c)
                            <button wire:click="attachCustomer({{ $c->id }}, '{{ $c->name }}')"
                                class="w-full text-left px-2.5 py-1.5 hover:bg-indigo-50 border-b border-gray-50 last:border-0 transition">
                                <span class="font-medium text-gray-700">{{ $c->name }}</span> <span class="text-gray-400">—
                                    {{ $c->phone }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>

        {{-- Cart items — scrolls internally --}}
        <div class="overflow-y-auto px-3 py-1" style="flex: 1 1 0; min-height: 0;">
            @forelse($cart as $index => $item)
                    <div class="py-2 border-b border-gray-50">
                        <div class="flex items-center gap-1.5">

                            {{-- Dot: how it was added --}}
                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0
                                        {{ $item['added_via'] === 'barcode_scan' ? 'bg-emerald-400' :
                ($item['added_via'] === 'quick_select' ? 'bg-amber-400' : 'bg-blue-400') }}">
                            </span>

                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-medium text-gray-800 truncate">{{ $item['name'] }}</div>
                                <div class="text-xs text-gray-400">
                                    Rs. {{ number_format($item['price'], 2) }}
                                </div>
                            </div>

                            {{-- Qty controls --}}
                            <div class="flex items-center gap-0.5 flex-shrink-0 bg-gray-50 rounded-lg p-0.5">
                                <button wire:click="decrementQty({{ $index }})"
                                    class="w-5 h-5 flex items-center justify-center rounded-md bg-white border border-gray-200 text-gray-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 text-xs transition">−</button>
                                <input type="text" min="1" max="{{ $item['max_qty'] }}" value="{{ $item['qty'] }}"
                                    wire:change="setQty({{ $index }}, $event.target.value)"
                                    class="w-10 text-center text-xs font-medium border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-indigo-400 py-0.5" />
                                <button wire:click="incrementQty({{ $index }})"
                                    class="w-5 h-5 flex items-center justify-center rounded-md bg-white border border-gray-200 text-gray-500 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-500 text-xs transition">+</button>
                            </div>

                            <div class="text-right flex-shrink-0 min-w-[48px]">
                                @if($item['discount'] > 0)
                                    <div class="text-[10px] text-gray-300 line-through leading-tight">
                                        Rs. {{ number_format($item['price'] * $item['qty'], 2) }}
                                    </div>
                                @endif
                                <div class="text-xs font-bold text-gray-800">
                                    Rs. {{ number_format($item['line_total'], 2) }}
                                </div>
                            </div>
                        </div>

                        {{-- Discount row --}}
                        <div class="flex items-center justify-end gap-2 mt-1 pl-3">
                            @if($item['discount'] > 0)
                                <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full">
                                    − Rs. {{ number_format($item['discount'], 2) }} off
                                </span>
                                <button wire:click="removeItemDiscount({{ $index }})"
                                    class="text-[10px] text-red-400 hover:text-red-600 transition">Remove</button>
                                <button wire:click="openItemDiscount({{ $index }})"
                                    class="text-[10px] text-indigo-500 hover:text-indigo-700 transition">Edit</button>
                            @else
                                <button wire:click="openItemDiscount({{ $index }})"
                                    class="flex items-center gap-1 text-[10px] text-gray-400 hover:text-indigo-600 transition">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    Add discount
                                </button>
                            @endif
                        </div>
                    </div>
            @empty
                <div class="flex flex-col items-center justify-center h-40 text-gray-300 text-xs gap-2">
                    <span class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M17 13l1.5 6M9 19a1 1 0 100 2 1 1 0 000-2zm8 0a1 1 0 100 2 1 1 0 000-2z" />
                        </svg>
                    </span>
                    <span>Cart is empty</span>
                    <span class="text-gray-300">Scan or search to add items</span>
                </div>
            @endforelse
        </div>

        {{-- Totals — always visible --}}
        <div class="px-3.5 py-2.5 border-t border-gray-100 flex-shrink-0 space-y-1 bg-gray-50/50">
            <div class="flex justify-between text-xs text-gray-500">
                <span>Subtotal</span>
                <span>Rs. {{ number_format($subtotal, 2) }}</span>
            </div>
            @if($discountTotal > 0)
                <div class="flex justify-between text-xs text-emerald-600 font-medium">
                    <span>Discount</span>
                    <span>− Rs. {{ number_format($discountTotal, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-base font-bold text-gray-900 pt-1.5 border-t border-gray-200">
                <span>Total</span>
                <span>Rs. {{ number_format($grandTotal, 2) }}</span>
            </div>
        </div>

        {{-- Charge button — always visible --}}
        <div class="px-3.5 py-3 flex-shrink-0">
            <button wire:click="openPayment" @class([
                'w-full py-3 text-white font-bold rounded-xl transition text-sm flex items-center justify-center gap-2',
                'bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 hover:-translate-y-0.5' => count($cart) > 0,
                'bg-gray-200 text-gray-400 cursor-not-allowed' => count($cart) === 0,
            ]) {{ empty($cart) ? 'disabled' : '' }}>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Charge Rs. {{ number_format($grandTotal, 2) }}
            </button>
        </div>

    </div>

    {{-- ===================================================== --}}
    {{-- Payment Modal --}}
    {{-- ===================================================== --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-5">

                <h3 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Complete payment
                </h3>

                {{-- Amount due --}}
                <div
                    class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl p-4 text-center mb-3 shadow-lg shadow-indigo-200">
                    <div class="text-xs text-indigo-200 mb-0.5 uppercase tracking-wide font-semibold">Amount due</div>
                    <div class="text-3xl font-bold text-white">Rs. {{ number_format($grandTotal, 2) }}</div>
                </div>

                {{-- Payment method --}}
                <div class="grid grid-cols-2 gap-1.5 mb-3">
                    @foreach(['cash' => 'Cash', 'card' => 'Card', 'transfer' => 'Transfer', 'loyalty_points' => 'Points'] as $value => $label)
                            <button wire:click="$set('paymentMethod', '{{ $value }}')" class="py-2 rounded-lg border text-xs font-semibold transition
                                                {{ $paymentMethod === $value
                        ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm'
                        : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300' }}">
                                {{ $label }}
                            </button>
                    @endforeach
                </div>

                {{-- Tendered amount --}}
                <div class="mb-2">
                    <label class="text-xs text-gray-500 mb-1 block font-medium">Amount tendered (Rs.)</label>
                    <input type="number" wire:model.live="tenderedAmount" placeholder="0.00"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        step="0.01" />
                </div>

                {{-- Change --}}
                @if((float) $tenderedAmount >= $grandTotal && (float) $tenderedAmount > 0)
                    <div
                        class="bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2.5 flex justify-between text-sm mb-2">
                        <span class="text-emerald-600 font-medium">Change</span>
                        <span class="font-bold text-emerald-700">Rs. {{ number_format($changeAmount, 2) }}</span>
                    </div>
                @endif

                {{-- Note --}}
                <div class="mb-3">
                    <input type="text" wire:model="paymentNote" placeholder="Note (optional)"
                        class="w-full border border-gray-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-300" />
                </div>

                {{-- Validation errors --}}
                @if($errors->any())
                    <div class="mb-2 text-xs text-red-500 bg-red-50 rounded-lg px-3 py-2">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex gap-2">
                    <button wire:click="closePayment"
                        class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button wire:click="completeSale"
                        class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm transition shadow-sm shadow-emerald-200">
                        Complete sale
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ===================================================== --}}
    {{-- Item Discount Modal --}}
    {{-- ===================================================== --}}
    @if($showDiscountModal && $discountingIndex !== null && isset($cart[$discountingIndex]))
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs mx-4 p-5">

                <h3 class="text-sm font-semibold text-gray-800 mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Discount
                </h3>
                <p class="text-xs text-gray-400 mb-4 truncate">{{ $cart[$discountingIndex]['name'] }}</p>

                {{-- Type toggle --}}
                <div class="grid grid-cols-2 gap-1.5 mb-3">
                    <button wire:click="$set('discountType', 'fixed')" class="py-2 rounded-lg border text-xs font-semibold transition
                                {{ $discountType === 'fixed'
            ? 'bg-indigo-600 text-white border-indigo-600'
            : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300' }}">
                        Rs. Fixed
                    </button>
                    <button wire:click="$set('discountType', 'percent')" class="py-2 rounded-lg border text-xs font-semibold transition
                                {{ $discountType === 'percent'
            ? 'bg-indigo-600 text-white border-indigo-600'
            : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300' }}">
                        % Percent
                    </button>
                </div>

                {{-- Value input --}}
                <div class="mb-1">
                    <label class="text-xs text-gray-500 mb-1 block font-medium">
                        {{ $discountType === 'percent' ? 'Percentage off' : 'Amount off (Rs.)' }}
                    </label>
                    <input type="number" wire:model="discountValue"
                        placeholder="{{ $discountType === 'percent' ? 'e.g. 10' : 'e.g. 50.00' }}" step="0.01" min="0"
                        autofocus
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                </div>

                <p class="text-xs text-gray-400 mb-4">
                    Line subtotal: Rs.
                    {{ number_format($cart[$discountingIndex]['price'] * $cart[$discountingIndex]['qty'], 2) }}
                </p>

                {{-- Validation errors --}}
                @if($errors->any())
                    <div class="mb-3 text-xs text-red-500 bg-red-50 rounded-lg px-3 py-2">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex gap-2">
                    <button wire:click="closeDiscountModal"
                        class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button wire:click="applyItemDiscount"
                        class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition shadow-sm shadow-indigo-200">
                        Apply
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- Toast notifications --}}
    <div x-data="{ show: false, message: '', type: 'error' }"
        x-on:notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
        x-show="show" x-transition
        class="fixed bottom-4 right-4 z-50 px-4 py-2.5 rounded-xl shadow-lg text-xs font-medium text-white"
        :class="type === 'error' ? 'bg-red-500' : 'bg-emerald-500'" style="display:none">
        <span x-text="message"></span>
    </div>

</div>

@push('scripts')
    <script>
        function posTerminal() {
            return {
                init() {
                    document.addEventListener('click', (e) => {
                        if (!e.target.closest('input, button, select, a')) {
                            this.$refs.scanInput?.focus();
                        }
                    });
                }
            }
        }
    </script>
@endpush