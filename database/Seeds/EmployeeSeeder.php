<?php

namespace Database\Seeds;

use Core\Database\Database;

class EmployeeSeeder

{

  private static function getDefaultValue($value, $default)
  {
    return $value ?? $default;
  }


  public static function run(): void
  {
    $db = Database::getInstance();
    $employees = self::getEmployees();
    self::insertEmployees($db, $employees);
    echo "Employees inseridos com sucesso!\n";
  }

  private static function getEmployees(): array
  {
    return [
      [
        'name' => 'Super Admin',
        'cpf' => '123.456.789-00',
        'email' => 'super@example.com',
        'password' => password_hash('super123', PASSWORD_DEFAULT),
        'phone' => '(41) 99999-9999',
        'birth_date' => '1990-01-01',
        'role_id' => 1,
        'salary' => 15000.00,
        'hire_date' => date('Y-m-d'),
        'status' => 'Active',
        'termination_date' => null,
        'termination_reason' => null,
        'address_street' => 'Rua das Flores',
        'address_number' => '100',
        'address_complement' => 'Apto 1001',
        'address_neighborhood' => 'Centro',
        'address_city' => 'Curitiba',
        'address_state' => 'PR',
        'address_zipcode' => '80010-140',
        'nationality' => 'Brasileiro',
        'marital_status' => 'Married',
        'notes' => 'Super administrador do sistema'
      ],
      [
        'name' => 'RH Manager',
        'cpf' => '987.654.321-00',
        'email' => 'rh@example.com',
        'password' => password_hash('rh123', PASSWORD_DEFAULT),
        'phone' => '(41) 98888-8888',
        'birth_date' => '1985-05-15',
        'role_id' => 2,
        'salary' => 8000.00,
        'hire_date' => date('Y-m-d'),
        'status' => 'Active',
        'termination_date' => null,
        'termination_reason' => null,
        'address_street' => 'Avenida República',
        'address_number' => '1500',
        'address_complement' => 'Sala 505',
        'address_neighborhood' => 'Rebouças',
        'address_city' => 'Curitiba',
        'address_state' => 'PR',
        'address_zipcode' => '80230-031',
        'nationality' => 'Brasileiro',
        'marital_status' => 'Single',
        'notes' => 'Gerente de RH'
      ],
      [
        'name' => 'John Doe',
        'cpf' => '111.222.333-44',
        'email' => 'john.doe@example.com',
        'password' => password_hash('johndoe123', PASSWORD_DEFAULT),
        'role_id' => 3,
        'phone' => null,
        'birth_date' => null,
        'salary' => null,
        'hire_date' => date('Y-m-d'),
        'status' => 'Active',
        'termination_date' => null,
        'termination_reason' => null,
        'address_street' => null,
        'address_number' => null,
        'address_complement' => null,
        'address_neighborhood' => null,
        'address_city' => null,
        'address_state' => null,
        'address_zipcode' => null,
        'nationality' => null,
        'marital_status' => null,
        'notes' => null
      ],
      [
        'name' => 'Jane Smith',
        'cpf' => '444.555.666-77',
        'email' => 'jane.smith@example.com',
        'password' => password_hash('janesmith123', PASSWORD_DEFAULT),
        'role_id' => 3,
        'phone' => null,
        'birth_date' => null,
        'salary' => null,
        'hire_date' => date('Y-m-d'),
        'status' => 'Active',
        'termination_date' => null,
        'termination_reason' => null,
        'address_street' => null,
        'address_number' => null,
        'address_complement' => null,
        'address_neighborhood' => null,
        'address_city' => null,
        'address_state' => null,
        'address_zipcode' => null,
        'nationality' => null,
        'marital_status' => null,
        'notes' => null
      ]
    ];
  }

  private static function insertEmployees($db, array $employees): void
  {
    foreach ($employees as $employee) {
      // Aplicar valores padrão
      $employee = [
        'name' => $employee['name'],
        'cpf' => $employee['cpf'],
        'email' => $employee['email'],
        'password' => $employee['password'],
        'phone' => self::getDefaultValue($employee['phone'], '(00) 00000-0000'),
        'birth_date' => self::getDefaultValue($employee['birth_date'], date('Y-m-d')),
        'role_id' => $employee['role_id'],
        'salary' => self::getDefaultValue($employee['salary'], 0.00),
        'hire_date' => self::getDefaultValue($employee['hire_date'], date('Y-m-d')),
        'status' => self::getDefaultValue($employee['status'], 'Active'),
        'termination_date' => self::getDefaultValue($employee['termination_date'], null),
        'termination_reason' => self::getDefaultValue($employee['termination_reason'], null),
        'address_street' => self::getDefaultValue($employee['address_street'], 'Não informado'),
        'address_number' => self::getDefaultValue($employee['address_number'], 'S/N'),
        'address_complement' => self::getDefaultValue($employee['address_complement'], ''),
        'address_neighborhood' => self::getDefaultValue($employee['address_neighborhood'], 'Centro'),
        'address_city' => self::getDefaultValue($employee['address_city'], 'Não informado'),
        'address_state' => self::getDefaultValue($employee['address_state'], 'PR'),
        'address_zipcode' => self::getDefaultValue($employee['address_zipcode'], '00000-000'),
        'nationality' => self::getDefaultValue($employee['nationality'], 'Brasileiro'),
        'marital_status' => self::getDefaultValue($employee['marital_status'], 'Single'),
        'notes' => self::getDefaultValue($employee['notes'], '')
      ];

      $sql = "INSERT INTO employees (
          name, cpf, email, password, phone, birth_date, role_id, salary,
          hire_date, status, termination_date, termination_reason,
          address_street, address_number, address_complement,
          address_neighborhood, address_city, address_state,
          address_zipcode, nationality, marital_status, notes
      ) VALUES (
          :name, :cpf, :email, :password, :phone, :birth_date, :role_id,
          :salary, :hire_date, :status, :termination_date, :termination_reason,
          :address_street, :address_number, :address_complement,
          :address_neighborhood, :address_city, :address_state,
          :address_zipcode, :nationality, :marital_status, :notes
      )";

      $stmt = $db->prepare($sql);
      $stmt->execute($employee);
    }
  }
}
