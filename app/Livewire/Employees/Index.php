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

    // Runs on every request to this component, including searches and pagination.
    public function boot(): void
    {
        Gate::authorize('admin');
    }

    // A new search or filter always starts again from page 1.
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedJobTitle(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'jobTitle']);
        $this->resetPage();
    }

    #[Computed]
    public function filtersActive(): bool
    {
        return trim($this->search) !== '' || $this->jobTitle !== '';
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
            ->orderBy('id')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.employees.index');
    }
}