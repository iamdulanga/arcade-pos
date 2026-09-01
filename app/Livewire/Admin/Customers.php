<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class Customers extends Component
{
    use WithPagination;

    public string $search = '';

    // Form fields
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public bool   $is_active = true;

    public ?int $editingId = null;
    public bool $showForm = false;

    // Purchase history modal
    public bool   $showHistory = false;
    public ?int   $historyCustomerId = null;
    public string $historyCustomerName = '';

    protected function rules(): array
    {
        return [
            'name'    => 'required|string|max:30|unique:customers,name,' . ($this->editingId ?? 'NULL'),
            'phone'   => 'nullable|string|max:20|unique:customers,phone,' . ($this->editingId ?? 'NULL'),
            'email'   => 'nullable|email|max:255|unique:customers,email,' . ($this->editingId ?? 'NULL'),
            'address' => 'nullable|string|max:500',
        ];
    }

    public function render()
    {
        $customers = Customer::when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->withCount('sales')
            ->withSum(['sales' => fn($q) => $q->where('status', 'completed')], 'total_amount')
            ->orderBy('name')
            ->paginate(20);

        $history = [];
        if ($this->showHistory && $this->historyCustomerId) {
            $history = \App\Models\Sale::with(['items', 'payments'])
                ->where('customer_id', $this->historyCustomerId)
                ->where('status', 'completed')
                ->orderByDesc('sold_at')
                ->limit(15)
                ->get();
        }

        return view('livewire.admin.customers', compact('customers', 'history'))
            ->layout('layouts.admin', ['title' => 'Customers']);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $customer->name;
        $this->phone     = $customer->phone ?? '';
        $this->email     = $customer->email ?? '';
        $this->address   = $customer->address ?? '';
        $this->is_active = $customer->is_active;
        $this->showForm  = true;
    }

    public function openHistory(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $this->historyCustomerId   = $id;
        $this->historyCustomerName = $customer->name;
        $this->showHistory         = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'      => $this->name,
            'phone'     => $this->phone ?: null,
            'email'     => $this->email ?: null,
            'address'   => $this->address ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            Customer::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Customer updated.');
        } else {
            Customer::create($data);
            session()->flash('success', 'Customer created.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $customer = Customer::withCount('sales')->findOrFail($id);

        if ($customer->sales_count > 0) {
            session()->flash('error', 'Cannot delete — this customer has sales records.');
            return;
        }

        $customer->delete();
        session()->flash('success', 'Customer deleted.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function closeHistory(): void
    {
        $this->showHistory       = false;
        $this->historyCustomerId = null;
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'phone', 'email', 'address', 'editingId']);
        $this->is_active = true;
    }

    public function updatedSearch(): void { $this->resetPage(); }
}
