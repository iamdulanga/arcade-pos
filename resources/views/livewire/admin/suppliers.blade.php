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
               placeholder="Search supplier, contact, phone…"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-64"/>

        <button wire:click="openCreate"
                class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New supplier
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Supplier</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Contact person</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Phone</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Products</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($suppliers as $supplier)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $supplier->name }}</div>
                        @if($supplier->address)
                        <div class="text-xs text-gray-400 truncate max-w-xs">{{ $supplier->address }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $supplier->contact_person ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $supplier->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $supplier->email ?? '—' }}</td>
                    <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $supplier->products_count }}</td>
                    <td class="px-4 py-3">
                        <button wire:click="toggleActive({{ $supplier->id }})"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium transition
                                       {{ $supplier->is_active
                                           ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                           : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $supplier->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <button wire:click="openEdit({{ $supplier->id }})"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</button>
                            <button wire:click="delete({{ $supplier->id }})"
                                    wire:confirm="Delete {{ $supplier->name }}?"
                                    class="text-xs text-red-400 hover:text-red-600 font-medium">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">
                        No suppliers yet. Add your first supplier.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($suppliers->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">

            <h3 class="text-base font-semibold text-gray-800 mb-5">
                {{ $editingId ? 'Edit supplier' : 'New supplier' }}
            </h3>

            <div class="space-y-4">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Supplier name <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="name" placeholder="e.g. Vijitha Yapa Publications"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Contact person</label>
                    <input type="text" wire:model="contact_person" placeholder="e.g. Kamal Perera"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                        <input type="text" wire:model="phone" placeholder="07X-XXXXXXX"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" wire:model="email" placeholder="supplier@example.com"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                        @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                    <textarea wire:model="address" rows="2" placeholder="Supplier address…"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="is_active" id="sup_is_active"
                           class="rounded border-gray-300 text-indigo-600"/>
                    <label for="sup_is_active" class="text-sm text-gray-600">Active</label>
                </div>

            </div>

            <div class="flex gap-2 mt-6">
                <button wire:click="closeForm"
                        class="flex-1 py-2 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="save"
                        class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">
                    {{ $editingId ? 'Save changes' : 'Create supplier' }}
                </button>
            </div>

        </div>
    </div>
    @endif

</div>
