<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    // Profile info
    public string $name = '';
    public string $email = '';

    // Password change
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Delete account confirmation
    public bool   $showDeleteModal = false;
    public string $deletePassword = '';

    public function mount(): void
    {
        $this->name  = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function render()
    {
        return view('livewire.profile')
            ->layout('layouts.admin', ['title' => 'My Profile']);
    }

    public function updateProfile(): void
    {
        $user = auth()->user();

        $this->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update([
            'name'  => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('success', 'Profile updated.');
    }

    public function updatePassword(): void
    {
        $user = auth()->user();

        $this->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        $user->update(['password' => Hash::make($this->password)]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('success', 'Password changed.');
    }

    public function confirmDelete(): void
    {
        $this->deletePassword = '';
        $this->showDeleteModal = true;
    }

    public function deleteAccount(): void
    {
        $user = auth()->user();

        // Never allow the last remaining admin to delete themselves
        if ($user->hasRole('admin') && \App\Models\User::role('admin')->count() <= 1) {
            $this->addError('deletePassword', 'You are the only admin — cannot delete this account.');
            return;
        }

        $this->validate(['deletePassword' => 'required']);

        if (!Hash::check($this->deletePassword, $user->password)) {
            $this->addError('deletePassword', 'Password is incorrect.');
            return;
        }

        Auth::logout();
        $user->delete();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/', navigate: false);
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletePassword  = '';
    }
}
