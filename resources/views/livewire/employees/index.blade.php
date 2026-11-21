<div class="space-y-6">
    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
    @endif

    <div class="flex items-center justify-between gap-4">
        <flux:text>
            @if ($this->filtersActive)
                Showing {{ $this->employees->total() }} of {{ $this->totalEmployees }}
                {{ Str::plural('employee', $this->totalEmployees) }}
            @else
                {{ $this->totalEmployees }} {{ Str::plural('employee', $this->totalEmployees) }} registered
            @endif
        </flux:text>

        @if (Route::has('employees.create'))
            <flux:button variant="primary" icon="plus" :href="route('employees.create')">Add employee</flux:button>
        @endif
    </div>

    @if ($this->totalEmployees > 0)
        <div class="flex flex-wrap items-end gap-3">
            <div class="w-full sm:w-80">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="Search by name or Employee ID"
                    aria-label="Search employees"
                />
            </div>

            <div class="w-full sm:w-56">
                <flux:select wire:model.live="jobTitle" aria-label="Filter by job title">
                    <flux:select.option value="">All job titles</flux:select.option>
                    @foreach ($this->jobTitles as $title)
                        <flux:select.option :value="$title">{{ $title }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            @if ($this->filtersActive)
                <flux:button icon="x-mark" wire:click="resetFilters">Reset</flux:button>
            @endif
        </div>
    @endif

    @if ($this->employees->isEmpty())
        @if ($this->filtersActive)
            <div class="rounded-xl border border-dashed border-zinc-300 px-6 py-16 text-center dark:border-zinc-600">
                <flux:icon name="magnifying-glass" class="mx-auto mb-3 size-10 text-zinc-400" />
                <flux:heading size="lg">No matching employees</flux:heading>
                <flux:text class="mt-1">Try a different name, Employee ID or job title.</flux:text>
                <flux:button class="mt-4" icon="x-mark" wire:click="resetFilters">Reset search and filter</flux:button>
            </div>
        @else
            <div class="rounded-xl border border-dashed border-zinc-300 px-6 py-16 text-center dark:border-zinc-600">
                <flux:icon name="users" class="mx-auto mb-3 size-10 text-zinc-400" />
                <flux:heading size="lg">No employees yet</flux:heading>
                <flux:text class="mt-1">Employee records you add will appear here.</flux:text>

                @if (Route::has('employees.create'))
                    <flux:button variant="primary" icon="plus" class="mt-4" :href="route('employees.create')">
                        Add the first employee
                    </flux:button>
                @endif
            </div>
        @endif
    @else
        <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 text-left text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Employee ID</th>
                        <th class="px-4 py-3 font-medium">Full Name</th>
                        <th class="px-4 py-3 font-medium">Job Title</th>
                        <th class="px-4 py-3 font-medium">Contact Number</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->employees as $employee)
                        <tr wire:key="employee-{{ $employee->id }}" class="border-t border-zinc-200 dark:border-zinc-700">
                            <td class="px-4 py-3 font-medium">{{ $employee->employee_id }}</td>
                            <td class="px-4 py-3">{{ $employee->full_name }}</td>
                            <td class="px-4 py-3">{{ $employee->job_title }}</td>
                            <td class="px-4 py-3">{{ $employee->contact_number ?: '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <flux:button size="sm" icon="eye" :href="route('employees.show', $employee)">View</flux:button>
                                    <flux:button size="sm" icon="pencil-square" :href="route('employees.edit', $employee)">Edit</flux:button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <flux:pagination :paginator="$this->employees" />
    @endif
</div>