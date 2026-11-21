<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class StatusToggle extends Component
{
    public Employee $employee;

    // Runs on every request to this component, so Front Desk cannot call it.
    public function boot(): void
    {
        Gate::authorize('admin');
    }

    public function mount(Employee $employee): void
    {
        $this->employee = $employee;
    }

    public function toggle(): void
    {
        $this->employee->refresh();
        $this->employee->forceFill(['is_active' => ! $this->employee->is_active])->save();

        session()->flash('status', $this->employee->is_active
            ? "{$this->employee->full_name} was reactivated."
            : "{$this->employee->full_name} was deactivated.");

        $this->redirectRoute('employees.show', $this->employee, navigate: true);
    }

    public function render()
    {
        return view('livewire.employees.status-toggle');
    }
}