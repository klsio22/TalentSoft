<?php

namespace Tests\Unit\Controllers;

use App\Controllers\EmployeesController;
use App\Models\Employee;
use App\Models\Role;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Tests\TestCase;

/**
 * Testes unitários para o controlador EmployeesController
 */
class EmployeesControllerTest extends TestCase
{
    /**
     * Mock da classe Auth para simular um usuário autenticado
     */
    private function mockAuth(bool $isAdmin = true, bool $isHR = false): void
    {
        // Criar um mock da classe Auth
        $authMock = $this->getMockBuilder(Auth::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Configurar os métodos
        $authMock->method('check')->willReturn(true);
        $authMock->method('isAdmin')->willReturn($isAdmin);
        $authMock->method('isHR')->willReturn($isHR);
        $authMock->method('user')->willReturn(new Employee());

        // Definir o mock como estático
        $reflectionClass = new \ReflectionClass(Auth::class);
        $instanceProperty = $reflectionClass->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $authMock);
    }

    /**
     * Criar um role e um employee para os testes
     * @return array [Role, Employee]
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
     * Configurar o ambiente para simular uma requisição
     */

    /**
     * Testa se o controlador renderiza a lista de funcionários
     */
    public function test_index_renders_employee_list(): void
    {
        // Criar dados de teste
        [$role, $employee] = $this->createTestData();

        // Criar uma instância do controlador
        $controller = new EmployeesController();

        // Capturar a saída do método index
        $output = $this->getOutput(function () use ($controller) {
            $controller->index();
        });

        // Verificar se a saída contém elementos esperados
        $this->assertStringContainsString('Lista de Funcionários', $output);
        $this->assertStringContainsString($employee->name, $output);
    }

    /**
     * Testa se o controlador renderiza o formulário de criação
     */
    public function test_create_renders_form(): void
    {
        // Criar dados de teste
        [$role, $employee] = $this->createTestData();

        // Criar uma instância do controlador
        $controller = new EmployeesController();

        // Capturar a saída do método create
        $output = $this->getOutput(function () use ($controller) {
            $controller->create();
        });

        // Verificar se a saída contém elementos esperados
        $this->assertStringContainsString('Novo Funcionário', $output);
        $this->assertStringContainsString('form', $output);
        $this->assertStringContainsString('action="/employees"', $output);
    }

    /**
     * Testa se o controlador exibe os detalhes de um funcionário
     */
    public function test_show_displays_employee_details(): void
    {
        // Criar dados de teste
        [$role, $employee] = $this->createTestData();

        // Simular parâmetros da requisição
        $_GET['id'] = $employee->id;

        // Criar uma instância do controlador e do request
        $controller = new EmployeesController();
        $request = new Request();

        // Capturar a saída do método show
        $output = $this->getOutput(function () use ($controller, $request) {
            $controller->show($request);
        });

        // Verificar se a saída contém elementos esperados
        $this->assertStringContainsString('Detalhes do Funcionário', $output);
        $this->assertStringContainsString($employee->name, $output);
        $this->assertStringContainsString($employee->email, $output);
    }

    /**
     * Testa se o controlador renderiza o formulário de edição
     */
    public function test_edit_renders_edit_form(): void
    {
        // Criar dados de teste
        [$role, $employee] = $this->createTestData();

        // Simular parâmetros da requisição
        $_GET['id'] = $employee->id;

        // Criar uma instância do controlador e do request
        $controller = new EmployeesController();
        $request = new Request();

        // Capturar a saída do método edit
        $output = $this->getOutput(function () use ($controller, $request) {
            $controller->edit($request);
        });

        // Verificar se a saída contém elementos esperados
        $this->assertStringContainsString('Editar Funcionário', $output);
        $this->assertStringContainsString('form', $output);
        $this->assertStringContainsString('value="' . $employee->name . '"', $output);
        $this->assertStringContainsString('value="' . $employee->email . '"', $output);
    }

    /**
     * Testa o armazenamento de um novo funcionário
     */
    public function test_store_creates_new_employee(): void
    {
        // Criar dados de teste
        [$role, $employee] = $this->createTestData();

        // Simular parâmetros da requisição
        $_POST = [
            'name' => 'New Employee',
            'email' => 'new@example.com',
            'cpf' => '99988877755',
            'birth_date' => '1991-02-02',
            'role_id' => $role->id,
            'hire_date' => date('Y-m-d'),
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Criar uma instância do controlador
        $controller = $this->getMockBuilder(EmployeesController::class)
            ->onlyMethods(['redirectTo'])
            ->getMock();

        // Configurar o mock para não redirecionar
        $controller->expects($this->once())
            ->method('redirectTo')
            ->willReturnCallback(function ($url) {
                return true;
            });

        // Executar o método store
        $request = new Request();
        $controller->store($request);

        // Verificar se o funcionário foi criado
        $newEmployee = Employee::findByEmail('new@example.com');
        $this->assertNotNull($newEmployee);
        $this->assertEquals('New Employee', $newEmployee->name);
    }

    /**
     * Testa a atualização de um funcionário
     */
    public function test_update_modifies_employee(): void
    {
        // Criar dados de teste
        [$role, $employee] = $this->createTestData();

        // Simular parâmetros da requisição
        $_POST = [
            'id' => $employee->id,
            'name' => 'Updated Employee',
            'email' => $employee->email,
            'cpf' => $employee->cpf,
            'birth_date' => $employee->birth_date,
            'role_id' => $employee->role_id,
            'hire_date' => $employee->hire_date,
            'status' => $employee->status
        ];
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Criar uma instância do controlador
        $controller = $this->getMockBuilder(EmployeesController::class)
            ->onlyMethods(['redirectTo'])
            ->getMock();

        // Configurar o mock para não redirecionar
        $controller->expects($this->once())
            ->method('redirectTo')
            ->willReturnCallback(function ($url) {
                return true;
            });

        // Executar o método update
        $request = new Request();
        $controller->update($request);

        // Verificar se o funcionário foi atualizado
        $updatedEmployee = Employee::findById($employee->id);
        $this->assertEquals('Updated Employee', $updatedEmployee->name);
    }

    /**
     * Testa a exclusão de um funcionário
     */
    public function test_destroy_removes_employee(): void
    {
        // Criar dados de teste
        [$role, $employee] = $this->createTestData();

        // Simular parâmetros da requisição
        $_POST['id'] = $employee->id;
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Criar uma instância do controlador
        $controller = $this->getMockBuilder(EmployeesController::class)
            ->onlyMethods(['redirectTo'])
            ->getMock();

        // Configurar o mock para não redirecionar
        $controller->expects($this->once())
            ->method('redirectTo')
            ->willReturnCallback(function ($url) {
                return true;
            });

        // Executar o método destroy
        $request = new Request();
        $controller->destroy($request);

        // Verificar se o funcionário foi removido
        $deletedEmployee = Employee::findById($employee->id);
        $this->assertNull($deletedEmployee);
    }
}
