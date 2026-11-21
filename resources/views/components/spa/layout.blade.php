@props(['title' => null, 'subtitle' => null])

@php
    $isAdmin = auth()->user()->isAdmin();

    // [label, icon, route name, admin only?]
    $menu = [
        'Operations' => [
            ['Dashboard', 'home', 'dashboard', false],
            ['Transactions', 'clipboard-document-list', 'transactions.index', false],
            ['Session Timer', 'clock', 'sessions.index', false],
        ],
        'Records' => [
            ['Services', 'sparkles', 'services.index', false],
            ['Employees', 'users', 'employees.index', true],
        ],
        'Finance' => [
            ['Expenses', 'wallet', 'expenses.index', false],
            ['Salary', 'banknotes', 'salaries.index', true],
            ['Reports', 'chart-bar', 'reports.index', true],
        ],
        'Administration' => [
            ['Users', 'user-group', 'users.index', true],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">

    <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <flux:sidebar.brand href="{{ route('dashboard') }}" name="Vitality Spa" />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            @foreach ($menu as $group => $items)
                @continue(! $isAdmin && collect($items)->every(fn ($item) => $item[3]))
                <flux:sidebar.group :heading="$group" class="grid">
                    @foreach ($items as [$label, $icon, $route, $adminOnly])
                        @continue($adminOnly && ! $isAdmin)

                        @php $exists = Route::has($route); @endphp

                        <flux:sidebar.item
                            :icon="$icon"
                            :href="$exists ? route($route) : '#'"
                            :current="$exists && request()->routeIs($route, Str::before($route, '.').'.*')"
                            :badge="$exists ? null : 'Soon'"
                        >
                            {{ $label }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>
            @endforeach
        </flux:sidebar.nav>

        <flux:spacer />

        <x-spa.user-menu />
    </flux:sidebar>

    {{-- Mobile top bar --}}
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:spacer />
        <span class="text-sm font-medium">{{ $title }}</span>
    </flux:header>

    <flux:main>
        <div class="mb-6">
            <flux:heading size="xl" level="1">{{ $title }}</flux:heading>
            @if ($subtitle)
                <flux:subheading>{{ $subtitle }}</flux:subheading>
            @endif
        </div>

        {{ $slot }}
    </flux:main>

    @fluxScripts
</body>
</html>