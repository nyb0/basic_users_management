<?php

namespace App\Enums;

enum UserRoles: string
{
    case ADMIN = 'admin';
    case MODERATOR = 'moderator';
    case USER = 'user';


    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function authSearchCases(): array
    {
        if (! auth()->user()->isAdmin()) {
            return collect(self::cases())
                ->where('value', '!=', self::ADMIN->value)
                ->pluck('value')
                ->toArray();
        }
        return self::toArray();
    }

    public static function authEditCases(): array
    {
        if (! auth()->user()->isAdmin()) {
            return collect(self::cases())
                ->where('value', '=', self::USER->value)
                ->pluck('value')
                ->toArray();
        }
        return self::toArray();
    }
}
