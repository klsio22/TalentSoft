<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\Role;
use Tests\TestCase;

/**
 * Testes unitários para operações CRUD do modelo Employee
 */
class EmployeeCRUDTest extends TestCase
{
    /**
     * Testa a criação de um funcionário
     */
    public function test_create_employee(): void
    {
        // Primeiro criamos um role para associar ao employee
        $role = new Role(['name' => 'Test Role']);
        $this->assertTrue($role->save(), 'Não foi possível salvar o cargo');

        // Dados do employee
        $employeeData = [
            'name' => 'Test Employee',
            'email' => 'test@example.com',
            'cpf' => '12345678909',
            'birth_date' => '1990-01-01',
            'role_id' => $role->id,
            'salary' => 5000.00,
            'hire_date' => date('Y-m-d'),
            'status' => 'active',
            'address' => 'Test Address',
            'city' => 'Test City',
            'state' => 'TS',
            'zipcode' => '12345-678',
        ];

        // Criar employee
        $employee = new Employee($employeeData);
        $this->assertTrue($employee->save(), 'Não foi possível salvar o funcionário');

        // Verificar se o employee foi salvo com os dados corretos
        $savedEmployee = Employee::findById($employee->id);
        $this->assertNotNull($savedEmployee);
        $this->assertEquals($employeeData['name'], $savedEmployee->name);
        $this->assertEquals($employeeData['email'], $savedEmployee->email);
        $this->assertEquals($employeeData['cpf'], $savedEmployee->cpf);
    }

    /**
     * Testa a leitura (read) de um funcionário
     */
    public function test_read_employee(): void
    {
        // Primeiro criamos um role para associar ao employee
        $role = new Role(['name' => 'Test Role']);
        $this->assertTrue($role->save());

        // Criar um employee
        $employee = new Employee([
            'name' => 'Test Employee',
            'email' => 'read@example.com',
            'cpf' => '98765432109',
            'birth_date' => '1991-02-02',
            'role_id' => $role->id,
            'salary' => 6000.00,
            'hire_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $this->assertTrue($employee->save());

        // Buscar pelo ID
        $foundById = Employee::findById($employee->id);
        $this->assertNotNull($foundById);
        $this->assertEquals($employee->name, $foundById->name);

        // Buscar pelo email
        $foundByEmail = Employee::findByEmail($employee->email);
        $this->assertNotNull($foundByEmail);
        $this->assertEquals($employee->id, $foundByEmail->id);
    }

    /**
     * Testa a atualização (update) de um funcionário
     */
    public function test_update_employee(): void
    {
        // Primeiro criamos um role para associar ao employee
        $role = new Role(['name' => 'Test Role']);
        $this->assertTrue($role->save());

        // Criar um employee
        $employee = new Employee([
            'name' => 'Original Name',
            'email' => 'update@example.com',
            'cpf' => '11122233344',
            'birth_date' => '1992-03-03',
            'role_id' => $role->id,
            'salary' => 7000.00,
            'hire_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $this->assertTrue($employee->save());

        // Atualizar o employee
        $employee->name = 'Updated Name';
        $employee->salary = 8000.00;

        $this->assertTrue($employee->save());

        // Verificar se as alterações foram salvas
        $updatedEmployee = Employee::findById($employee->id);
        $this->assertEquals('Updated Name', $updatedEmployee->name);
        $this->assertEquals(8000.00, (float)$updatedEmployee->salary);
    }

    /**
     * Testa a exclusão (delete) de um funcionário
     */
    public function test_delete_employee(): void
    {
        // Primeiro criamos um role para associar ao employee
        $role = new Role(['name' => 'Test Role']);
        $this->assertTrue($role->save());

        // Criar um employee
        $employee = new Employee([
            'name' => 'To Delete',
            'email' => 'delete@example.com',
            'cpf' => '55566677788',
            'birth_date' => '1993-04-04',
            'role_id' => $role->id,
            'salary' => 9000.00,
            'hire_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $this->assertTrue($employee->save());
        $employeeId = $employee->id;

        // Excluir o employee
        $this->assertTrue($employee->destroy());

        // Verificar se o employee foi excluído
        $deletedEmployee = Employee::findById($employeeId);
        $this->assertNull($deletedEmployee);
    }

    /**
     * Testa a criação de um funcionário com credenciais
     */
    public function test_create_employee_with_credentials(): void
    {
        // Primeiro criamos um role para associar ao employee
        $role = new Role(['name' => 'Test Role']);
        $this->assertTrue($role->save());

        // Dados para criar um employee com credenciais
        $employeeData = [
            'name' => 'Credentials Employee',
            'email' => 'credentials@example.com',
            'cpf' => '99988877766',
            'birth_date' => '1994-05-05',
            'role_id' => $role->id,
            'salary' => 10000.00,
            'hire_date' => date('Y-m-d'),
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // Criar employee com credenciais
        list($success, $message, $employee) = Employee::createWithCredentials($employeeData);

        $this->assertTrue($success);
        $this->assertNotNull($employee);

        // Verificar se as credenciais foram criadas
        $credential = $employee->credential();
        $this->assertNotNull($credential);

        // Verificar se a autenticação funciona
        $this->assertTrue($employee->authenticate('password123'));
    }

    /**
     * Testa a validação de dados ao criar um funcionário
     */
    public function test_employee_validation(): void
    {
        // Tentar criar um funcionário sem dados obrigatórios
        $employee = new Employee();
        $this->assertFalse($employee->save());

        // Verificar que há erros de validação
        $this->assertTrue($employee->hasErrors());

        // Verificar erros específicos
        $this->assertNotNull($employee->errors('name'));
        $this->assertNotNull($employee->errors('email'));
        $this->assertNotNull($employee->errors('cpf'));
        $this->assertNotNull($employee->errors('role_id'));
    }
}
