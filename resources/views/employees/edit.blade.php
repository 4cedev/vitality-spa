<x-spa.layout :title="'Edit '.$employee->full_name" :subtitle="$employee->employee_id">
    <div class="space-y-6">
        <flux:button icon="arrow-left" :href="route('employees.show', $employee)">Back to employee details</flux:button>

        <livewire:employees.edit :employee="$employee" />
    </div>
</x-spa.layout>