<div class="relative mb-6 w-full">
    <flux:heading size="xl" level="1">{{ __('Settings') }}</flux:heading>
    @if (session('password_notice'))
        <flux:callout variant="warning" icon="key" :heading="session('password_notice')" class="mb-4" />
    @endif
    <flux:subheading size="lg" class="mb-6">{{ __('Manage your profile and account settings') }}</flux:subheading>
    <flux:separator variant="subtle" />
</div>
