<div class="max-w-2xl space-y-5">

    @if(session('success'))
    <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    {{-- Profile info --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </span>
            <h2 class="text-base font-semibold text-gray-800">Profile information</h2>
        </div>
        <p class="text-sm text-gray-500 mb-5">Update your name and email address.</p>

        <form wire:submit="updateProfile" class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Full name</label>
                <input type="text" wire:model="name"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email address</label>
                <input type="email" wire:model="email"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition">
                Save changes
            </button>
        </form>
    </div>

    {{-- Change password --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </span>
            <h2 class="text-base font-semibold text-gray-800">Change password</h2>
        </div>
        <p class="text-sm text-gray-500 mb-5">Use a strong password you don't use elsewhere.</p>

        <form wire:submit="updatePassword" class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Current password</label>
                <input type="password" wire:model="current_password"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                @error('current_password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">New password</label>
                <input type="password" wire:model="password"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
                @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Confirm new password</label>
                <input type="password" wire:model="password_confirmation"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"/>
            </div>

            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition">
                Update password
            </button>
        </form>
    </div>

    {{-- Danger zone --}}
    <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-6">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </span>
            <h2 class="text-base font-semibold text-gray-800">Delete account</h2>
        </div>
        <p class="text-sm text-gray-500 mb-4">
            Once your account is deleted, this cannot be undone. You'll be logged out immediately.
        </p>

        <button wire:click="confirmDelete"
                class="px-5 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-xl transition">
            Delete my account
        </button>
    </div>

    {{-- Delete confirmation modal --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6">
            <h3 class="text-base font-semibold text-gray-800 mb-2">Are you sure?</h3>
            <p class="text-sm text-gray-500 mb-4">
                This will permanently delete your account. Enter your password to confirm.
            </p>

            <input type="password" wire:model="deletePassword" placeholder="Password"
                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 mb-1"/>
            @error('deletePassword') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

            <div class="flex gap-2 mt-4">
                <button wire:click="closeDeleteModal"
                        class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button wire:click="deleteAccount"
                        class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl text-sm transition">
                    Delete account
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
