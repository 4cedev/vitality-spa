<form wire:submit="save" class="max-w-3xl space-y-6">
    <div class="grid gap-4 md:grid-cols-2">
        <flux:input wire:model="full_name" label="Full name" required />

        <div>
            <flux:input wire:model="job_title" label="Job title" list="job-titles" placeholder="e.g. Therapist" required />
            <datalist id="job-titles">
                @foreach (\App\Livewire\Employees\Create::JOB_TITLES as $title)
                    <option value="{{ $title }}"></option>
                @endforeach
            </datalist>
        </div>

        <flux:input wire:model="birthdate" type="date" label="Birthdate" max="{{ now()->subDay()->format('Y-m-d') }}" />

        <flux:select wire:model="gender" label="Gender">
            <flux:select.option value="">Select…</flux:select.option>
            @foreach (\App\Livewire\Employees\Create::GENDERS as $gender)
                <flux:select.option :value="$gender">{{ $gender }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="contact_number" label="Contact number" placeholder="09XXXXXXXXX" />
        <flux:input wire:model="email" type="email" label="Email address" placeholder="name@example.com" />

        <div class="md:col-span-2">
            <flux:textarea wire:model="address" label="Address" rows="3" />
        </div>
    </div>

    <flux:text class="text-sm">The Employee ID is assigned automatically when you save.</flux:text>

    <div class="flex gap-2">
        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
            Save employee
        </flux:button>
        <flux:button :href="route('employees.index')">Cancel</flux:button>
    </div>
</form>