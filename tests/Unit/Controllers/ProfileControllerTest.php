<?php

namespace Tests\Unit\Controllers;

use App\Controllers\ProfileController;
use App\Models\Employee;
use App\Models\Role;
use Lib\Authentication\Auth;
use Tests\TestCase;

/**
 * Testes unitários para o controlador ProfileController
 */
class ProfileControllerTest extends ControllerTestCase
{
    private Employee $mockEmployee;
    private Role $mockRole;

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
        // Criar role para o teste
        $this->mockRole = new Role([
            'name' => 'Test Role',
            'description' => 'Test Description'
        ]);
        $this->assertTrue($this->mockRole->save(), 'Falha ao salvar role do mock');

        // Criar funcionário para o teste
        $this->mockEmployee = new Employee([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'Active',
            'role_id' => $this->mockRole->id,
            'cpf' => '12345678901',
            'birth_date' => '1990-01-01',
            'hire_date' => date('Y-m-d')
        ]);
        $this->assertTrue($this->mockEmployee->save(), 'Falha ao salvar employee do mock');
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
        $mockController = $this->getMockBuilder(ProfileController::class)
            ->disableOriginalConstructor() // Desabilitar o construtor original para evitar redirecionamento prematuro
            ->onlyMethods(['redirectTo'])
            ->getMock();

        // Configurar expectativa para o método redirectTo
        $mockController->expects($this->once())
            ->method('redirectTo')
            ->with($this->stringContains('login'));

        // Chamar o construtor explicitamente para acionar o redirecionamento
        $reflection = new \ReflectionClass(ProfileController::class);
        $constructor = $reflection->getConstructor();
        $constructor->invoke($mockController);
    }

    /**
     * Testa se o método show redireciona para login quando não há usuário autenticado
     */
    public function testShowRedirectsToLoginWhenNoUserFound(): void
    {
        // Simular usuário logado mas Auth::user() retorna null
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = 999; // ID inexistente

        // Criar controlador mockado
        $controller = new class extends ProfileController {
            public function __construct()
            {
                // Sobrescrever o construtor para evitar redirecionamentos
            }

            public string $redirectUrl = '';

            public function redirectTo(string $url): void
            {
                $this->redirectUrl = $url;
                echo "Redirect: $url";
            }
        };

        // Capturar saída
        ob_start();
        $controller->show();
        $output = ob_get_clean();

        // Verificar se há redirecionamento para login
        $this->assertStringContainsString('Redirect:', $output);
        $this->assertStringContainsString('login', $controller->redirectUrl);
    }

    /**
     * Testa se o perfil do usuário é exibido corretamente
     */
    public function testShowDisplaysUserProfile(): void
    {
        // Simular usuário logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->mockEmployee->id;
        $this->assertTrue(Auth::check(), 'O usuário deve estar logado');

        // Criar controlador mockado
        $controller = new class extends ProfileController {
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
        $controller->show();
        $output = ob_get_clean();

        // Verificar se a view correta é renderizada
        $this->assertStringContainsString('View: profile/show', $output);
        $this->assertStringContainsString('Data[title]: Meu Perfil', $output);
        $this->assertStringContainsString('Data[user]: App\Models\Employee', $output);
    }
}
