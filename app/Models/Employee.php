<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Employee extends Model
{
    use HasFactory;

    public const ID_PREFIX = 'EMP-';
    public const ID_MIN_DIGITS = 3;

    // employee_id is deliberately NOT fillable: it is always generated.
    protected $fillable = [
        'full_name',
        'job_title',
        'birthdate',
        'gender',
        'contact_number',
        'email',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Employee $employee) {
            if (blank($employee->employee_id)) {
                $employee->employee_id = static::nextEmployeeId();
            }
        });
    }

    /**
     * EMP-001, EMP-002, ... Grows past three digits on its own (EMP-1000).
     */
    public static function nextEmployeeId(): string
    {
        $last = static::query()->orderByDesc('id')->value('employee_id');

        $number = $last ? (int) Str::after($last, self::ID_PREFIX) : 0;

        return self::ID_PREFIX
            .str_pad((string) ($number + 1), self::ID_MIN_DIGITS, '0', STR_PAD_LEFT);
    }
}