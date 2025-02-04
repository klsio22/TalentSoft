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
  public string $cpf;
  public ?string $phone;
  public ?string $birthDate;
  public ?float $salary;
  public ?string $addressStreet;
  public ?string $addressNumber;
  public ?string $addressComplement;
  public ?string $addressNeighborhood;
  public ?string $addressCity;
  public ?string $addressState;
  public ?string $addressZipcode;
  public ?string $nationality;
  public ?string $maritalStatus;
  public ?string $notes;
  public string $role;
  public int $roleId;

  public function __construct(array $data)
  {
    // Dados principais
    $this->id = $data['id'] ?? 0;
    $this->name = $data['name'] ?? '';
    $this->email = $data['email'] ?? '';
    $this->password = $data['password'] ?? '';
    $this->cpf = $data['cpf'] ?? '';

    // Dados pessoais
    $this->phone = $data['phone'] ?? null;
    $this->birthDate = $data['birth_date'] ?? null;
    $this->salary = isset($data['salary']) ? (float)$data['salary'] : null;

    // Endereço
    $this->addressStreet = $data['address_street'] ?? '';
    $this->addressNumber = $data['address_number'] ?? '';
    $this->addressComplement = $data['address_complement'] ?? '';
    $this->addressNeighborhood = $data['address_neighborhood'] ?? '';
    $this->addressCity = $data['address_city'] ?? '';
    $this->addressState = $data['address_state'] ?? '';
    $this->addressZipcode = $data['address_zipcode'] ?? '';

    // Informações adicionais
    $this->nationality = $data['nationality'] ?? 'Brasileiro';
    $this->maritalStatus = $this->validateMaritalStatus($data['marital_status'] ?? 'Single');
    $this->notes = $data['notes'] ?? '';

    // Permissões
    $this->role = $data['role'] ?? 'user';
    $this->roleId = $data['role_id'] ?? 3;
  }


  public static function attempt(array $credentials): ?self
  {
    $result = null;

    try {
      if (self::hasValidCredentials($credentials)) {
        $user = self::findUserByEmail($credentials['email']);

        // Debug para verificar os dados retornados
        error_log("Dados do usuário encontrado: " . print_r($user, true));

        if ($user) {
          $passwordValid = password_verify($credentials['password'], $user['password']);
          error_log("Senha válida: " . ($passwordValid ? 'sim' : 'não'));

          if ($passwordValid) {
            error_log("Role do usuário: " . $user['role']);
            $result = new self($user);
          }
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
    $stmt = $db->prepare("
          SELECT
              e.id,
              e.name,
              e.email,
              e.password,
              e.role_id,
              r.name as role
          FROM employees e
          JOIN roles r ON e.role_id = r.id
          WHERE e.email = :email
          LIMIT 1
      ");

    $stmt->execute(['email' => $email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log("Resultado da busca por email: " . print_r($result, true));
    return $result ?: null;
  }

  public static function findById(?int $id): ?self
  {

    try {
      $db = Database::getInstance();
      $stmt = $db->prepare("
              SELECT e.*, r.name as role
              FROM employees e
              JOIN roles r ON e.role_id = r.id
              WHERE e.id = :id
              LIMIT 1
          ");
      $stmt->execute([':id' => $id]);
      $data = $stmt->fetch(PDO::FETCH_ASSOC);

      return $data ? new self($data) : null;
    } catch (\PDOException $e) {
      error_log("Erro ao buscar usuário: " . $e->getMessage());
      return null;
    }
  }

  public function isAdmin(): bool
  {
    return $this->role === 'admin';
  }

  public function getRole(): string
  {
    return $this->role_name ?? 'user';
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
    $stmt = $db->query("
          SELECT e.*, r.name as role
          FROM employees e
          JOIN roles r ON e.role_id = r.id
      ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_map(function ($user) {
      return new self($user);
    }, $users);
  }
  public static function update(array $data): bool
  {
    try {
      $db = Database::getInstance();

      error_log("Dados recebidos para update: " . print_r($data, true));

      $sql = "UPDATE employees SET name = :name, email = :email WHERE id = :id";
      $stmt = $db->prepare($sql);

      $params = [
        ':name' => $data['name'],
        ':email' => $data['email'],
        ':id' => (int)$data['id']
      ];

      error_log("Parâmetros do update: " . print_r($params, true));

      $result = $stmt->execute($params);
      error_log("Resultado do update: " . ($result ? 'sucesso' : 'falha'));

      return $result;
    } catch (\PDOException $e) {
      error_log("Erro ao atualizar usuário: " . $e->getMessage());
      return false;
    }
  }
  public static function delete(int $id): bool
  {
    try {
      $db = Database::getInstance();
      $sql = "DELETE FROM employees WHERE id = :id";
      $stmt = $db->prepare($sql);
      return $stmt->execute([':id' => $id]);
    } catch (\PDOException $e) {
      error_log("Erro ao deletar usuário: " . $e->getMessage());
      return false;
    }
  }
  public static function sanitizeData(array $data): array
  {
    return array_map(function ($value) {
      return is_string($value) ? trim($value) : $value;
    }, $data);
  }


  private function validateMaritalStatus(?string $status): ?string
  {
    $validStatus = ['Single', 'Married', 'Divorced', 'Widowed'];
    return in_array($status, $validStatus) ? $status : 'Single';
  }
}
