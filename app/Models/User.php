<?php

namespace App\Models;

use Core\Database\Database;
use PDO;

class User
{
    public int $id;
    public string $name;
    public string $email;
    public string $password;
    public string $role;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->role = $data['role'] ?? 'user';
    }


    public static function attempt(array $credentials): ?self
    {
        $result = null;

        try {
            if (self::hasValidCredentials($credentials)) {
                $user = self::findUserByEmail($credentials['email']);

                if ($user && password_verify($credentials['password'], $user['password'])) {
                    error_log(json_encode([
                        'email' => $credentials['email'],
                        'encontrado' => true,
                        'senha_valida' => true
                    ]));
                    $result = new self($user);
                }
            }
        } catch (\Exception $e) {
            error_log("Erro crítico no login: " . $e->getMessage());
        }

        return $result;
    }

    private static function hasValidCredentials(array $credentials): bool
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            error_log("Erro: Credenciais incompletas");
            return false;
        }
        return true;
    }

    public static function findUserByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findById(int $id): ?self
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new self($data);
        }

        return null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function current(): ?self
    {
        if (self::check()) {
            return self::findById($_SESSION['user_id']);
        }
        return null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public static function login(self $user): void
    {
        $_SESSION['user_id'] = $user->id;
    }

    public static function logout(): void
    {
        unset($_SESSION['user_id']);
    }

    public static function all(): array
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM users";
        $stmt = $db->query($query);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($user) {
            return new self($user);
        }, $users);
    }
}
