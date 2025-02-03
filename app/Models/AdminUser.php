<?php

namespace App\Models;

use Core\Database\Database;
use Lib\FlashMessage;
use PDO;

class AdminUser extends User
{
  public static function register(array $data): ?self
  {
    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
      FlashMessage::danger('Por favor, preencha todos os campos.');
      return null;
    }

    $userData = [
      'name' => $data['name'],
      'email' => $data['email'],
      'password' => $data['password'],
      'role' => 'user',
    ];


    error_log(print_r($userData, true));
    error_log(print_r($data['password'], true));

    $user = new self($userData);
    return  $user->create($userData) ? $user : null;
  }

  public static function create(array $data): ?self
  {
    if (self::findByEmail($data['email'])) {
      FlashMessage::danger('Email já cadastrado.');
      return null;
    }

    $user = new self($data);
    return $user->saveDates() ? $user : null;
  }

  public function updateUser(array $data): bool
  {
    $success = true;

    if ($this->isValidInputData($data) && $this->isEmailAvailable($data['email'])) {
      $this->updateUserData($data);
      $success = $this->saveDates();

      if ($success) {
        FlashMessage::success('Usuário atualizado com sucesso.');
      } else {
        FlashMessage::danger('Erro ao atualizar usuário.');
      }
    }

    return $success;
  }

  private function isValidInputData(array $data): bool
  {
    if (empty($data['name']) || empty($data['email'])) {
      FlashMessage::danger('Nome e email são obrigatórios.');
      return false;
    }
    return true;
  }

  private function isEmailAvailable(string $email): bool
  {
    $existingUser = self::findByEmail($email);
    if ($existingUser && $existingUser->id !== $this->id) {
      FlashMessage::danger('Este email já está em uso.');
      return false;
    }
    return true;
  }

  private function updateUserData(array $data): void
  {
    $this->name = $data['name'];
    $this->email = $data['email'];
    $this->role = $data['role'] ?? $this->role;

    if (!empty($data['password'])) {
      $this->password = $data['password'];
    }
  }

  public static function findByEmail(string $email): ?self
  {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("
          SELECT e.*, r.name as role
          FROM employees e
          JOIN roles r ON e.role_id = r.id
          WHERE e.email = :email
          LIMIT 1
      ");
    $stmt->execute(['email' => $email]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
      return new self($data);
    }

    return null;
  }
  public function saveDates(): bool
  {
    $database = Database::getInstance();

    if ($this->id === 0) {
      $sql = "INSERT INTO employees (
              name, email, password, role_id,
              cpf, status
          ) VALUES (
              :name, :email, :password,
              (SELECT id FROM roles WHERE name = 'admin'),
              :cpf, 'Active'
          )";

      $statement = $database->prepare($sql);
      return $statement->execute([
        ':name' => $this->name,
        ':email' => $this->email,
        ':password' => password_hash($this->password, PASSWORD_DEFAULT),
        ':cpf' => '000.000.000-00'
      ]);
    }
  }
}
