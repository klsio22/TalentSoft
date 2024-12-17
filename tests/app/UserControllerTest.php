<?php

use PHPUnit\Framework\TestCase;
use Core\Router\Router;

class UserControllerTest extends TestCase
{
  protected function setUp(): void
  {
    parent::setUp();

    // Restaurar handlers padrão
    restore_error_handler();
    restore_exception_handler();

    // Limpar buffers pendentes
    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    // Configurar ambiente de teste
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['HTTP_HOST'] = '127.0.0.1:8081';
    $_SERVER['REQUEST_SCHEME'] = 'http';

    // Incluir arquivos necessários
    require __DIR__ . '/../../config/bootstrap.php';
    require __DIR__ . '/../../config/routes.php';
  }

  protected function tearDown(): void
  {
    // Restaurar handlers padrão
    restore_error_handler();
    restore_exception_handler();

    // Limpar buffers pendentes
    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    parent::tearDown();
  }

  public function test_connection_to_root(): void
  {
    // Espera que a saída contenha o título da página de login
    $this->expectOutputRegex('/<title>TalentSoft<\/title>/i');

    // Executar a ação sob teste
    Router::getInstance()->dispatch();
  }
}
