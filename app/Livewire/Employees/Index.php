<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Runs on every request to this component, including pagination clicks.
    public function boot(): void
    {
        Gate::authorize('admin');
    }

    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->orderBy('id')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.employees.index');
    }
}