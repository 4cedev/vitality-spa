<x-spa.layout title="Dashboard" subtitle="Business overview">
    <flux:callout icon="sparkles" heading="Welcome, {{ auth()->user()->name }}">
        You are signed in as {{ auth()->user()->role->label() }}. Dashboard widgets will appear here.
    </flux:callout>
</x-spa.layout>