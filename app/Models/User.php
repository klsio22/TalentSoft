<?php

namespace App\Models;

class User
{
    public static function attempt(array $credentials): ?self
    {
        if ($credentials['username'] === 'validUser' && $credentials['password'] === 'validPassword') {
            return new self();
        }
        return null;
    }
}
