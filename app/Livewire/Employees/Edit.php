<?php

namespace App\Livewire\Employees;

use App\Livewire\Employees\Concerns\ManagesEmployeeForm;
use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Edit extends Component
{
    use ManagesEmployeeForm;

    public Employee $employee;

    // Runs on every request to this component, so Front Desk cannot submit it either.
    public function boot(): void
    {
        Gate::authorize('admin');
    }

    public function mount(Employee $employee): void
    {
        $this->employee = $employee;

        $this->full_name = $employee->full_name;
        $this->job_title = $employee->job_title;
        $this->birthdate = $employee->birthdate?->format('Y-m-d');
        $this->gender = (string) $employee->gender;
        $this->contact_number = (string) $employee->contact_number;
        $this->email = (string) $employee->email;
        $this->address = (string) $employee->address;
    }

    public function save(): void
    {
        $data = $this->validatedEmployeeData();

        // employee_id is not fillable, so it can never change here.
        $this->employee->fill($data);
        $changed = $this->employee->isDirty();
        $this->employee->save();

        session()->flash('status', $changed
            ? "{$this->employee->full_name}'s record was updated."
            : "No changes were made to {$this->employee->full_name}'s record.");

        $this->redirectRoute('employees.show', $this->employee, navigate: true);
    }

    public function render()
    {
        return view('livewire.employees.edit');
    }
}