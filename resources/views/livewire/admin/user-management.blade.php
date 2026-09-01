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
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Joined</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition {{ !$user->is_active ? 'opacity-60' : '' }}">
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
                    <td class="px-4 py-3">
                        @if($user->is_active)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Inactive
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">
                        {{ $user->created_at->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            @if($user->id !== auth()->id())
                            <button wire:click="openEdit({{ $user->id }})"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Edit</button>

                            @if($user->is_active)
                            <button wire:click="askConfirm('deactivate', {{ $user->id }}, '{{ $user->name }}')"
                                    class="text-xs text-amber-500 hover:text-amber-700 font-medium">Deactivate</button>
                            @else
                            <button wire:click="askConfirm('reactivate', {{ $user->id }}, '{{ $user->name }}')"
                                    class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">Reactivate</button>
                            @endif

                            <button wire:click="askConfirm('delete', {{ $user->id }}, '{{ $user->name }}')"
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
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">No users found.</td>
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

    {{-- ===================================================== --}}
    {{-- In-app confirmation modal (replaces browser confirm()) --}}
    {{-- ===================================================== --}}
    @if($showConfirm)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">

            @php
                $styles = [
                    'delete'      => ['icon' => 'red',    'btn' => 'bg-red-600 hover:bg-red-700',       'label' => 'Delete'],
                    'deactivate'  => ['icon' => 'amber',  'btn' => 'bg-amber-500 hover:bg-amber-600',   'label' => 'Deactivate'],
                    'reactivate'  => ['icon' => 'emerald','btn' => 'bg-emerald-600 hover:bg-emerald-700','label' => 'Reactivate'],
                ];
                $style = $styles[$confirmAction] ?? $styles['delete'];
            @endphp

            <div class="flex items-start gap-3 mb-2">
                <span class="w-9 h-9 rounded-full bg-{{ $style['icon'] }}-50 flex items-center justify-center flex-shrink-0">
                    @if($confirmAction === 'delete')
                    <svg class="w-4.5 h-4.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    @elseif($confirmAction === 'deactivate')
                    <svg class="w-4.5 h-4.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 105.636 5.636a9 9 0 0012.728 12.728zM12 8v4m0 4h.01"/>
                    </svg>
                    @else
                    <svg class="w-4.5 h-4.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @endif
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">
                        {{ $style['label'] }} {{ $confirmUserName }}?
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        @if($confirmAction === 'delete')
                            If they have sales or stock history, they'll be deactivated instead of permanently deleted.
                        @elseif($confirmAction === 'deactivate')
                            They won't be able to log in, but their records stay intact. You can reactivate them anytime.
                        @else
                            They'll be able to log in again immediately.
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <button wire:click="cancelConfirm"
                        class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button wire:click="confirmProceed"
                        class="flex-1 py-2.5 text-white font-semibold rounded-xl text-sm transition {{ $style['btn'] }}">
                    {{ $style['label'] }}
                </button>
            </div>

        </div>
    </div>
    @endif

</div>
