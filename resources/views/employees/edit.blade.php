<x-spa.layout :title="'Edit '.$employee->full_name" :subtitle="$employee->employee_id">
    <div class="space-y-4">
        <flux:button icon="arrow-left" :href="route('employees.index')">Back to Employee Records</flux:button>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <flux:text>The edit form is built in Task 5.</flux:text>
        </div>
    </div>
</x-spa.layout>