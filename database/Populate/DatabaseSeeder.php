<?php

namespace Database\Seeds;

use Core\Database\Database;

class DatabaseSeeder
{
    public static function migrate(): void
    {
        Database::migrate();
    }

    public static function seed(): void
    {
        $db = Database::getInstance();

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
            ],
            [
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'password' => password_hash('user123', PASSWORD_DEFAULT),
                'role' => 'user',
            ],
        ];

        foreach ($users as $user) {
            $query = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':name' => $user['name'],
                ':email' => $user['email'],
                ':password' => $user['password'],
                ':role' => $user['role'],
            ]);
        }

        echo "Banco de dados populado com sucesso!\n";
    }

    public static function run(): void
    {
        self::migrate();
        self::seed();
    }
}
