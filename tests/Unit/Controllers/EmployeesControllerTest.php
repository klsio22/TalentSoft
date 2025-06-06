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
    private Employee $mockEmployee;

    /**
     * Preparar o ambiente antes de cada teste
     */
    public function setUp(): void
    {
        parent::setUp();

        // Definir constante para evitar exit() em redirectTo
        if (!defined('PHPUNIT_TEST_RUNNING')) {
            define('PHPUNIT_TEST_RUNNING', true);
        }

        // Configurar ambiente de teste para Request
        $_SERVER['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/test';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_REQUEST = $_REQUEST ?? [];
        $_POST = $_POST ?? [];
        $_GET = $_GET ?? [];

        // Configurar sessão para autenticação
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Configurar o mock da autenticação
        $this->mockAuth();
    }

    public function tearDown(): void
    {
        // Limpar todos os buffers de output
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Limpar variáveis superglobais
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTPS']);
        $_REQUEST = [];
        $_POST = [];
        $_GET = [];

        // Limpar sessão
        if (isset($_SESSION['employee'])) {
            unset($_SESSION['employee']);
        }

        parent::tearDown();
    }

    /**
     * Configura os mocks para testes do controller
     * @param bool $isAdmin Se o usuário é admin
     */
    private function mockAuth(bool $isAdmin = true): void
    {
        // Criar um role de admin ou HR para o mock
        $roleData = $isAdmin
            ? ['name' => 'Admin', 'description' => 'Administrador']
            : ['name' => 'HR', 'description' => 'Recursos Humanos'];
        $role = new Role($roleData);
        $this->assertTrue($role->save(), 'Falha ao salvar role do mock');

        // Criar um empregado simulado para o Auth::user()
        $this->mockEmployee = new Employee([
            'name' => 'Mock User',
            'email' => 'mock@example.com',
            'status' => 'Active',
            'role_id' => $role->id,
            'cpf' => '11111111111',
            'birth_date' => '1990-01-01',
            'hire_date' => date('Y-m-d')
        ]);
        $this->assertTrue($this->mockEmployee->save(), 'Falha ao salvar employee do mock');

        // Simular login do usuário na sessão - VERIFICAR SE O ID FOI SALVO
        $this->assertNotNull($this->mockEmployee->id, 'Employee mock deve ter um ID válido');
        $_SESSION['employee']['id'] = $this->mockEmployee->id;

        // Verificar se a autenticação funciona
        $this->assertTrue(\Lib\Authentication\Auth::check(), 'Auth::check() deve retornar true após configurar sessão');
    }

    /**
     * Creates test data for the test cases
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
     * Configurar o ambiente para simular uma requisição
     */

    /**
     * Testa se o controlador renderiza a lista de funcionários
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_index_renders_employee_list(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Criar uma instância do controlador (mockAuth já é chamado no setUp)
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
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_create_renders_form(): void
    {
        // Criar dados de teste
        $this->createTestData();

        // Criar uma instância do controlador
        $controller = new EmployeesController();

        // Capturar a saída do método create
        $output = $this->getOutput(function () use ($controller) {
            $controller->create();
        });

        // Verificar se a saída contém elementos esperados
        $this->assertStringContainsString('Novo Funcionário', $output);
        $this->assertStringContainsString('form', $output);
        $this->assertStringContainsString('action="/mock-route/employees.store"', $output);
    }

    /**
     * Testa se o controlador exibe os detalhes de um funcionário
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_show_displays_employee_details(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Simular parâmetros da requisição através do $_REQUEST
        $_REQUEST['id'] = $employee->id;
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
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_edit_renders_edit_form(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Simular parâmetros da requisição através do $_REQUEST
        $_REQUEST['id'] = $employee->id;
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
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_store_creates_new_employee(): void
    {
        // Criar dados de teste
        [$role] = $this->createTestData();

        // Configurar requisição POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/employees/store';

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
        $_REQUEST = $_POST;

        // Criar uma instância do controlador
        $controller = $this->getMockBuilder(EmployeesController::class)
            ->onlyMethods(['redirectTo'])
            ->getMock();

        // Configurar o mock para não redirecionar
        $controller->expects($this->once())
            ->method('redirectTo')
            ->willReturnCallback(function () {
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
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_update_modifies_employee(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Configurar requisição POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/employees/update';

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
        $_REQUEST = $_POST;

        // Criar uma instância do controlador
        $controller = $this->getMockBuilder(EmployeesController::class)
            ->onlyMethods(['redirectTo'])
            ->getMock();

        // Configurar o mock para não redirecionar
        $controller->expects($this->once())
            ->method('redirectTo')
            ->willReturnCallback(function () {
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
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_destroy_removes_employee(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Configurar requisição POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/employees/destroy';

        // Simular parâmetros da requisição
        $_POST['id'] = $employee->id;
        $_REQUEST = $_POST;

        // Criar uma instância do controlador
        $controller = $this->getMockBuilder(EmployeesController::class)
            ->onlyMethods(['redirectTo'])
            ->getMock();

        // Configurar o mock para não redirecionar
        $controller->expects($this->once())
            ->method('redirectTo')
            ->willReturnCallback(function () {
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
