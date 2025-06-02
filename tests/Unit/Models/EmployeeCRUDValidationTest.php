<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\Role;
use Tests\TestCase;

/**
 * Testes unitários para validar operações CRUD no modelo Employee
 */
class EmployeeCRUDValidationTest extends TestCase
{
    /**
     * Criar role e employee para os testes
     */
    private function createTestData(): array
    {
        $role = new Role(['name' => 'Test Role']);
        $this->assertTrue($role->save());

        $employee = new Employee([
            'name' => 'Test Employee',
            'email' => 'crud-test@example.com',
            'cpf' => '12345678900',
            'birth_date' => '1990-01-01',
            'role_id' => $role->id,
            'hire_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $this->assertTrue($employee->save());
        return [$role, $employee];
    }

    /**
     * Testa operação de criação (Create)
     */
    public function test_create_employee_crud(): void
    {
        [$role, $employee] = $this->createTestData();

        $this->assertNotNull($employee->id);
        $this->assertEquals('Test Employee', $employee->name);
        $this->assertEquals('crud-test@example.com', $employee->email);
    }

    /**
     * Testa operação de leitura (Read)
     */
    public function test_read_employee_crud(): void
    {
        [$role, $employee] = $this->createTestData();

        $foundEmployee = Employee::findById($employee->id);
        $this->assertNotNull($foundEmployee);
        $this->assertEquals($employee->name, $foundEmployee->name);

        $foundByEmail = Employee::findByEmail($employee->email);
        $this->assertNotNull($foundByEmail);
        $this->assertEquals($employee->id, $foundByEmail->id);
    }

    /**
     * Testa operação de atualização (Update)
     */
    public function test_update_employee_crud(): void
    {
        [$role, $employee] = $this->createTestData();

        $employee->name = 'Updated Name';
        $this->assertTrue($employee->save());

        $updatedEmployee = Employee::findById($employee->id);
        $this->assertEquals('Updated Name', $updatedEmployee->name);
    }

    /**
     * Testa operação de exclusão (Delete)
     */
    public function test_delete_employee_crud(): void
    {
        [$role, $employee] = $this->createTestData();

        $employeeId = $employee->id;
        $this->assertTrue($employee->destroy());

        $deletedEmployee = Employee::findById($employeeId);
        $this->assertNull($deletedEmployee);
    }
}
