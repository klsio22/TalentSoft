<?php

namespace App\Models;

class User
{
    public int $id;
    public string $email;
    public string $password;


    public static function attempt(array $credentials): ?self
    {
        if ($credentials['email'] === 'validUser@example.com' && $credentials['password'] === 'validPassword') {
            $user = new self();
            $user->id = 1; // Defina um ID fictício para o usuário
            $user->email = $credentials['email'];
            $user->password = $credentials['password'];
            return $user;
        }
        return null;
    }

    public static function findById(int $id): ?self
    {
        // Implementação fictícia para encontrar um usuário pelo ID
        if ($id === 1) {
            $user = new self();
            $user->id = 1;
            $user->email = 'validUser@example.com';
            $user->password = 'validPassword';
            return $user;
        }
        return null;
    }
}
