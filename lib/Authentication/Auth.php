<?php

namespace Lib\Authentication;

use App\Models\Employee;

class Auth
{
    public static function login($employee): void
    {
        $_SESSION['employee']['id'] = $employee->id;
    }

    public static function user(): ?Employee
    {
        if (isset($_SESSION['employee']['id'])) {
            $id = $_SESSION['employee']['id'];
            return Employee::findById($id);
        }

        return null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['employee']['id']) && self::user() !== null;
    }

    public static function logout(): void
    {
        unset($_SESSION['employee']['id']);
    }

    /**
     * Verifica se o usuário logado é admin
     */
    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && $user->isAdmin();
    }

    /**
     * Verifica se o usuário logado é de RH
     */
    public static function isHR(): bool
    {
        $user = self::user();
        return $user && $user->isHR();
    }

    /**
     * Verifica se o usuário logado é um usuário comum
     */
    public static function isUser(): bool
    {
        $user = self::user();
        return $user && $user->isUser();
    }
}
