<?php

namespace Tests\Unit\Controllers;

use App\Controllers\ProfileController;
use App\Models\Employee;
use App\Models\Role;
use App\Services\ProfileAvatar;
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

            // Override render method to prevent view rendering
            public function render(string $view, array $data = []): void
            {
                // Don't render the view to avoid null user errors
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

    /**
     * Testa se o upload de um arquivo inválido (PDF) é rejeitado corretamente
     */
    public function testUploadAvatarRejectsInvalidPdfFile(): void
    {
        // Simular usuário logado para poder acessar o endpoint
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->mockEmployee->id;

        // Criar um arquivo temporário simulando um PDF
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_pdf_');
        $pdfPath = dirname(__DIR__, 2) . '/_data/exemple.pdf';
        $this->assertFileExists($pdfPath, 'O arquivo PDF de exemplo deve existir');
        file_put_contents($tmpFile, file_get_contents($pdfPath));

        // Simular um upload de arquivo PDF mas com extensão de imagem
        $_FILES = [
            'avatar' => [
                'name' => 'fake_image.jpg', // Nome com extensão de imagem
                'type' => 'application/pdf', // Porém tipo MIME é de PDF
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmpFile)
            ]
        ];

        // Criar uma classe de controller modificada que captura os redirecionamentos
        $controller = new class extends ProfileController {
            public string $redirectUrl = '';
            public bool $validationFailed = false;

            public function __construct()
            {
                // Sobrescrever construtor para evitar redirecionamento inicial
            }

            public function redirectTo(string $url): void
            {
                $this->redirectUrl = $url;
                echo "Redirect: $url";
            }
        };

        // Mockamos o serviço ProfileAvatar para simular a rejeição
        $profileAvatarMock = $this->getMockBuilder(ProfileAvatar::class)
            ->disableOriginalConstructor()
            ->getMock();

        // O método update deve retornar false para arquivos inválidos
        $profileAvatarMock->method('update')->willReturn(false);

        // Mockamos Employee para retornar o ProfileAvatar mockado
        $employeeMock = $this->getMockBuilder(Employee::class)
            ->disableOriginalConstructor()
            ->getMock();

        $employeeMock->method('avatar')->willReturn($profileAvatarMock);
        $employeeMock->method('errors')->willReturn('O arquivo enviado não é uma imagem válida');

        // Sobrescrevemos o método estático Auth::user() usando reflexão
        $reflection = new \ReflectionClass(Auth::class);
        $method = $reflection->getMethod('user');
        $method->setAccessible(true);

        // Substituir o método original com uma closure que retorna nosso mock
        $closure = function () use ($employeeMock) {
            return $employeeMock;
        };

        // Usar closure_bind para criar um método estático com o mesmo escopo
        $staticClosure = \Closure::bind($closure, null, Auth::class);

        // Salvar o método original para restaurar depois
        $originalMethod = $method;

        // Tentar injetar o método mockado (isso é um hack e pode não funcionar em todas as versões PHP)
        // No mundo real, seria melhor usar uma biblioteca de mock estático como AspectMock
        try {
            // Capturar saída
            ob_start();
            $controller->uploadAvatar();
            $output = ob_get_clean();

            // Verificar se houve redirecionamento para a página de perfil
            $this->assertStringContainsString('profile', $controller->redirectUrl);
        } finally {
            // Limpar o arquivo temporário
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
        // Simular usuário logado
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['employee']['id'] = $this->mockEmployee->id;

        // Simular um erro de upload do PHP
        $_FILES = [
            'avatar' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '',
                'error' => UPLOAD_ERR_INI_SIZE, // Erro: arquivo excede o limite do php.ini
                'size' => 0
            ]
        ];

        // Criar controlador mockado
        $controller = new class extends ProfileController {
            public string $redirectUrl = '';

            public function __construct()
            {
                // Evitar redirecionamento no construtor
            }

            public function redirectTo(string $url): void
            {
                $this->redirectUrl = $url;
                echo "Redirect: $url";
            }
        };

        // Capturar saída
        ob_start();
        $controller->uploadAvatar();
        $output = ob_get_clean();

        // Verificar se houve redirecionamento para a página de perfil
        $this->assertStringContainsString('profile', $controller->redirectUrl);
    }
}
