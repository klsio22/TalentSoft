<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Core\Router\Router;

class RouterTest extends TestCase
{
  public function setUp(): void
  {
    parent::setUp();
    session_start();

    // Reinicializar o Router
    $routerReflection = new \ReflectionClass(Router::class);
    $instanceProperty = $routerReflection->getProperty('instance');
    $instanceProperty->setValue(null, null);

    // Carregar as rotas usando o caminho correto
    $routesPath = __DIR__ . '/../../../../config/routes.php';

    if (!file_exists($routesPath)) {
      throw new \RuntimeException("Arquivo de rotas não encontrado em: " . $routesPath);
    }

    require $routesPath;

    $_SERVER['HTTP_HOST'] = 'localhost:8081';
    $_SERVER['REQUEST_SCHEME'] = 'http';
    $this->assertTrue(true);
  }

  public function tearDown(): void
  {
    while (ob_get_level() > 0) {
      ob_end_clean();
    }

    session_destroy();

    $_SERVER['REQUEST_METHOD'] = null;
    $_SERVER['REQUEST_URI'] = null;

    parent::tearDown();
    $this->assertTrue(true);
  }

  public function dispatch(): string
  {
    ob_start();
    try {
      Router::getInstance()->dispatch();
      return ob_get_clean();
    } catch (\Exception $e) {
      ob_end_clean();
      throw $e;
    }
  }

  public function test_simple_route()
  {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/test';

    $output = $this->dispatch();
    $this->assertStringContainsString('Test route', $output);
    $this->assertTrue(true);
  }
}
