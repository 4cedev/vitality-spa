<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'job', except: '')]
    public string $jobTitle = '';

    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    public ?string $notice = null;

    // Runs on every request to this component, including button clicks.
    public function boot(): void
    {
        Gate::authorize('admin');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedJobTitle(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'jobTitle', 'statusFilter']);
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $employee = Employee::findOrFail($id);

        $employee->forceFill(['is_active' => ! $employee->is_active])->save();

        $this->notice = $employee->is_active
            ? "{$employee->full_name} was reactivated."
            : "{$employee->full_name} was deactivated.";
    }

    #[Computed]
    public function filtersActive(): bool
    {
        return trim($this->search) !== '' || $this->jobTitle !== '' || $this->statusFilter !== '';
    }

    #[Computed]
    public function totalEmployees(): int
    {
        return Employee::count();
    }

    #[Computed]
    public function jobTitles()
    {
        return Employee::query()
            ->select('job_title')
            ->distinct()
            ->orderBy('job_title')
            ->pluck('job_title');
    }

    #[Computed]
    public function employees()
    {
        // Collapse repeated spaces, then escape LIKE wildcards so "%" and "_"
        // are searched for literally. "!" is the escape character.
        $term = trim(preg_replace('/\s+/', ' ', $this->search));
        $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

        return Employee::query()
            ->when($term !== '', fn ($query) => $query->where(
                fn ($query) => $query
                    ->whereRaw("full_name LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("employee_id LIKE ? ESCAPE '!'", [$like])
            ))
            ->when($this->jobTitle !== '', fn ($query) => $query->where('job_title', $this->jobTitle))
            ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('id')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.employees.index');
    }
}