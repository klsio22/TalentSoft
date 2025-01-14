<?php

namespace App\Models;

use Core\Database\Database;
use Lib\FlashMessage;
use PDO;

class User
{
    public int $id;
    public string $username;
    public string $email;
    public string $password;
    public string $role;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->role = $data['role'] ?? 'user';
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

    public static function findByEmail(string $email): ?self
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
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

    public function save(): bool
    {
        $database = Database::getInstance();

        if ($this->id === 0) {
            $insertQuery = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
            $statement = $database->prepare($insertQuery);
            $parameters = [
                ':name' => $this->username,
                ':email' => $this->email,
                ':password' => password_hash($this->password, PASSWORD_DEFAULT),
                ':role' => $this->role
            ];
        } else {
            $updateQuery = "UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id";
            $statement = $database->prepare($updateQuery);
            $parameters = [
                ':name' => $this->username,
                ':email' => $this->email,
                ':role' => $this->role,
                ':id' => $this->id
            ];
        }

        return $statement->execute($parameters);
    }
    public static function create(array $data): ?self
    {
        if (self::findByEmail($data['email'])) {
            FlashMessage::danger('Email já cadastrado.');
            return null;
        }

        $user = new self($data);
        FlashMessage::success('Usuário cadastrado com sucesso.');
        return $user->save() ? $user : null;
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
