<?php

namespace App\Livewire\Admin;

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

    protected function rules(): array
    {
        $passwordRule = $this->editingId
            ? 'nullable|string|min:8|confirmed'
            : 'required|string|min:8|confirmed';

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($this->editingId ?? 'NULL'),
            'password' => $passwordRule,
            'role' => 'required|in:admin,cashier,stock_manager',
        ];
    }

    public function render()
    {
        $users = User::when(
            $this->search,
            fn($q) =>
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
        // Prevent editing yourself
        if ($id === auth()->id()) {
            session()->flash('error', 'Use your profile page to edit your own account.');
            return;
        }

        $user = User::findOrFail($id);
        $this->editingId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()?->name ?? 'cashier';
        $this->password = '';
        $this->password_confirmation = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);

            $data = [
                'name' => $this->name,
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
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $user->assignRole($this->role);
            session()->flash('success', 'User created. They can now log in.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            return;
        }

        User::findOrFail($id)->delete();
        session()->flash('success', 'User deleted.');
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

    public function mount(): void
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
}
