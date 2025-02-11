<?php

namespace App\Models;

use Core\Database\Database;
use Lib\FlashMessage;
use PDO;

class AdminUser extends User
{
  const VALID_MARITAL_STATUSES = ['Single', 'Married', 'Divorced', 'Widowed'];
  const DEFAULT_ROLE = 'user';
  const DEFAULT_NATIONALITY = 'Brasileiro';

  public static function register(array $data): ?self
  {
    if (self::validateData($data)) {
      $userData = self::prepareUserData($data);
      $user = new self($userData);
      return $user->create() ? $user : null;
    }
    return null;
  }

  private static function validateData(array $data): bool
  {
    $errors = [];

    // Validar campos obrigatórios
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
      'address_neighborhood',
      'address_city',
      'address_state',
      'address_zipcode'
    ];

    foreach ($requiredFields as $field) {
      if (empty($data[$field])) {
        $errors[] = "Campo obrigatório faltando: $field";
      }
    }

    // Validar salário
    if (empty($data['salary']) || !is_numeric(str_replace(',', '.', $data['salary']))) {
      $errors[] = "Salário inválido.";
    } else {
      $data['salary'] = (float) str_replace(',', '.', $data['salary']);
    }

    // Validar data de nascimento
    if (empty($data['birth_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['birth_date'])) {
      $errors[] = 'Data de nascimento inválida (use YYYY-MM-DD).';
    }

    // Validar email duplicado
    if (self::findByEmail($data['email'])) {
      $errors[] = 'Este email já está em uso.';
    }

    // Retornar resultado da validação
    if (!empty($errors)) {
      foreach ($errors as $error) {
        FlashMessage::danger($error);
        error_log($error);
      }
      return false;
    }

    return true;
  }

  private static function prepareUserData(array $data): array
  {
    return [
      'name' => $data['name'],
      'email' => $data['email'],
      'password' => password_hash($data['password'], PASSWORD_DEFAULT),
      'cpf' => $data['cpf'],
      'phone' => $data['phone'] ?? '',
      'birth_date' => $data['birth_date'] ?? date('Y-m-d'),
      'salary' => $data['salary'] ?? 0.00,
      'address_street' => $data['address_street'] ?? '',
      'address_number' => $data['address_number'] ?? '',
      'address_complement' => $data['address_complement'] ?? '',
      'address_neighborhood' => $data['address_neighborhood'] ?? '',
      'address_city' => $data['address_city'] ?? '',
      'address_state' => $data['address_state'] ?? '',
      'address_zipcode' => $data['address_zipcode'] ?? '',
      'nationality' => $data['nationality'] ?? self::DEFAULT_NATIONALITY,
      'marital_status' => self::validateMaritalStatus($data['marital_status'] ?? 'Single'),
      'role_id' => 3
    ];
  }

  public function create(): bool
  {
    if (!$this->isValidForSave()) {
      return false;
    }

    try {
      $parameters = $this->prepareParameters();
      $sql = "INSERT INTO employees (
                        name, email, password, role_id, cpf,
                        phone, birth_date, salary, hire_date,
                        status, address_street, address_number,
                        address_complement, address_neighborhood,
                        address_city, address_state, address_zipcode,
                        nationality, marital_status, notes
                    ) VALUES (
                        :name, :email, :password, :role_id,
                        :cpf, :phone, :birth_date, :salary,
                        CURDATE(), 'Active', :address_street,
                        :address_number, :address_complement,
                        :address_neighborhood, :address_city,
                        :address_state, :address_zipcode,
                        :nationality, :marital_status, :notes
                    )";

      $stmt = Database::getInstance()->prepare($sql);
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

  private function isValidForSave(): bool
  {
    if (empty($this->name) || empty($this->email) || empty($this->password)) {
      FlashMessage::danger('Campos obrigatórios não preenchidos.');
      return false;
    }

    return true;
  }

  private function prepareParameters(): array
  {
    return [
      ':name' => $this->name,
      ':email' => $this->email,
      ':password' => $this->password,
      ':role_id' => $this->roleId,
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
      ':nationality' => $this->nationality ?? self::DEFAULT_NATIONALITY,
      ':marital_status' => $this->maritalStatus ?? 'Single',
      ':notes' => $this->notes ?? ''
    ];
  }

  private static function validateMaritalStatus(?string $status): string
  {
    return in_array($status, self::VALID_MARITAL_STATUSES) ? $status : 'Single';
  }

  public static function findByEmail(string $email): ?self
  {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("
            SELECT e.*, r.name AS role_name
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

  public static function update(array $data): bool
  {
    $result = false;
    try {
      $db = Database::getInstance();
      error_log("Dados recebidos para update: " . print_r($data, true));

      if (!isset($data['id'])) {
        error_log("ID não fornecido para atualização");
      } else {
        // Buscar dados existentes
        $currentUser = self::findById($data['id']);
        if (!$currentUser) {
          error_log("Usuário não encontrado com ID: " . $data['id']);
        } else {
          $params = [
            ':name' => $data['name'] ?? $currentUser->name,
            ':email' => $data['email'] ?? $currentUser->email,
            ':cpf' => $data['cpf'] ?? $currentUser->cpf,
            ':phone' => $data['phone'] ?? $currentUser->phone,
            ':birth_date' => $data['birth_date'] ?? $currentUser->birthDate,
            ':salary' => $data['salary'] ?? $currentUser->salary,
            ':address_street' => $data['address_street'] ?? $currentUser->addressStreet,
            ':address_number' => $data['address_number'] ?? $currentUser->addressNumber,
            ':address_complement' => $data['address_complement'] ?? $currentUser->addressComplement,
            ':address_neighborhood' => $data['address_neighborhood'] ?? $currentUser->addressNeighborhood,
            ':address_city' => $data['address_city'] ?? $currentUser->addressCity,
            ':address_state' => $data['address_state'] ?? $currentUser->addressState,
            ':address_zipcode' => $data['address_zipcode'] ?? $currentUser->addressZipcode,
            ':nationality' => $data['nationality'] ?? $currentUser->nationality,
            ':marital_status' => self::validateMaritalStatus($data['marital_status'] ?? $currentUser->maritalStatus),
            ':notes' => $data['notes'] ?? $currentUser->notes,
            ':id' => (int)$data['id']
          ];

          $sql = "UPDATE employees SET
                    name = :name,
                    email = :email,
                    cpf = :cpf,
                    phone = :phone,
                    birth_date = :birth_date,
                    salary = :salary,
                    address_street = :address_street,
                    address_number = :address_number,
                    address_complement = :address_complement,
                    address_neighborhood = :address_neighborhood,
                    address_city = :address_city,
                    address_state = :address_state,
                    address_zipcode = :address_zipcode,
                    nationality = :nationality,
                    marital_status = :marital_status,
                    notes = :notes
                    WHERE id = :id";

          $stmt = $db->prepare($sql);
          $result = $stmt->execute($params);

          error_log("Resultado do update: " . ($result ? "sucesso" : "falha"));
        }
      }
    } catch (\PDOException $e) {
      error_log("Erro no banco de dados: " . $e->getMessage());
    } catch (\Exception $e) {
      error_log("Erro geral: " . $e->getMessage());
    }
    return $result;
  }

  public static function delete(int $id): bool
  {
    try {
      $db = Database::getInstance();
      $sql = "DELETE FROM employees WHERE id = :id";
      error_log("SQL para deletar usuário: " . $sql);
      $stmt = $db->prepare($sql);
      return $stmt->execute([':id' => $id]);
    } catch (\PDOException $e) {
      error_log("Erro ao deletar usuário: " . $e->getMessage());
      return false;
    }
  }
}
