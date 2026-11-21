@php $user = auth()->user(); @endphp

<flux:dropdown position="top" align="start">
    <flux:profile
        :name="$user->name"
        :initials="Str::upper(Str::substr($user->name, 0, 2))"
        icon:trailing="chevrons-up-down"
    />

    <flux:menu class="w-60">
        <div class="px-2 py-1.5">
            <div class="text-sm font-medium">{{ $user->name }}</div>
            <div class="text-xs text-zinc-500">{{ $user->role->label() }}</div>
        </div>

        <flux:menu.separator />

        @if (Route::has('profile.edit'))
            <flux:menu.item :href="route('profile.edit')" icon="cog">Settings</flux:menu.item>
            <flux:menu.separator />
        @endif

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                Log out
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>