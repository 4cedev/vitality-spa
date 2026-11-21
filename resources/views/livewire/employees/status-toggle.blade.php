<div class="flex items-center gap-3">
    <flux:badge :color="$employee->is_active ? 'green' : 'zinc'">
        {{ $employee->is_active ? 'Active' : 'Inactive' }}
    </flux:badge>

    @if ($employee->is_active)
        <flux:button variant="danger" icon="no-symbol" wire:click="toggle"
            wire:confirm="Deactivate {{ $employee->full_name }}? Their record and history are kept.">
            Deactivate
        </flux:button>
    @else
        <flux:button icon="arrow-path" wire:click="toggle">Reactivate</flux:button>
    @endif
</div>