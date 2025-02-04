<?php

namespace App\Models;

use Core\Database\Database;
use Lib\FlashMessage;
use PDO;

class AdminUser extends User
{
  public static function register(array $data): ?self
  {
    $result = null;

    if (self::validateRegistrationData($data)) {
      $userData = self::prepareUserData($data);
      $user = new self($userData);
      $result = $user->create($userData) ? $user : null;
    }

    return $result;
  }

  private static function validateRegistrationData(array $data): bool
  {
    $isValid = true;
    $requiredFields = [
      'name',
      'email',
      'password',
      'cpf',
      'phone',
      'birth_date',
      'salary',
      'address_street',
      'address_number',
      'address_complement',
      'address_neighborhood',
      'address_city',
      'address_state',
      'address_zipcode'
    ];

    // Valida salário primeiro
    if ($isValid && empty($data['salary'])) {
      FlashMessage::danger("Salário é obrigatório.");
      $isValid = false;
    } else {
      $data['salary'] = (float) str_replace(',', '.', $data['salary']);
    }

    // Valida data de nascimento
    if ($isValid && (empty($data['birth_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['birth_date']))) {
      FlashMessage::danger('Data de nascimento inválida (use YYYY-MM-DD).');
      $isValid = false;
    }

    // Valida campos obrigatórios
    if ($isValid) {
      foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
          error_log("Campo obrigatório faltando: $field");
          FlashMessage::danger("Campo obrigatório faltando: $field");
          $isValid = false;
          break;
        }
      }
    }

    return $isValid;
  }

  private static function prepareUserData(array $data): array
  {
    return [
      'name' => $data['name'],
      'email' => $data['email'],
      'password' => $data['password'],
      'cpf' => $data['cpf'],
      'phone' => $data['phone'] ?? null,
      'birth_date' => $data['birth_date'] ?? null,
      'salary' => $data['salary'] ?? null,
      'address_street' => $data['address_street'] ?? null,
      'address_number' => $data['address_number'] ?? null,
      'address_complement' => $data['address_complement'] ?? null,
      'address_neighborhood' => $data['address_neighborhood'] ?? null,
      'address_city' => $data['address_city'] ?? null,
      'address_state' => $data['address_state'] ?? "",
      'address_zipcode' => $data['address_zipcode'] ?? "",
      'nationality' => $data['nationality'] ?? 'Brasileiro',
      'is_active' => $data['is_active'] ?? true,
      'marital_status' => $data['marital_status'] ?? 'Single',
      'notes' => $data['notes'] ?? '',
      'role_id' => 3
    ];
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
    $success = false;

    if ($this->isValidForSave()) {
      $parameters = $this->prepareParameters();
      $success = $this->executeInsert($parameters);
    }

    return $success;
  }

  private function isValidForSave(): bool
  {
    if ($this->id !== 0) {
      FlashMessage::danger('Registro já existe.');
      return false;
    }

    if (empty($this->name) || empty($this->email) || empty($this->password)) {
      FlashMessage::danger('Campos obrigatórios não preenchidos');
      return false;
    }

    return true;
  }

  private function prepareParameters(): array
  {
    return [
      ':name' => $this->name,
      ':email' => $this->email,
      ':password' => password_hash($this->password, PASSWORD_DEFAULT),
      ':role' => 'user',
      ':cpf' => $this->cpf,
      ':phone' => $this->phone ?? '',
      ':birth_date' => $this->birthDate ?? date('Y-m-d'),
      ':salary' => $this->salary ?? 0.00,
      ':address_street' => $this->addressStreet ?? '',
      ':address_number' => $this->addressNumber ?? '',
      ':address_complement' => $this->addressComplement ?? '',
      ':address_neighborhood' => $this->addressNeighborhood ?? '',
      ':address_city' => $this->addressCity ?? '',
      ':address_state' => $this->addressState ?? '',
      ':address_zipcode' => $this->addressZipcode ?? '',
      ':nationality' => $this->nationality ?? 'Brasileiro',
      ':marital_status' => $this->validateMaritalStatus($this->maritalStatus),
      ':notes' => $this->notes ?? ''
    ];
  }

  private function executeInsert(array $parameters): bool
  {
    $database = Database::getInstance();
    error_log("Dados para inserção: " . json_encode($parameters));

    try {
      $sql = "INSERT INTO employees (
                  name, email, password, role_id, cpf,
                  phone, birth_date, salary, hire_date,
                  status, address_street, address_number,
                  address_complement, address_neighborhood,
                  address_city, address_state, address_zipcode,
                  nationality, marital_status, notes
              ) VALUES (
                  :name, :email, :password,
                  (SELECT id FROM roles WHERE name = :role),
                  :cpf, :phone, :birth_date, :salary,
                  CURDATE(), 'Active', :address_street,
                  :address_number, :address_complement,
                  :address_neighborhood, :address_city,
                  :address_state, :address_zipcode,
                  :nationality, :marital_status, :notes
              )";


      $stmt = $database->prepare($sql);
      $result = $stmt->execute($parameters);

      if ($result) {
        FlashMessage::success('Usuário cadastrado com sucesso.');
      } else {
        FlashMessage::danger('Erro ao cadastrar usuário.');
      }

      return $result;
    } catch (\PDOException $e) {
      error_log("Erro no banco: " . $e->getMessage());
      FlashMessage::danger('Erro ao cadastrar: ' . $e->getMessage());
      return false;
    }
  }

  private function validateMaritalStatus(?string $status): string
  {
    $valid = ['Single', 'Married', 'Divorced', 'Widowed'];
    return in_array($status, $valid) ? $status : 'Single';
  }
}
