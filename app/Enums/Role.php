<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case FrontDesk = 'front_desk';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator / Owner',
            self::FrontDesk => 'Front Desk',
        };
    }
}