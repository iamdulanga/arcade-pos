<?php

namespace App\Livewire\Admin;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class Suppliers extends Component
{
    use WithPagination;

    public string $search = '';

    // Form fields
    public string $name = '';
    public string $contact_person = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public bool   $is_active = true;

    public ?int $editingId = null;
    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'is_active'      => 'boolean',
        ];
    }

    public function render()
    {
        $suppliers = Supplier::when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('contact_person', 'like', "%{$this->search}%")
            )
            ->withCount('products')
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.admin.suppliers', compact('suppliers'))
            ->layout('layouts.admin', ['title' => 'Suppliers']);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $this->editingId       = $id;
        $this->name            = $supplier->name;
        $this->contact_person  = $supplier->contact_person ?? '';
        $this->phone           = $supplier->phone ?? '';
        $this->email           = $supplier->email ?? '';
        $this->address         = $supplier->address ?? '';
        $this->is_active       = $supplier->is_active;
        $this->showForm        = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'           => $this->name,
            'contact_person' => $this->contact_person ?: null,
            'phone'          => $this->phone ?: null,
            'email'          => $this->email ?: null,
            'address'        => $this->address ?: null,
            'is_active'      => $this->is_active,
        ];

        if ($this->editingId) {
            Supplier::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Supplier updated.');
        } else {
            Supplier::create($data);
            session()->flash('success', 'Supplier created.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update(['is_active' => !$supplier->is_active]);
    }

    public function delete(int $id): void
    {
        $supplier = Supplier::withCount('products')->findOrFail($id);

        if ($supplier->products_count > 0) {
            session()->flash('error', 'Cannot delete — this supplier has products linked. Deactivate it instead.');
            return;
        }

        $supplier->delete();
        session()->flash('success', 'Supplier deleted.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'contact_person', 'phone', 'email', 'address', 'editingId']);
        $this->is_active = true;
    }

    public function updatedSearch(): void { $this->resetPage(); }
}
