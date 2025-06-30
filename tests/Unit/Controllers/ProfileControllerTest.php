<?php

namespace Tests\Unit\Controllers;

use App\Controllers\ProfileController;
use App\Models\Employee;
use App\Models\Role;
use Lib\Authentication\Auth;

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
        $this->mockRole = new Role([
            'name' => 'Test Role',
            'description' => 'Test Description'
        ]);
        $this->assertTrue($this->mockRole->save(), 'Falha ao salvar role do mock');

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
        $_FILES = [];

        if (isset($_SESSION['employee'])) {
            unset($_SESSION['employee']);
        }
    }

    /**
     * Testa o redirecionamento para login quando não há usuário autenticado
     */
    public function testConstructorRedirectsToLoginWhenNotAuthenticated(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['employee']);
        $this->assertFalse(Auth::check(), 'Não deve haver usuário logado');

        $mockController = $this->getMockBuilder(ProfileController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['redirectTo'])
            ->getMock();

        $mockController->expects($this->once())
            ->method('redirectTo')
            ->with($this->stringContains('login'));

        $reflection = new \ReflectionClass(ProfileController::class);
        $constructor = $reflection->getConstructor();
        $constructor->invoke($mockController);
    }

    /**
     * Testa se o método show redireciona para login quando não há usuário autenticado
     */
    public function testShowRedirectsToLoginWhenNoUserFound(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = 999;

        $controller = new class extends ProfileController {
            public function __construct()
            {
                /* Método vazio para evitar redirecionamentos do construtor original */
            }

            public string $redirectUrl = '';

            public function redirectTo(string $url): void
            {
                $this->redirectUrl = $url;
                echo "Redirect: $url";
            }

            public function render(string $view, array $data = []): void
            {
                /* Método vazio para evitar renderização da view */
            }
        };

        ob_start();
        $controller->show();
        $output = ob_get_clean();

        $this->assertStringContainsString('Redirect:', $output);
        $this->assertStringContainsString('login', $controller->redirectUrl);
    }

    /**
     * Testa se o perfil do usuário é exibido corretamente
     */
    public function testShowDisplaysUserProfile(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->mockEmployee->id;

        $controller = new class extends ProfileController {
            public function __construct()
            {
                // Método vazio para evitar redirecionamentos do construtor original
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

        ob_start();
        $controller->show();
        $output = ob_get_clean();

        $this->assertStringContainsString('View: profile/show', $output);
        $this->assertStringContainsString('Data[title]: Meu Perfil', $output);
        $this->assertStringContainsString('Data[user]: App\Models\Employee', $output);
    }

    /**
     * Testa se o upload de um arquivo inválido (PDF) é rejeitado corretamente
     */
    public function testUploadAvatarRejectsInvalidPdfFile(): void
    {
        // Usar o Employee real criado no setup
        $employee = $this->mockEmployee;
        $this->assertNotNull($employee, 'O mock de Employee deve existir');

        // Configurar a sessão para usar o funcionário real
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $employee->id;

        // Preparar arquivo de teste PDF
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_pdf_');
        $pdfPath = dirname(__DIR__, 2) . '/_data/exemple.pdf';
        $this->assertFileExists($pdfPath, 'O arquivo PDF de exemplo deve existir');
        file_put_contents($tmpFile, file_get_contents($pdfPath));

        // Configurar $_FILES para simular upload de PDF disfarçado como JPG
        $_FILES = [
            'avatar' => [
                'name' => 'fake_image.jpg',
                'type' => 'application/pdf',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmpFile)
            ]
        ];

        try {
            // Teste de integração: usar um controller real com redirecionamento simulado
            $controller = new class extends ProfileController {
                public string $redirectUrl = '';

                public function __construct()
                {
                    // Não chamar o construtor pai para evitar redirecionamentos
                }

                public function redirectTo(string $url): void
                {
                    $this->redirectUrl = $url;
                }
            };

            // Executar o método
            $controller->uploadAvatar();

            // Verificar se houve redirecionamento para o perfil
            $this->assertStringContainsString('profile', $controller->redirectUrl);

            // Verificar que o arquivo PDF realmente foi rejeitado
            // Verificar que o avatar_name não foi atualizado
            $updatedEmployee = Employee::findById($employee->id);
            $this->assertNull(
                $updatedEmployee->getAvatarName(),
                'O nome do avatar não deve ser atualizado para arquivos inválidos'
            );
        } finally {
            // Limpeza
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    /**
     * Testa se o upload com erro do PHP é tratado corretamente
     */
    public function testUploadAvatarHandlesPhpUploadError(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->mockEmployee->id;

        $_FILES = [
            'avatar' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '',
                'error' => UPLOAD_ERR_INI_SIZE,
                'size' => 0
            ]
        ];

        $controller = new class extends ProfileController {
            public string $redirectUrl = '';

            public function __construct()
            {
                // Método vazio para evitar redirecionamentos do construtor original
            }

            public function redirectTo(string $url): void
            {
                $this->redirectUrl = $url;
                echo "Redirect: $url";
            }
        };

        ob_start();
        $controller->uploadAvatar();
        ob_end_clean(); // Limpar o buffer sem armazenar a saída

        $this->assertStringContainsString('profile', $controller->redirectUrl);
    }

    /**
     * Testa se a remoção de avatar funciona corretamente
     */
    public function testRemoveAvatarSuccessfully(): void
    {
        // Usar o Employee real criado no setup
        $employee = $this->mockEmployee;
        $this->assertNotNull($employee, 'O mock de Employee deve existir');

        // Configurar a sessão para usar o funcionário real
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $employee->id;

        // Primeiro, definir um avatar para o funcionário
        $avatarName = 'Employees_' . $employee->id . '_avatar.jpg';
        $this->assertTrue(
            $employee->setAvatarName($avatarName),
            'Deve ser possível definir um nome de avatar'
        );

        // Verificar se o avatar foi definido
        $updatedEmployee = Employee::findById($employee->id);
        $this->assertEquals(
            $avatarName,
            $updatedEmployee->getAvatarName(),
            'O nome do avatar deve ser atualizado no banco de dados'
        );

        // Criar uma instância do controller com redirecionamento simulado
        $controller = new class extends ProfileController {
            public string $redirectUrl = '';

            public function __construct()
            {
                // Não chamar o construtor pai para evitar redirecionamentos
            }

            public function redirectTo(string $url): void
            {
                $this->redirectUrl = $url;
            }
        };

        // Executar o método de remoção
        $controller->removeAvatar();

        // Verificar se houve redirecionamento para o perfil
        $this->assertStringContainsString('profile', $controller->redirectUrl);

        // Verificar que o avatar foi removido do banco de dados
        $employeeAfterRemoval = Employee::findById($employee->id);
        $this->assertNull(
            $employeeAfterRemoval->getAvatarName(),
            'O nome do avatar deve ser removido após a remoção'
        );
    }

    /**
     * Testa o comportamento quando não há usuário autenticado na remoção de avatar
     */
    public function testRemoveAvatarWithNoUser(): void
    {
        // Garantir que não há usuário na sessão
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['employee']);

        // Verificar que não há usuário logado
        $this->assertFalse(Auth::check(), 'Não deve haver usuário logado para este teste');

        // Criar controller com redirecionamento simulado
        $controller = new class extends ProfileController {
            public string $redirectUrl = '';

            public function __construct()
            {
                // Não chamar o construtor pai para evitar redirecionamentos
            }

            public function redirectTo(string $url): void
            {
                $this->redirectUrl = $url;
                echo "Redirected to: $url";
            }
        };

        // Executar o método em teste capturando a saída
        ob_start();
        $controller->removeAvatar();
        $output = ob_get_clean();

        // Verificar se houve redirecionamento para a página de login
        $this->assertStringContainsString('login', $controller->redirectUrl);
        $this->assertStringContainsString('Redirected to:', $output);
    }
}
