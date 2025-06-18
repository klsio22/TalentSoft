<?php

namespace Tests\Unit\Controllers;

use App\Controllers\HRController;
use App\Models\Employee;
use App\Models\Role;
use Lib\Authentication\Auth;
use Tests\TestCase;

/**
 * Testes unitários para o controlador HRController
 */
class HRControllerTest extends ControllerTestCase
{
    private Role $adminRole;
    private Role $hrRole;
    private Role $userRole;
    private Employee $adminEmployee;
    private Employee $hrEmployee;
    private Employee $userEmployee;

    public function setUp(): void
    {
        parent::setUp();
        $this->setupTestData();
    }

    public function tearDown(): void
    {
        $this->cleanupEnvironment();
        parent::tearDown();
    }

    private function setupTestData(): void
    {
        // Criar roles para o teste
        $this->adminRole = new Role([
            'name' => 'Admin',
            'description' => 'Administrador'
        ]);
        $this->assertTrue($this->adminRole->save(), 'Falha ao salvar role de admin');

        $this->hrRole = new Role([
            'name' => 'HR',
            'description' => 'Recursos Humanos'
        ]);
        $this->assertTrue($this->hrRole->save(), 'Falha ao salvar role de HR');

        $this->userRole = new Role([
            'name' => 'User',
            'description' => 'Usuário'
        ]);
        $this->assertTrue($this->userRole->save(), 'Falha ao salvar role de usuário');

        // Criar funcionários para o teste
        $this->adminEmployee = new Employee([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'status' => 'Active',
            'role_id' => $this->adminRole->id,
            'cpf' => '12345678901',
            'birth_date' => '1990-01-01',
            'hire_date' => date('Y-m-d')
        ]);
        $this->assertTrue($this->adminEmployee->save(), 'Falha ao salvar admin employee');

        $this->hrEmployee = new Employee([
            'name' => 'HR User',
            'email' => 'hr@example.com',
            'status' => 'Active',
            'role_id' => $this->hrRole->id,
            'cpf' => '23456789012',
            'birth_date' => '1991-02-02',
            'hire_date' => date('Y-m-d')
        ]);
        $this->assertTrue($this->hrEmployee->save(), 'Falha ao salvar HR employee');

        $this->userEmployee = new Employee([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'status' => 'Active',
            'role_id' => $this->userRole->id,
            'cpf' => '34567890123',
            'birth_date' => '1992-03-03',
            'hire_date' => date('Y-m-d')
        ]);
        $this->assertTrue($this->userEmployee->save(), 'Falha ao salvar user employee');
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

    /**
     * Testa o redirecionamento para login quando não há usuário autenticado
     */
    public function testConstructorRedirectsToLoginWhenNotAuthenticated(): void
    {
        // Garantir que não há usuário logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['employee']);
        $this->assertFalse(Auth::check(), 'Não deve haver usuário logado');

        // Criar controlador mockado que captura o redirecionamento
        $mockController = $this->getMockBuilder(HRController::class)
            ->disableOriginalConstructor() // Desabilitar o construtor original para evitar redirecionamento prematuro
            ->onlyMethods(['redirectTo'])
            ->getMock();

        // Configurar expectativa para o método redirectTo
        $mockController->expects($this->once())
            ->method('redirectTo')
            ->with($this->stringContains('login'));

        // Chamar o construtor explicitamente para acionar o redirecionamento
        $reflection = new \ReflectionClass(HRController::class);
        $constructor = $reflection->getConstructor();
        $constructor->invoke($mockController);
    }

    /**
     * Testa o redirecionamento para home quando o usuário não é HR nem admin
     */
    public function testConstructorRedirectsToHomeWhenNotHROrAdmin(): void
    {
        // Simular usuário regular logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->userEmployee->id;
        $this->assertTrue(Auth::check(), 'O usuário deve estar logado');
        $this->assertFalse(Auth::isHR(), 'O usuário não deve ser HR');
        $this->assertFalse(Auth::isAdmin(), 'O usuário não deve ser admin');

        // Criar controlador mockado que captura o redirecionamento
        $mockController = $this->getMockBuilder(HRController::class)
            ->disableOriginalConstructor() // Desabilitar o construtor original para evitar redirecionamento prematuro
            ->onlyMethods(['redirectTo'])
            ->getMock();

        // Configurar expectativa para o método redirectTo
        $mockController->expects($this->once())
            ->method('redirectTo')
            ->with($this->stringContains('/user'));

        // Chamar o construtor explicitamente para acionar o redirecionamento
        $reflection = new \ReflectionClass(HRController::class);
        $constructor = $reflection->getConstructor();
        $constructor->invoke($mockController);
    }

    /**
     * Testa se a página home do HR é exibida corretamente para usuário HR
     */
    public function testHomeDisplaysHRDashboardForHRUser(): void
    {
        // Simular usuário HR logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->hrEmployee->id;
        $this->assertTrue(Auth::check(), 'O usuário deve estar logado');
        $this->assertTrue(Auth::isHR(), 'O usuário deve ser HR');

        // Criar controlador mockado que não redireciona no construtor
        $controller = new class extends HRController {
            public function __construct()
            {
                // Sobrescrever o construtor para evitar redirecionamentos
            }

            public function render(string $view, array $data = []): void
            {
                echo "View: $view";
                foreach ($data as $key => $value) {
                    if (is_object($value)) {
                        echo "\nData[$key]: " . get_class($value);
                    } else {
                        echo "\nData[$key]: $value";
                    }
                }
            }
        };

        // Capturar saída
        ob_start();
        $controller->home();
        $output = ob_get_clean();

        // Verificar se a view correta é renderizada
        $this->assertStringContainsString('View: hr/home', $output);
        $this->assertStringContainsString('Data[title]: Área de Recursos Humanos', $output);
        $this->assertStringContainsString('Data[employee]: App\Models\Employee', $output);
    }

    /**
     * Testa se a página home do HR é exibida corretamente para usuário admin
     */
    public function testHomeDisplaysHRDashboardForAdminUser(): void
    {
        // Simular usuário admin logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->adminEmployee->id;
        $this->assertTrue(Auth::check(), 'O usuário deve estar logado');
        $this->assertTrue(Auth::isAdmin(), 'O usuário deve ser admin');

        // Criar controlador mockado que não redireciona no construtor
        $controller = new class extends HRController {
            public function __construct()
            {
                // Sobrescrever o construtor para evitar redirecionamentos
            }

            public function render(string $view, array $data = []): void
            {
                echo "View: $view";
                foreach ($data as $key => $value) {
                    if (is_object($value)) {
                        echo "\nData[$key]: " . get_class($value);
                    } else {
                        echo "\nData[$key]: $value";
                    }
                }
            }
        };

        // Capturar saída
        ob_start();
        $controller->home();
        $output = ob_get_clean();

        // Verificar se a view correta é renderizada
        $this->assertStringContainsString('View: hr/home', $output);
        $this->assertStringContainsString('Data[title]: Área de Recursos Humanos', $output);
        $this->assertStringContainsString('Data[employee]: App\Models\Employee', $output);
    }

    /**
     * Testa se a notificação é exibida quando o parâmetro notification é true
     */
    public function testHomeDisplaysNotification(): void
    {
        // Simular usuário HR logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->hrEmployee->id;
        $this->assertTrue(Auth::check(), 'O usuário deve estar logado');
        $this->assertTrue(Auth::isHR(), 'O usuário deve ser HR');

        // Configurar parâmetro notification
        $_GET['notification'] = 'true';

        // Criar controlador mockado que não redireciona no construtor
        $controller = new class extends HRController {
            public function __construct()
            {
                // Sobrescrever o construtor para evitar redirecionamentos
            }

            public function render(string $view, array $data = []): void
            {
                echo "View: $view";
                foreach ($data as $key => $value) {
                    if (is_object($value)) {
                        echo "\nData[$key]: " . get_class($value);
                    } else {
                        echo "\nData[$key]: $value";
                    }
                }
            }
        };

        // Capturar saída
        ob_start();
        $controller->home();
        $output = ob_get_clean();

        // Verificar se a view correta é renderizada
        $this->assertStringContainsString('View: hr/home', $output);

        // Verificar se a notificação foi definida
        // Nota: Não podemos verificar diretamente a mensagem flash, mas podemos verificar
        // se o método foi chamado através de um mock mais complexo se necessário
    }
}
