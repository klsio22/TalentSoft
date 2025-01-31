<?php

namespace Database\Seeds;

use Core\Database\Database;
use PDOException;

class DatabaseSeeder
{
  public static function migrate(): void
  {
    try {
      Database::migrate();
      echo "Migração executada com sucesso!\n";
    } catch (PDOException $e) {
      die("Erro na migração: " . $e->getMessage() . "\n");
    }
  }

  public static function seed(): void
  {
    $db = Database::getInstance();

    // Roles
    $roles = [
      ['name' => 'Super User', 'description' => 'Acesso total ao sistema'],
      ['name' => 'RH Admin', 'description' => 'Administração de RH'],
      ['name' => 'Employee', 'description' => 'Funcionário padrão']
    ];

    foreach ($roles as $role) {
      $query = "INSERT INTO roles (name, description) VALUES (:name, :description)";
      $stmt = $db->prepare($query);
      $stmt->execute([
        ':name' => $role['name'],
        ':description' => $role['description']
      ]);
    }

    // Employees
    $employees = [
      [
        'name' => 'Super Admin',
        'cpf' => '123.456.789-00',
        'email' => 'super@example.com',
        'password' => password_hash('super123', PASSWORD_DEFAULT),
        'role_id' => 1,
        'hire_date' => '2024-01-01'
      ],
      [
        'name' => 'RH Manager',
        'cpf' => '987.654.321-00',
        'email' => 'rh@example.com',
        'password' => password_hash('rh123', PASSWORD_DEFAULT),
        'role_id' => 2,
        'hire_date' => '2024-01-01'
      ]
    ];

    foreach ($employees as $employee) {
      $query = "INSERT INTO employees (name, cpf, email, password, role_id, hire_date)
                 VALUES (:name, :cpf, :email, :password, :role_id, :hire_date)";
      $stmt = $db->prepare($query);
      $stmt->execute([
        ':name' => $employee['name'],
        ':cpf' => $employee['cpf'],
        ':email' => $employee['email'],
        ':password' => $employee['password'],
        ':role_id' => $employee['role_id'],
        ':hire_date' => $employee['hire_date']
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
