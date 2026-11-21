<x-spa.layout title="Add Employee" subtitle="Register a new spa employee">
    <div class="space-y-6">
        <flux:button icon="arrow-left" :href="route('employees.index')">Back to Employee Records</flux:button>

        <livewire:employees.create />
    </div>
</x-spa.layout>