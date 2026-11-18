
<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Create an account')"
            :description="__('Enter your details below to create your account')"
        />

        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Full name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Your name"
            />

            <!-- Email -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Password"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Confirm password"
                viewable
            />

            <!-- Register Button -->
            <div class="flex items-center justify-end">
                <flux:button
                    variant="primary"
                    type="submit"
                    class="w-full"
                    data-test="register-button"
                >
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <!-- Login Link -->
        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Already have an account?') }}

            <flux:link :href="route('login')" wire:navigate>
                {{ __('Log in') }}
            </flux:link>
        </div>
    </div>
</x-layouts::auth>
