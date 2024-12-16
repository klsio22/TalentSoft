<?php

use PHPUnit\Framework\TestCase;
use Core\Router\Router;
use Lib\Authentication\Auth;

class UserControllerTest extends TestCase
{
  private const TEST_USER_EMAIL = 'user@example.com';
  private const TEST_USER_PASSWORD = 'user123';
  private const TEST_ADMIN_EMAIL = 'admin@example.com';
  private const TEST_ADMIN_PASSWORD = 'admin123';

  protected function setUp(): void
  {
    parent::setUp();

    // Limpar buffers existentes
    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    // Gerenciar sessão
    if (session_status() === PHP_SESSION_ACTIVE) {
      session_destroy();
      session_write_close();
    }
    session_start();

    // Limpar variáveis globais
    $_POST = [];
    $_GET = [];
    $_SERVER = [];

    // Configurar ambiente básico
    $_SERVER['HTTP_HOST'] = 'localhost:8081';
    $_SERVER['REQUEST_SCHEME'] = 'http';

    // Carregar dependências
    require __DIR__ . '/../../config/bootstrap.php';

    // Reinicializar o Router
    $this->resetRouter();

    require __DIR__ . '/../../config/routes.php';

    // Restaurar handlers padrão
    restore_error_handler();
    restore_exception_handler();
  }

  private function resetRouter(): void
  {
    // Reset necessário para isolar os testes
    $routerReflection = new \ReflectionClass(Router::class);
    $instanceProperty = $routerReflection->getProperty('instance');
    $instanceProperty->setValue(null, null);
  }


  protected function tearDown(): void
  {
    // Limpar buffers
    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    // Limpar sessão
    if (session_status() === PHP_SESSION_ACTIVE) {
      session_destroy();
      session_write_close();
    }

    // Restaurar handlers
    restore_error_handler();
    restore_exception_handler();

    parent::tearDown();
  }

  private function dispatch(): string
  {
    // Garantir buffer limpo antes
    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    ob_start();
    try {
      $output = Router::getInstance()->dispatch();
      return ob_get_clean();
    } catch (\Exception $e) {
      ob_end_clean();
      throw $e;
    } finally {
      // Garantir que não sobrem buffers
      while (ob_get_level() > 0) {
        ob_end_clean();
      }
    }
  }

  private function performLogin(string $email, string $password): string
  {
    $_POST['email'] = $email;
    $_POST['password'] = $password;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/';

    return $this->dispatch();
  }

  public function test_show_login_form()
  {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';

    $output = $this->dispatch();

    // Verificar elementos específicos do formulário de login
    $this->assertStringContainsString('<h1 class="text-2xl font-bold mb-6 text-center">Login</h1>', $output);
    $this->assertStringContainsString('<form method="POST" action="/">', $output);
    $this->assertStringContainsString('<input type="email" name="email"', $output);
    $this->assertStringContainsString('<input type="password" name="password"', $output);
  }

  public function test_user_login_and_redirect_to_home()
  {
    $output = $this->performLogin(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);

    $this->assertTrue(Auth::check(), 'Usuário deve estar autenticado');
    $this->assertEquals('user', Auth::user()->role, 'Role deve ser user');
    $this->assertStringContainsString('home', $output, 'Deve redirecionar para home');
  }

  public function test_admin_login_and_redirect_to_home_admin()
  {
    $_SERVER['REQUEST_URI'] = '/admin/login';
    $output = $this->performLogin(self::TEST_ADMIN_EMAIL, self::TEST_ADMIN_PASSWORD);

    $this->assertTrue(Auth::check(), 'Admin deve estar autenticado');
    $this->assertEquals('admin', Auth::user()->role, 'Role deve ser admin');
    $this->assertStringContainsString('home/admin', $output, 'Deve redirecionar para home/admin');
  }

  public function test_logout()
  {
    // Login
    $this->performLogin(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
    $this->assertTrue(Auth::check(), 'Usuário deve estar autenticado antes do logout');

    // Logout
    $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/logout';

    $output = $this->dispatch();

    $this->assertFalse(Auth::check(), 'Usuário não deve estar autenticado após logout');
    $this->assertStringContainsString('users/login', $output, 'Deve redirecionar para login');
  }
}
