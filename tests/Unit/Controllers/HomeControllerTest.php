<?php

namespace Tests\Unit\Controllers;

use App\Controllers\HomeController;
use App\Models\Employee;
use App\Models\Role;
use Lib\Authentication\Auth;
use Tests\TestCase;

/**
 * Testes unitários para o controlador HomeController
 */
class HomeControllerTest extends ControllerTestCase
{
    /** @phpstan-ignore-next-line */
    private Employee $mockEmployee;
    private Role $mockRole;
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
     * Testa se a página inicial é exibida corretamente para visitantes não autenticados
     */
    public function testIndexDisplaysHomePageForGuests(): void
    {
        // Garantir que não há usuário logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['employee']);

        // Criar controlador
        $controller = new class extends HomeController {
            public function render(string $view, array $data = []): void
            {
                echo "View: $view";
                foreach ($data as $key => $value) {
                    echo "\nData[$key]: $value";
                }
            }

            public function redirectTo(string $url): void
            {
                echo "Redirect: $url";
            }
        };

        // Capturar saída
        ob_start();
        $controller->index();
        $output = ob_get_clean();

        // Verificar se a view correta é renderizada
        $this->assertStringContainsString('View: home/index', $output);
        $this->assertStringContainsString('Data[title]: TalentSoft - Sistema de Gestão de Talentos', $output);
    }

    /**
     * Testa o redirecionamento para a página de admin quando um admin está logado
     */
    public function testIndexRedirectsToAdminHomeWhenAdminIsLoggedIn(): void
    {
        // Simular usuário admin logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->adminEmployee->id;
        $this->assertTrue(Auth::check(), 'O usuário deve estar logado');
        $this->assertTrue(Auth::isAdmin(), 'O usuário deve ser admin');

        // Criar controlador
        $controller = new class extends HomeController {
            public function render(string $view, array $data = []): void
            {
                echo "View: $view";
            }

            public function redirectTo(string $url): void
            {
                echo "Redirect: $url";
            }
        };

        // Capturar saída
        ob_start();
        $controller->index();
        $output = ob_get_clean();

        // Verificar se há redirecionamento para a página de admin
        $this->assertStringContainsString('Redirect:', $output);
        $this->assertStringContainsString('admin', $output);
    }

    /**
     * Testa o redirecionamento para a página de HR quando um usuário HR está logado
     */
    public function testIndexRedirectsToHRHomeWhenHRIsLoggedIn(): void
    {
        // Simular usuário HR logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->hrEmployee->id;
        $this->assertTrue(Auth::check(), 'O usuário deve estar logado');
        $this->assertTrue(Auth::isHR(), 'O usuário deve ser HR');

        // Criar controlador
        $controller = new class extends HomeController {
            public function render(string $view, array $data = []): void
            {
                echo "View: $view";
            }

            public function redirectTo(string $url): void
            {
                echo "Redirect: $url";
            }
        };

        // Capturar saída
        ob_start();
        $controller->index();
        $output = ob_get_clean();

        // Verificar se há redirecionamento para a página de HR
        $this->assertStringContainsString('Redirect:', $output);
        $this->assertStringContainsString('hr', $output);
    }

    /**
     * Testa o redirecionamento para a página de usuário quando um usuário regular está logado
     */
    public function testIndexRedirectsToUserHomeWhenUserIsLoggedIn(): void
    {
        // Simular usuário regular logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->userEmployee->id;
        $this->assertTrue(Auth::check(), 'O usuário deve estar logado');
        $this->assertTrue(Auth::isUser(), 'O usuário deve ser um usuário regular');

        // Criar controlador
        $controller = new class extends HomeController {
            public function render(string $view, array $data = []): void
            {
                echo "View: $view";
            }

            public function redirectTo(string $url): void
            {
                echo "Redirect: $url";
            }
        };

        // Capturar saída
        ob_start();
        $controller->index();
        $output = ob_get_clean();

        // Verificar se há redirecionamento para a página de usuário
        $this->assertStringContainsString('Redirect:', $output);
        $this->assertStringContainsString('user', $output);
    }
}
