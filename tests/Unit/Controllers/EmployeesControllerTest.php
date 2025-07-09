<?php

namespace Tests\Unit\Controllers;

use App\Controllers\EmployeesController;
use App\Models\Employee;
use App\Models\Role;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Testes unitários para o controlador EmployeesController
 */
class EmployeesControllerTest extends TestCase
{
    private Employee $mockEmployee;
    private Role $mockRole;

    public function setUp(): void
    {
        parent::setUp();

        // Definir constante para evitar exit() em redirectTo
        if (!defined('PHPUNIT_TEST_RUNNING')) {
            define('PHPUNIT_TEST_RUNNING', true);
        }

        $this->setupServerEnvironment();
        $this->setupSession();
        $this->mockAuth();
    }

    public function tearDown(): void
    {
        $this->cleanupEnvironment();
        parent::tearDown();
    }

    private function setupServerEnvironment(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    private function setupSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function cleanupEnvironment(): void
    {
        $_REQUEST = [];
        $_POST = [];
        $_GET = [];

        if (isset($_SESSION['employee'])) {
            unset($_SESSION['employee']);
        }
    }

    private function mockAuth(bool $isAdmin = true): void
    {
        $roleData = $isAdmin
            ? ['name' => 'Admin', 'description' => 'Administrador']
            : ['name' => 'HR', 'description' => 'Recursos Humanos'];

        $this->mockRole = new Role($roleData);
        $this->assertTrue($this->mockRole->save(), 'Falha ao salvar role do mock');

        $this->mockEmployee = new Employee([
            'name' => 'Mock User',
            'email' => 'mock@example.com',
            'status' => 'Active',
            'role_id' => $this->mockRole->id,
            'cpf' => '11111111111',
            'birth_date' => '1990-01-01',
            'hire_date' => date('Y-m-d')
        ]);
        $this->assertTrue($this->mockEmployee->save(), 'Falha ao salvar employee do mock');

        $_SESSION['employee']['id'] = $this->mockEmployee->id;
        $this->assertTrue(Auth::check(), 'Auth::check() deve retornar true após configurar sessão');
    }

    /**
     * @return array{Role, Employee}
     */
    private function createTestData(): array
    {
        $role = new Role(['name' => 'Test Role', 'description' => 'Test Description']);
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
     * Cria um controlador real para testes que precisam de funcionalidade completa
     */
    private function createRealController(): EmployeesController
    {
        // Usar um controller real para testes de view
        return new class extends EmployeesController {
            public function redirectTo(string $url): void
            {
                // Sobrescrever o método redirectTo para evitar redirecionamentos reais
                // durante os testes, mas ainda assim permitir que o teste capture a saída
                echo "Redirecionando para: $url";
            }
        };
    }

    /**
     * Testa se o controlador renderiza a lista de funcionários
     */
    public function testIndexRendersEmployeeList(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Criar controlador real
        $controller = $this->createRealController();

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
    public function testCreateRendersForm(): void
    {
        // Criar dados de teste
        $this->createTestData();

        // Criar controlador real
        $controller = $this->createRealController();

        // Capturar a saída do método create
        $output = $this->getOutput(function () use ($controller) {
            $controller->create();
        });

        // Verificar se a saída contém elementos esperados
        $this->assertStringContainsString('Novo Funcionário', $output);
        $this->assertStringContainsString('form', $output);
    }

    /**
     * Testa se o controlador exibe os detalhes de um funcionário
     */
    public function testShowDisplaysEmployeeDetails(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Simular parâmetros da requisição
        $_REQUEST['id'] = $employee->id;
        $_GET['id'] = $employee->id;

        // Criar controlador real
        $controller = $this->createRealController();

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
    public function testEditRendersEditForm(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Simular parâmetros da requisição
        $_REQUEST['id'] = $employee->id;
        $_GET['id'] = $employee->id;

        // Criar controlador real
        $controller = $this->createRealController();

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
    public function testStoreCreatesNewEmployee(): void
    {
        // Criar dados de teste
        [$role] = $this->createTestData();

        // Configurar requisição POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
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

        // Criar controlador mockado
        $controller = $this->getMockBuilder(EmployeesController::class)
            ->onlyMethods(['redirectTo'])
            ->getMock();

        $controller->expects($this->once())
            ->method('redirectTo');

        // Executar o método store
        $request = new Request();
        $controller->store($request);

        // Verificar se o funcionário foi criado
        $newEmployee = Employee::findByEmail('new@example.com');
        $this->assertNotNull($newEmployee);
        $this->assertEquals('New Employee', $newEmployee->name);
    }

    /**
     * Testa a desativação de um funcionário (soft delete)
     */
    public function testDestroyRemovesEmployee(): void
    {
        // Criar dados de teste
        [, $employee] = $this->createTestData();

        // Configurar requisição POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['id'] = $employee->id;
        $_REQUEST = $_POST;

        // Garantir que o usuário autenticado seja diferente do funcionário a ser desativado
        // Isso evita o erro de tentar desativar o próprio usuário
        $this->assertNotEquals(
            $employee->id,
            $this->mockEmployee->id,
            'O ID do funcionário de teste deve ser diferente do ID do usuário autenticado'
        );

        // Verificar se o Auth está funcionando corretamente
        $this->assertTrue(Auth::check(), 'O usuário deve estar autenticado');
        $this->assertNotNull(Auth::user(), 'Auth::user() deve retornar um usuário');
        $this->assertNotEquals(
            $employee->id,
            Auth::user()->id,
            'O usuário autenticado deve ser diferente do funcionário a ser desativado'
        );

        // Criar controlador mockado
        $controller = $this->getMockBuilder(EmployeesController::class)
            ->onlyMethods(['redirectTo'])
            ->getMock();

        $controller->expects($this->once())
            ->method('redirectTo');

        // Executar o método destroy
        $request = new Request();
        $controller->destroy($request);

        // Verificar se o funcionário foi desativado (soft delete)
        $deactivatedEmployee = Employee::findById($employee->id);
        $this->assertNotNull($deactivatedEmployee, 'O funcionário não deve ser removido fisicamente');
        $this->assertEquals('Inactive', $deactivatedEmployee->status, 'O status do funcionário deve ser Inactive');
    }
}
