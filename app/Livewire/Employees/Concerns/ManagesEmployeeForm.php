<?php

namespace App\Livewire\Employees\Concerns;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

trait ManagesEmployeeForm
{
    public const GENDERS = ['Male', 'Female', 'Other'];

    public const JOB_TITLES = ['Therapist', 'Nail Technician', 'Front Desk Staff'];

    public string $full_name = '';
    public string $job_title = '';
    public ?string $birthdate = null;
    public string $gender = '';
    public string $contact_number = '';
    public string $email = '';
    public string $address = '';

    protected function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'job_title' => ['required', 'string', 'max:100'],
            'birthdate' => ['nullable', 'date_format:Y-m-d', 'before:today', 'after:1900-01-01'],
            'gender' => ['nullable', Rule::in(self::GENDERS)],
            'contact_number' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,30}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'full_name.required' => 'Please enter the employee\'s full name.',
            'job_title.required' => 'Please enter the employee\'s job title.',
            'birthdate.date_format' => 'Enter a valid date.',
            'birthdate.before' => 'Birthdate must be a date in the past.',
            'birthdate.after' => 'Enter a valid birthdate.',
            'contact_number.regex' => 'Enter a valid phone number, for example 09171234567.',
            'email.email' => 'Enter a valid email address, for example name@example.com.',
        ];
    }

    /**
     * Trim, validate and normalise the form. Optional fields left empty become
     * NULL, and the email is lowercased.
     */
    protected function validatedEmployeeData(): array
    {
        foreach (['full_name', 'job_title', 'gender', 'contact_number', 'email', 'address'] as $field) {
            $this->$field = trim($this->$field);
        }

        $data = collect($this->validate())
            ->map(fn ($value) => is_string($value) && $value === '' ? null : $value)
            ->all();

        $data['email'] = $data['email'] ? Str::lower($data['email']) : null;

        return $data;
    }
}