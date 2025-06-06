<?php

namespace Tests\Unit\Controllers;

use App\Models\Employee;
use App\Models\Role;
use Core\Http\Request;
use Tests\TestCase;

/**
 * Testes unitários para o CRUD de Employee
 * Versão modificada para focar apenas na validação dos métodos CRUD
 */
class EmployeesControllerCRUDTest extends TestCase
{
    /**
     * Criar um role e um employee para os testes
     * @return array{0: \App\Models\Role, 1: \App\Models\Employee}
     */
    private function createTestData(): array
    {
        $role = new Role(['name' => 'Test Role']);
        $this->assertTrue($role->save());

        $employee = new Employee([
            'name' => 'Test Employee',
            'email' => 'controller@example.com',
            'cpf' => '11122233355',
            'birth_date' => '1990-01-01',
            'role_id' => $role->id,
            'hire_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $this->assertTrue($employee->save());

        return [$role, $employee];
    }

    /**
     * Testa se o modelo Employee permite a criação de registros (Create)
     */
    public function test_employee_create(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Verificar se o funcionário foi criado corretamente
        $this->assertNotNull($employee->id);
        $this->assertEquals('Test Employee', $employee->name);
        $this->assertEquals('controller@example.com', $employee->email);
    }

    /**
     * Testa se o modelo Employee permite a leitura de registros (Read)
     */
    public function test_employee_read(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Buscar o funcionário pelo ID
        $foundEmployee = Employee::findById($employee->id);

        // Verificar se os dados foram recuperados corretamente
        $this->assertNotNull($foundEmployee);
        $this->assertEquals($employee->name, $foundEmployee->name);
        $this->assertEquals($employee->email, $foundEmployee->email);

        // Buscar o funcionário pelo email
        $foundByEmail = Employee::findByEmail($employee->email);
        $this->assertNotNull($foundByEmail);
        $this->assertEquals($employee->id, $foundByEmail->id);

        // Buscar todos os funcionários
        $employees = Employee::all();
        $this->assertGreaterThanOrEqual(1, count($employees));
    }

    /**
     * Testa se o modelo Employee permite a atualização de registros (Update)
     */
    public function test_employee_update(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Atualizar os dados do funcionário
        $employee->name = 'Updated Employee';
        $employee->email = 'updated@example.com';
        $this->assertTrue($employee->save());

        // Verificar se os dados foram atualizados no banco
        $updatedEmployee = Employee::findById($employee->id);
        $this->assertEquals('Updated Employee', $updatedEmployee->name);
        $this->assertEquals('updated@example.com', $updatedEmployee->email);
    }

    /**
     * Testa se o modelo Employee permite a exclusão de registros (Delete)
     */
    public function test_employee_delete(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Armazenar o ID para consulta posterior
        $employeeId = $employee->id;

        // Excluir o funcionário
        $this->assertTrue($employee->destroy());

        // Verificar se o funcionário foi removido do banco
        $deletedEmployee = Employee::findById($employeeId);
        $this->assertNull($deletedEmployee);
    }

    /**
     * Testa se as validações do modelo Employee funcionam corretamente
     */
    public function test_employee_validations(): void
    {
        // Criar um funcionário com dados inválidos
        $employee = new Employee([
            // Faltando campos obrigatórios
        ]);

        // Tentativa de salvar deve falhar
        $this->assertFalse($employee->save());

        // Deve ter erros de validação
        $errors = $employee->errors();
        $this->assertNotEmpty($errors);

        // Verificar se existem erros para campos obrigatórios
        $this->assertArrayHasKey('name', $errors, 'Deve haver erro para o campo name');
        $this->assertArrayHasKey('email', $errors, 'Deve haver erro para o campo email');
        $this->assertArrayHasKey('cpf', $errors, 'Deve haver erro para o campo cpf');
    }
}
