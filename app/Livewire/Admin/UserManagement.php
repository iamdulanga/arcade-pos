<?php

namespace App\Livewire\Admin;

use App\Models\InventoryAdjustment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination;

    public string $search = '';

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'cashier';

    public ?int $editingId = null;
    public bool $showForm = false;

    // In-app confirmation modal (replaces browser confirm())
    public bool   $showConfirm = false;
    public ?int   $confirmUserId = null;
    public string $confirmUserName = '';
    public string $confirmAction = ''; // 'delete' | 'deactivate' | 'reactivate'

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
    }

    protected function rules(): array
    {
        $passwordRule = $this->editingId
            ? 'nullable|string|min:8|confirmed'
            : 'required|string|min:8|confirmed';

        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . ($this->editingId ?? 'NULL'),
            'password' => $passwordRule,
            'role'     => 'required|in:admin,cashier,stock_manager',
        ];
    }

    public function render()
    {
        $users = User::when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->with('roles')
            ->orderBy('name')
            ->paginate(20);

        $roles = Role::orderBy('name')->get();

        return view('livewire.admin.user-management', compact('users', 'roles'))
            ->layout('layouts.admin', ['title' => 'User Management']);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Use your profile page to edit your own account.');
            return;
        }

        $user = User::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->role      = $user->roles->first()?->name ?? 'cashier';
        $this->password  = '';
        $this->password_confirmation = '';
        $this->showForm  = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);

            $data = [
                'name'  => $this->name,
                'email' => $this->email,
            ];

            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }

            $user->update($data);
            $user->syncRoles([$this->role]);
            session()->flash('success', 'User updated.');
        } else {
            $user = User::create([
                'name'     => $this->name,
                'email'    => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $user->assignRole($this->role);
            session()->flash('success', 'User created. They can now log in.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    // -----------------------------------------------------------
    // In-app confirmation modal flow
    // -----------------------------------------------------------

    /**
     * Open the confirmation modal for a destructive/state-changing action.
     * $action is 'delete', 'deactivate', or 'reactivate'.
     */
    public function askConfirm(string $action, int $id, string $name): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'You cannot do this to your own account.');
            return;
        }

        $this->confirmAction   = $action;
        $this->confirmUserId   = $id;
        $this->confirmUserName = $name;
        $this->showConfirm     = true;
    }

    public function cancelConfirm(): void
    {
        $this->showConfirm     = false;
        $this->confirmAction   = '';
        $this->confirmUserId   = null;
        $this->confirmUserName = '';
    }

    /**
     * Runs whichever action was staged by askConfirm().
     */
    public function confirmProceed(): void
    {
        $id = $this->confirmUserId;

        match ($this->confirmAction) {
            'delete'      => $this->performDelete($id),
            'deactivate',
            'reactivate'  => $this->performToggleActive($id),
            default       => null,
        };

        $this->cancelConfirm();
    }

    // -----------------------------------------------------------
    // Actual data operations (called only from confirmProceed)
    // -----------------------------------------------------------

    /**
     * Delete a user only if they have no sales or stock history.
     * Otherwise, deactivate them instead — this preserves audit trail
     * on every sale/stock adjustment they're linked to, and blocks
     * their login without erasing history.
     */
    private function performDelete(int $id): void
    {
        $user = User::findOrFail($id);

        $hasHistory = Sale::where('user_id', $id)->exists()
            || InventoryAdjustment::where('user_id', $id)->exists();

        if ($hasHistory) {
            $user->update(['is_active' => false]);
            session()->flash('success', "{$user->name} has sales/stock history, so they were deactivated instead of deleted. Their records are preserved and they can no longer log in.");
            return;
        }

        $user->delete();
        session()->flash('success', 'User deleted.');
    }

    private function performToggleActive(int $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        session()->flash('success', $user->is_active
            ? "{$user->name} reactivated. They can log in again."
            : "{$user->name} deactivated. They can no longer log in.");
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'email', 'password', 'password_confirmation', 'editingId']);
        $this->role = 'cashier';
    }

    public function updatedSearch(): void { $this->resetPage(); }
}
