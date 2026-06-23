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
        <div class="flex items-center gap-3 flex-wrap">

            {{-- Search --}}
            <input type="text"
                   wire:model.live.debounce.200ms="search"
                   placeholder="Search name, SKU, barcode…"
                   class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-56"/>

            {{-- Category filter --}}
            <select wire:model.live="filterCategory"
                    class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            {{-- Stock filter --}}
            <select wire:model.live="filterStock"
                    class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All stock levels</option>
                <option value="low">Low stock</option>
                <option value="out">Out of stock</option>
            </select>

        </div>
        <button wire:click="openCreate"
                class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New product
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Product</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">SKU</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Category</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Price</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Stock</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Flags</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $product->name }}</div>
                        @if($product->barcode)
                        <div class="text-xs text-gray-400 font-mono">{{ $product->barcode }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-mono text-xs text-gray-600">{{ $product->sku }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $product->category->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-800">Rs. {{ number_format($product->selling_price, 2) }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="font-medium {{ $product->stock_quantity === 0 ? 'text-red-500' : ($product->isLowStock() ? 'text-amber-500' : 'text-gray-800') }}">
                            {{ $product->stock_quantity }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            @if($product->has_barcode)
                            <span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 text-xs rounded font-medium">Barcode</span>
                            @endif
                            @if($product->is_pinned)
                            <span class="px-1.5 py-0.5 bg-amber-50 text-amber-600 text-xs rounded font-medium">Pinned</span>
                            @endif
                            @if(!$product->is_active)
                            <span class="px-1.5 py-0.5 bg-gray-100 text-gray-400 text-xs rounded font-medium">Inactive</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <button wire:click="openEdit({{ $product->id }})"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</button>
                            <button wire:click="delete({{ $product->id }})"
                                    wire:confirm="Delete {{ $product->name }}?"
                                    class="text-xs text-red-400 hover:text-red-600 font-medium">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">
                        No products found. Add your first product.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($products->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-screen overflow-y-auto p-6">

            <h3 class="text-base font-semibold text-gray-800 mb-5">
                {{ $editingId ? 'Edit product' : 'New product' }}
            </h3>

            <div class="grid grid-cols-2 gap-4">

                {{-- Name --}}
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Product name <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="name" placeholder="e.g. Plastic Basket 5L"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Category <span class="text-red-400">*</span></label>
                    <select wire:model="category_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="0">Select category…</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->prefix }})</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Supplier --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Supplier <span class="text-gray-400 font-normal">(optional)</span></label>
                    <select wire:model="supplier_id"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">No supplier</option>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Selling price --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Selling price (Rs.) <span class="text-red-400">*</span></label>
                    <input type="number" wire:model="selling_price" placeholder="0.00" step="0.01" min="0"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    @error('selling_price') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Cost price --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cost price (Rs.) <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="number" wire:model="cost_price" placeholder="0.00" step="0.01" min="0"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>

                {{-- Stock quantity --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Stock quantity <span class="text-red-400">*</span></label>
                    <input type="number" wire:model="stock_quantity" min="0"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    @error('stock_quantity') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                {{-- Reorder level --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Reorder level</label>
                    <input type="number" wire:model="reorder_level" min="0"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    <p class="text-xs text-gray-400 mt-0.5">Alert when stock drops to this level</p>
                </div>

                {{-- Barcode section --}}
                <div class="col-span-2 border border-gray-100 rounded-xl p-4 bg-gray-50">
                    <div class="flex items-center gap-2 mb-3">
                        <input type="checkbox" wire:model.live="has_barcode" id="has_barcode"
                               class="rounded border-gray-300 text-indigo-600"/>
                        <label for="has_barcode" class="text-sm font-medium text-gray-700">This item has a barcode</label>
                    </div>
                    @if($has_barcode)
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Barcode number</label>
                        <input type="text" wire:model="barcode" placeholder="Scan or type barcode…"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 font-mono"
                               autofocus/>
                        @error('barcode') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    @else
                    <p class="text-xs text-gray-400">A SKU will be auto-generated from the category prefix when saved.</p>
                    @endif
                </div>

                {{-- Quick-select pin --}}
                <div class="col-span-2 border border-gray-100 rounded-xl p-4 bg-gray-50">
                    <div class="flex items-center gap-2 mb-2">
                        <input type="checkbox" wire:model.live="is_pinned" id="is_pinned"
                               class="rounded border-gray-300 text-indigo-600"/>
                        <label for="is_pinned" class="text-sm font-medium text-gray-700">Pin to quick-select grid at POS</label>
                    </div>
                    @if($is_pinned)
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Grid position <span class="text-gray-400 font-normal">(optional — lower number = first)</span></label>
                        <input type="number" wire:model="pin_position" min="1" placeholder="e.g. 1"
                               class="w-32 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    </div>
                    @else
                    <p class="text-xs text-gray-400">Pinned items appear as quick-tap buttons at the POS counter.</p>
                    @endif
                </div>

                {{-- Book fields (author, ISBN) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Author <span class="text-gray-400 font-normal">(books only)</span></label>
                    <input type="text" wire:model="author" placeholder="e.g. S. Wijesuriya"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">ISBN <span class="text-gray-400 font-normal">(books only)</span></label>
                    <input type="text" wire:model="isbn" placeholder="978-955-…"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>

                {{-- Description --}}
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea wire:model="description" rows="2" placeholder="Brief product description…"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
                </div>

                {{-- Active --}}
                <div class="col-span-2 flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active" id="prod_is_active"
                           class="rounded border-gray-300 text-indigo-600"/>
                    <label for="prod_is_active" class="text-sm text-gray-600">Active — visible at POS and in reports</label>
                </div>

            </div>

            <div class="flex gap-2 mt-6">
                <button wire:click="closeForm"
                        class="flex-1 py-2 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="save"
                        class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">
                    {{ $editingId ? 'Save changes' : 'Create product' }}
                </button>
            </div>

        </div>
    </div>
    @endif

</div>
