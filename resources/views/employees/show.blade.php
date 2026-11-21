@php
    // Shows a value, or a muted "Not provided" when it is empty. Values are escaped.
    $show = fn ($value) => filled($value)
        ? e($value)
        : '<span class="text-zinc-400">Not provided</span>';
@endphp

<x-spa.layout :title="$employee->full_name" :subtitle="$employee->employee_id">
    <div class="max-w-3xl space-y-6">
        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <flux:button icon="arrow-left" :href="route('employees.index')">Back to Employee Records</flux:button>

            <div class="flex flex-wrap items-center gap-3">
                <livewire:employees.status-toggle :employee="$employee" />
                <flux:button variant="primary" icon="pencil-square" :href="route('employees.edit', $employee)">Edit</flux:button>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <flux:heading size="lg" class="mb-4">Employment information</flux:heading>

            <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-zinc-500">Employee ID</dt>
                    <dd class="mt-1 font-medium">{{ $employee->employee_id }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Job title</dt>
                    <dd class="mt-1 font-medium">{!! $show($employee->job_title) !!}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Record added</dt>
                    <dd class="mt-1">{{ $employee->created_at->format('F j, Y, g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Last updated</dt>
                    <dd class="mt-1">{{ $employee->updated_at->format('F j, Y, g:i A') }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <flux:heading size="lg" class="mb-4">Personal information</flux:heading>

            <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-zinc-500">Full name</dt>
                    <dd class="mt-1 font-medium">{{ $employee->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Birthdate</dt>
                    <dd class="mt-1">
                        @if ($employee->birthdate)
                            {{ $employee->birthdate->format('F j, Y') }}
                            <span class="text-zinc-500">({{ $employee->birthdate->age }} years old)</span>
                        @else
                            <span class="text-zinc-400">Not provided</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Gender</dt>
                    <dd class="mt-1">{!! $show($employee->gender) !!}</dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Contact number</dt>
                    <dd class="mt-1">{!! $show($employee->contact_number) !!}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-zinc-500">Email address</dt>
                    <dd class="mt-1">
                        @if ($employee->email)
                            <a href="mailto:{{ $employee->email }}" class="underline">{{ $employee->email }}</a>
                        @else
                            <span class="text-zinc-400">Not provided</span>
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-zinc-500">Address</dt>
                    <dd class="mt-1 whitespace-pre-line">{!! $show($employee->address) !!}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-spa.layout>