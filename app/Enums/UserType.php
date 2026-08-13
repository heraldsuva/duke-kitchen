<?php

namespace App\Enums;

enum UserType: string
{
    case SuperAdmin = 'SuperAdmin';
    case Admin = 'Admin';

    public function label(): string
    {
        return $this->value;
    }
}
