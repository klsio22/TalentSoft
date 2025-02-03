<?php

namespace Database\Seeds;

use Core\Database\Database;

class RoleSeeder
{

  public static function run(): void
  {
    $db = Database::getInstance();
    $roles = [
      ['name' => 'admin', 'description' => 'Acesso total ao sistema'],
      ['name' => 'rh', 'description' => 'Administração de RH'],
      ['name' => 'user', 'description' => 'Funcionário padrão']
    ];

    foreach ($roles as $role) {
      $sql = "INSERT INTO roles (name, description) VALUES (:name, :description)";
      $stmt = $db->prepare($sql);
      $stmt->execute($role);
    }

    echo "Roles inseridas com sucesso!\n";
  }
}
