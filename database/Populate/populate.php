<?php
// Realiza a migração (criação das tabelas)

require __DIR__ . '/../../config/bootstrap.php';

use Core\Database\Database;

Database::migrate();

// Conecta ao banco de dados
$db = Database::getInstance();

// Dados para popular o banco
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

// Insere os usuários no banco
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
