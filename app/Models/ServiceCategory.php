<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    use HasFactory;

    public const ID_PREFIX = 'SC-';
    public const ID_MIN_DIGITS = 3;

    protected $table = 'service_categories';

    // category_id is deliberately NOT fillable: it is always generated.
    protected $fillable = [
        'category_name',
    ];

    protected static function booted(): void
    {
        static::creating(function (ServiceCategory $category) {
            if (blank($category->category_id)) {
                $category->category_id = static::nextCategoryId();
            }
        });
    }

    /**
     * SC-001, SC-002, ... Grows past three digits on its own (SC-1000).
     */
    public static function nextCategoryId(): string
    {
        $last = static::query()->orderByDesc('id')->value('category_id');

        $number = $last ? (int) Str::after($last, self::ID_PREFIX) : 0;

        return self::ID_PREFIX
            .str_pad((string) ($number + 1), self::ID_MIN_DIGITS, '0', STR_PAD_LEFT);
    }
}