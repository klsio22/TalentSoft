<?php

namespace App\Models;

use Core\Database\Database;
use PDO;

class User
{
    public int $id;
    public string $username;
    public string $password;
    public string $role;


    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->username = $data['username'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->role = $data['role'] ?? '';
    }

    public static function attempt(array $credentials): ?self
    {
        $db = Database::getInstance();
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->execute([':email' => $credentials['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($credentials['password'], $user['password'])) {
            return new self($user);
        }

        return null;
    }

    public static function findById(int $id): ?self
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log(print_r($data, true));
        if ($data) {
            return new self($data);
        }

        return null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }
}
