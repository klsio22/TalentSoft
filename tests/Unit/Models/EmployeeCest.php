<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\Role;
use App\Models\UserCredential;
use Core\Database\Database;
use PHPUnit\Framework\TestCase;

class EmployeeCest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::create();
        Database::migrate();

        $adminRole = new Role(['name' => 'admin', 'description' => 'Administrador']);
        $adminRole->save();

        $hrRole = new Role(['name' => 'hr', 'description' => 'RH']);
        $hrRole->save();

        $userRole = new Role(['name' => 'user', 'description' => 'Usuário']);
        $userRole->save();
    }

    protected function tearDown(): void
    {
        Database::drop();
        parent::tearDown();
    }

    public function testCreateEmployee(): void
    {
        $role = Role::findByName('user');
        $employee = new Employee([
            'name' => 'Teste Employee',
            'cpf' => '444.444.444-44',
            'email' => 'teste@example.com',
            'birth_date' => '1990-01-01',
            'role_id' => $role->id,
            'salary' => 5000.00,
            'hire_date' => '2023-01-01',
            'status' => 'Active',
            'address' => 'Rua Teste, 123',
            'city' => 'Cidade Teste',
            'state' => 'PR',
            'zipcode' => '12345-678',
            'notes' => 'Observações de teste'
        ]);

        $this->assertTrue($employee->save());
        $this->assertGreaterThan(0, $employee->id);

        $foundEmployee = Employee::findById($employee->id);
        $this->assertNotNull($foundEmployee);
        $this->assertEquals('Teste Employee', $foundEmployee->name);
        $this->assertEquals('teste@example.com', $foundEmployee->email);
    }

    public function testFindEmployeeByEmail(): void
    {
        $role = Role::findByName('user');
        $employee = new Employee([
            'name' => 'Teste Email',
            'cpf' => '555.555.555-55',
            'email' => 'email_teste@example.com',
            'role_id' => $role->id,
            'hire_date' => '2023-01-01',
            'status' => 'Active',
        ]);
        $employee->save();

        $foundEmployee = Employee::findByEmail('email_teste@example.com');
        $this->assertNotNull($foundEmployee);
        $this->assertEquals('Teste Email', $foundEmployee->name);
    }
}
