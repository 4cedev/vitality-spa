<?php

namespace App\Livewire\Users;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';
    public bool $showForm = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $email = '';
    public string $role = 'front_desk';
    public string $password = '';

    public ?string $status = null;
    public ?string $error = null;

    // Runs on EVERY request to this component, including button clicks,
    // so Front Desk cannot call any action even by forging requests.
    public function boot(): void
    {
        Gate::authorize('admin');
    }

    #[Computed]
    public function users()
    {
        $term = trim($this->search);

        return User::query()
            ->when($term !== '', fn ($q) => $q->where(
                fn ($q) => $q->where('name', 'like', "%{$term}%")
                             ->orWhere('email', 'like', "%{$term}%")
            ))
            ->orderBy('name')
            ->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->resetForm();
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->showForm = true;
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function save(): void
    {
        $this->email = Str::lower(trim($this->email));

        $user = $this->editingId ? User::findOrFail($this->editingId) : null;

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => [$user ? 'nullable' : 'required', Password::defaults()],
        ]);

        if ($user) {
            $user->name = $this->name;
            $user->email = $this->email;

            // Nobody can change their own role.
            if (! $user->is(auth()->user())) {
                $user->role = Role::from($this->role);
            }

            if ($this->password !== '') {
                $user->password = $this->password; // hashed by the model cast
            }

            $user->save();
            $this->status = "{$user->name} was updated.";
        } else {
            $user = new User;
            $user->forceFill([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'role' => Role::from($this->role),
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();

            $this->status = "{$user->name} was added.";
        }

        $this->error = null;
        $this->resetForm();
        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->is(auth()->user())) {
            $this->status = null;
            $this->error = 'You cannot deactivate your own account.';
            return;
        }

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        $this->error = null;
        $this->status = $user->is_active
            ? "{$user->name} was reactivated."
            : "{$user->name} was deactivated.";
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password']);
        $this->role = 'front_desk';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.users.index');
    }
}