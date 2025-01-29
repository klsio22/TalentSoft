<?php

namespace Core\Database;

use Core\Constants\Constants;
use PDO;

use PDOException;
use Exception;

class Database
{
  private static ?PDO $instance = null;


  private function __construct() {} // Construtor privado


  public static function getDatabaseConn(): PDO
  {
    $user = $_ENV['DB_USERNAME'];
    $pwd  = $_ENV['DB_PASSWORD'];
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $db   = $_ENV['DB_DATABASE'];

    $pdo = new PDO('mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db, $user, $pwd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
  }

  public static function getConn(): PDO
  {
    $user = $_ENV['DB_USERNAME'];
    $pwd  = $_ENV['DB_PASSWORD'];
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];

    $pdo = new PDO('mysql:host=' . $host . ';port=' . $port, $user, $pwd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
  }


  public static function getInstance(): PDO
  {
    if (self::$instance === null) {
      $user = $_ENV['DB_USERNAME'];
      $pwd  = $_ENV['DB_PASSWORD'];
      $host = $_ENV['DB_HOST'];
      $port = $_ENV['DB_PORT'];
      $db   = $_ENV['DB_DATABASE'];

      try {
        self::$instance = new PDO(
          "mysql:host={$host};port={$port};dbname={$db}",
          $user,
          $pwd,
          [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
      } catch (PDOException $e) {
        throw new Exception("Erro de conexão: " . $e->getMessage());
      }
    }

    return self::$instance;
  }


  public static function create(): void
  {
    $sql = 'CREATE DATABASE IF NOT EXISTS ' . $_ENV['DB_DATABASE'] . ';';
    self::getConn()->exec($sql);
  }

  public static function drop(): void
  {
    $sql = 'DROP DATABASE IF EXISTS ' . $_ENV['DB_DATABASE'] . ';';
    self::getConn()->exec($sql);
  }

  public static function migrate(): void
  {
    $sql = file_get_contents(Constants::databasePath()->join('schema.sql'));
    self::getDatabaseConn()->exec($sql);
  }

  public static function execute(string $sql, array $params = []): bool
  {
    try {
      $stmt = self::getInstance()->prepare($sql);
      return $stmt->execute($params);
    } catch (\PDOException $e) {
      error_log("Erro na execução da query: " . $e->getMessage());
      return false;
    }
  }
}
