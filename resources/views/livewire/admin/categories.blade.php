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
        <div>
            <p class="text-xs text-gray-400 mt-0.5">Prefixes are used to auto-generate SKUs (e.g. PLT → PLT-0042)</p>
        </div>
        <button wire:click="openCreate"
                class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New category
        </button>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <input type="text"
               wire:model.live.debounce.200ms="search"
               placeholder="Search categories…"
               class="w-full max-w-xs px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Prefix</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Products</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($categories as $category)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $category->name }}</div>
                        @if($category->description)
                        <div class="text-xs text-gray-400">{{ $category->description }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-block px-2 py-0.5 bg-indigo-50 text-indigo-700 text-xs font-mono font-semibold rounded">
                            {{ $category->prefix }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $category->products_count }}</td>
                    <td class="px-4 py-3">
                        <button wire:click="toggleActive({{ $category->id }})"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition
                                       {{ $category->is_active
                                           ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                           : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $category->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <button wire:click="openEdit({{ $category->id }})"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</button>
                            <button wire:click="delete({{ $category->id }})"
                                    wire:confirm="Delete this category?"
                                    class="text-xs text-red-400 hover:text-red-600 font-medium">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-400">
                        No categories yet. Create your first one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create / Edit Modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">

            <h3 class="text-base font-semibold text-gray-800 mb-4">
                {{ $editingId ? 'Edit category' : 'New category' }}
            </h3>

            <div class="space-y-4">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Category name <span class="text-red-400">*</span></label>
                    <input type="text"
                           wire:model.live="name"
                           placeholder="e.g. Plastics"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    @error('name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        SKU prefix <span class="text-red-400">*</span>
                        <span class="text-gray-400 font-normal ml-1">(max 5 chars, used in auto-generated SKUs)</span>
                    </label>
                    <input type="text"
                           wire:model="prefix"
                           placeholder="e.g. PLT"
                           maxlength="5"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    @if($prefix)
                    <p class="text-xs text-gray-400 mt-1">Products will be numbered like: <span class="font-mono font-semibold text-indigo-600">{{ strtoupper($prefix) }}-0001</span></p>
                    @endif
                    @error('prefix') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text"
                           wire:model="description"
                           placeholder="Brief description"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-gray-300 text-indigo-600"/>
                    <label for="is_active" class="text-sm text-gray-600">Active</label>
                </div>

            </div>

            <div class="flex gap-2 mt-6">
                <button wire:click="closeForm"
                        class="flex-1 py-2 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="save"
                        class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">
                    {{ $editingId ? 'Save changes' : 'Create category' }}
                </button>
            </div>

        </div>
    </div>
    @endif

</div>
