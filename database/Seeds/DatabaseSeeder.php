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

  private static function cleanTables(): void
  {
    $db = Database::getInstance();

    // Desabilita verificação de chave estrangeira
    $db->exec('SET FOREIGN_KEY_CHECKS = 0');

    // Limpa tabelas na ordem correta
    $tables = [
      'employees_projects',
      'notifications',
      'approvals',
      'projects_employees_report',
      'employees',
      'roles',
      'projects'
    ];

    foreach ($tables as $table) {
      $db->exec("TRUNCATE TABLE {$table}");
    }

    // Reabilita verificação
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');
  }

  public static function run(): void
  {
    self::migrate();
    self::cleanTables();

    // Chama os seeders individuais
    RoleSeeder::run(); // Inserindo roles
    EmployeeSeeder::run(); // Inserindo employees
  }
}
