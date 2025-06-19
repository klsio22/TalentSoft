<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\EmployeeFactory;
use App\Models\Role;
use App\Models\UserCredential;
use Tests\TestCase;

/**
 * Testes unitários para o EmployeeFactory
 */
class EmployeeFactoryTest extends TestCase
{
    // Constantes para evitar duplicação de valores
    private const EMPLOYEE_NAME = 'Funcionário Teste Factory';
    private const BIRTH_DATE = '1990-01-01';
    private const HIRE_DATE_BR = '01/01/2023';
    private const HIRE_DATE_ISO = '2023-01-01';
    private const SALARY_BR = 'R$ 5.000,00';
    private const EMAIL_DOMAIN = '@example.com';
    private ?Role $testRole = null;

    /**
     * Configura dados para teste
     */
    public function setUp(): void
    {
        parent::setUp();

        // Criar um cargo para o funcionário
        $this->testRole = new Role([
            'name' => 'Cargo Teste Factory',
            'description' => 'Cargo para teste de EmployeeFactory'
        ]);
        $this->testRole->save();
    }

    /**
     * Testa a criação de um funcionário com credenciais
     */
    public function test_create_with_credentials_success(): void
    {
        $employeeData = [
            'name' => self::EMPLOYEE_NAME,
            'email' => 'factory_test_' . uniqid() . self::EMAIL_DOMAIN,
            'cpf' => uniqid(),
            'birth_date' => self::BIRTH_DATE,
            'role_id' => $this->testRole->id,
            'salary' => self::SALARY_BR, // Formato brasileiro para testar o pré-processamento
            'hire_date' => self::HIRE_DATE_BR, // Formato brasileiro para testar o pré-processamento
            'status' => 'Active',
            'password' => 'senha123',
            'password_confirmation' => 'senha123'
        ];

        [$success, $message, $employee] = EmployeeFactory::createWithCredentials($employeeData);

        $this->assertTrue($success);
        $this->assertEmpty($message);
        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertNotNull($employee->id);

        // Verificar se os dados foram processados corretamente
        $this->assertEquals($employeeData['name'], $employee->name);
        $this->assertEquals($employeeData['email'], $employee->email);
        $this->assertEquals($employeeData['cpf'], $employee->cpf);
        $this->assertEquals('2023-01-01', $employee->hire_date); // Formato convertido
        $this->assertEquals(5000.00, $employee->salary); // Valor convertido

        // Verificar se as credenciais foram criadas
        $credentials = UserCredential::findBy(['employee_id' => $employee->id]);
        $this->assertNotNull($credentials);
        $this->assertTrue(password_verify($employeeData['password'], $credentials->password_hash));
    }

    /**
     * Testa a validação de campos obrigatórios
     */
    public function test_create_with_credentials_missing_required_fields(): void
    {
        // Dados sem campos obrigatórios
        $employeeData = [
            'name' => self::EMPLOYEE_NAME,
            // Faltando email
            'cpf' => uniqid(),
            // Faltando role_id
            'birth_date' => self::BIRTH_DATE,
            'salary' => self::SALARY_BR,
            // Faltando hire_date
            'status' => 'Active',
            'password' => 'senha123',
            'password_confirmation' => 'senha123'
        ];

        [$success, $message, $employee] = EmployeeFactory::createWithCredentials($employeeData);

        $this->assertFalse($success);
        $this->assertNotEmpty($message);
        $this->assertNull($employee);
        $this->assertStringContainsString('obrigatório', $message);
    }

    /**
     * Testa a validação de senha e confirmação de senha
     */
    public function test_create_with_credentials_password_mismatch(): void
    {
        $employeeData = [
            'name' => self::EMPLOYEE_NAME,
            'email' => 'factory_test_' . uniqid() . self::EMAIL_DOMAIN,
            'cpf' => uniqid(),
            'birth_date' => self::BIRTH_DATE,
            'role_id' => $this->testRole->id,
            'salary' => self::SALARY_BR,
            'hire_date' => self::HIRE_DATE_BR,
            'status' => 'Active',
            'password' => 'senha123',
            'password_confirmation' => 'senha456' // Senha diferente
        ];

        [$success, $message, $employee] = EmployeeFactory::createWithCredentials($employeeData);

        $this->assertFalse($success);
        $this->assertNotEmpty($message);
        $this->assertNull($employee);
        $this->assertStringContainsString('senha', $message);
        $this->assertStringContainsString('confirmação', $message);
    }

    /**
     * Testa o pré-processamento de dados
     */
    public function test_preprocess_employee_data(): void
    {
        // Método privado, então vamos testar indiretamente através do createWithCredentials
        $employeeData = [
            'name' => self::EMPLOYEE_NAME,
            'email' => 'factory_test_' . uniqid() . self::EMAIL_DOMAIN,
            'cpf' => uniqid(),
            'birth_date' => self::BIRTH_DATE,
            'role_id' => $this->testRole->id,
            'salary' => 'R$ 1.234,56', // Formato brasileiro
            'hire_date' => self::HIRE_DATE_BR, // Formato brasileiro
            'status' => 'Active',
            'password' => 'senha123',
            'password_confirmation' => 'senha123'
        ];

        [$success, , $employee] = EmployeeFactory::createWithCredentials($employeeData);

        $this->assertTrue($success);
        $this->assertInstanceOf(Employee::class, $employee);

        // Verificar se os dados foram pré-processados corretamente
        $this->assertEquals(1234.56, $employee->salary); // Valor convertido
        $this->assertEquals(self::HIRE_DATE_ISO, $employee->hire_date); // Data convertida
    }

    /**
     * Testa a criação com dados inválidos para o Employee
     */
    public function test_create_with_invalid_employee_data(): void
    {
        // Criar um funcionário com o mesmo email para causar erro de validação
        $existingEmployee = new Employee([
            'name' => 'Funcionário Existente',
            'email' => 'duplicado' . self::EMAIL_DOMAIN,
            'cpf' => uniqid(),
            'birth_date' => self::BIRTH_DATE,
            'role_id' => $this->testRole->id,
            'salary' => 5000.00,
            'hire_date' => date('Y-m-d'),
            'status' => 'Active'
        ]);
        $existingEmployee->save();

        // Tentar criar outro funcionário com o mesmo email
        $employeeData = [
            'name' => self::EMPLOYEE_NAME,
            'email' => 'duplicado' . self::EMAIL_DOMAIN, // Email duplicado
            'cpf' => uniqid(),
            'birth_date' => self::BIRTH_DATE,
            'role_id' => $this->testRole->id,
            'salary' => self::SALARY_BR,
            'hire_date' => self::HIRE_DATE_BR,
            'status' => 'Active',
            'password' => 'senha123',
            'password_confirmation' => 'senha123'
        ];

        [$success, $message, $employee] = EmployeeFactory::createWithCredentials($employeeData);

        $this->assertFalse($success);
        $this->assertNotEmpty($message);
        $this->assertNull($employee);
    }
}
