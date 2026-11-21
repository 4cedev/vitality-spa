<div class="space-y-6">
    @if ($status)
        <flux:callout variant="success" icon="check-circle" :heading="$status" />
    @endif
    @if ($error)
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="$error" />
    @endif

    <div class="flex items-center justify-between gap-4">
        <div class="w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search name or email" />
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">Add user</flux:button>
    </div>

    @if ($showForm)
        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <flux:heading size="lg" class="mb-4">{{ $editingId ? 'Edit user' : 'New user' }}</flux:heading>

            <form wire:submit="save" class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="name" label="Full name" required />
                <flux:input wire:model="email" type="email" label="Email address" required />

                <flux:select wire:model="role" label="Role" :disabled="$editingId === auth()->id()">
                    @foreach (\App\Enums\Role::cases() as $case)
                        <flux:select.option :value="$case->value">{{ $case->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="password"
                    type="password"
                    viewable
                    :label="$editingId ? 'New password (leave blank to keep current)' : 'Temporary password'"
                />

                <div class="flex gap-2 md:col-span-2">
                    <flux:button type="submit" variant="primary">Save</flux:button>
                    <flux:button type="button" wire:click="cancel">Cancel</flux:button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 text-left text-zinc-600 dark:bg-zinc-900 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Name</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="px-4 py-3">
                            {{ $user->name }}
                            @if ($user->is(auth()->user()))
                                <span class="text-xs text-zinc-500">(you)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->role->label() }}</td>
                        <td class="px-4 py-3">
                            <flux:badge :color="$user->is_active ? 'green' : 'zinc'">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" wire:click="edit({{ $user->id }})">Edit</flux:button>

                                @unless ($user->is(auth()->user()))
                                    @if ($user->is_active)
                                        <flux:button size="sm" variant="danger"
                                            wire:click="toggleActive({{ $user->id }})"
                                            wire:confirm="Deactivate {{ $user->name }}? They will be signed out immediately.">
                                            Deactivate
                                        </flux:button>
                                    @else
                                        <flux:button size="sm" wire:click="toggleActive({{ $user->id }})">Reactivate</flux:button>
                                    @endif
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-zinc-500">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>