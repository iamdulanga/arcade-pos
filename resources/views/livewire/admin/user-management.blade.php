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
               placeholder="Search name or email…"
               class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 w-64"/>

        <button wire:click="openCreate"
                class="flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New user
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">User</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Role</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Joined</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-sm font-bold text-indigo-600 flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-medium text-gray-800">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                    <span class="ml-1 text-xs text-gray-400">(you)</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @foreach($user->roles as $role)
                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full
                            {{ $role->name === 'admin'
                                ? 'bg-indigo-100 text-indigo-700'
                                : ($role->name === 'cashier'
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : 'bg-amber-50 text-amber-700') }}">
                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                        </span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">
                        {{ $user->created_at->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            @if($user->id !== auth()->id())
                            <button wire:click="openEdit({{ $user->id }})"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</button>
                            <button wire:click="delete({{ $user->id }})"
                                    wire:confirm="Delete {{ $user->name }}? This cannot be undone."
                                    class="text-xs text-red-400 hover:text-red-600 font-medium">Delete</button>
                            @else
                            <a href="{{ route('profile') }}"
                               class="text-xs text-gray-400 hover:text-indigo-600 font-medium">Edit profile</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-400">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    {{-- Role legend --}}
    <div class="mt-4 flex items-center gap-4 text-xs text-gray-400">
        <span class="font-medium text-gray-500">Roles:</span>
        <span><span class="inline-block w-2 h-2 rounded-full bg-indigo-500 mr-1"></span>Admin — full access</span>
        <span><span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span>Cashier — POS counter only</span>
        <span><span class="inline-block w-2 h-2 rounded-full bg-amber-400 mr-1"></span>Stock manager — inventory & products</span>
    </div>

    {{-- Create / Edit Modal --}}
    @if($showForm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">

            <h3 class="text-base font-semibold text-gray-800 mb-5">
                {{ $editingId ? 'Edit user' : 'New user' }}
            </h3>

            <div class="space-y-4">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Full name <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="name" placeholder="e.g. Nimal Silva"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-400">*</span></label>
                    <input type="email" wire:model="email" placeholder="user@prasadtech.lk"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Password <span class="text-red-400">*</span>
                        @if($editingId)
                        <span class="text-gray-400 font-normal ml-1">(leave blank to keep current)</span>
                        @endif
                    </label>
                    <input type="password" wire:model="password" placeholder="Min. 8 characters"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                    @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Confirm password</label>
                    <input type="password" wire:model="password_confirmation" placeholder="Repeat password"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Role <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['admin' => 'Admin', 'cashier' => 'Cashier', 'stock_manager' => 'Stock Manager'] as $value => $label)
                        <button wire:click="$set('role', '{{ $value }}')"
                                class="py-2 px-3 rounded-lg border text-xs font-medium transition text-center
                                    {{ $role === $value
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">
                        @if($role === 'admin') Full access to all features.
                        @elseif($role === 'cashier') POS terminal access only.
                        @else Inventory and product management only.
                        @endif
                    </p>
                    @error('role') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

            </div>

            <div class="flex gap-2 mt-6">
                <button wire:click="closeForm"
                        class="flex-1 py-2 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
                <button wire:click="save"
                        class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-sm transition">
                    {{ $editingId ? 'Save changes' : 'Create user' }}
                </button>
            </div>

        </div>
    </div>
    @endif

</div>
