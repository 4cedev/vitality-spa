<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateOwner extends Command
{
    protected $signature = 'spa:create-owner';

    protected $description = 'Create an Administrator/Owner account';

    public function handle(): int
    {
        $name = text('Full name', required: true);

        $email = strtolower(text(
            'Email address',
            required: true,
            validate: function (string $value) {
                if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return 'Enter a valid email address.';
                }

                return User::where('email', strtolower($value))->exists()
                    ? 'That email is already in use.'
                    : null;
            },
        ));

        $pw = password(
            'Password (min 12 characters)',
            required: true,
            validate: function (string $value) {
                $errors = Validator::make(
                    ['password' => $value],
                    ['password' => [Password::min(12)->mixedCase()->numbers()->symbols()]],
                )->errors();

                return $errors->first('password') ?: null;
            },
        );

        password(
            'Confirm password',
            required: true,
            validate: fn (string $value) => $value === $pw ? null : 'Passwords do not match.',
        );

        (new User)->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => $pw,
            'role' => Role::Admin,
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->info("Owner account created for {$email}.");

        return self::SUCCESS;
    }
}