<?php

namespace Tests\Unit\Controllers;

use App\Controllers\ErrorController;
use Core\Http\Request;
use PHPUnit\Framework\TestCase;

/**
 * Testes unitários para o controlador ErrorController
 */
class ErrorControllerTest extends TestCase
{
    /**
     * Testa se a página de erro 404 é exibida corretamente
     */
    public function testNotFoundDisplaysCorrectly(): void
    {
        // Criar controlador mockado
        $controller = new class extends ErrorController {
            public $viewData = [];
            public $viewName = '';

            public function render(string $view, array $data = []): void
            {
                $this->viewName = $view;
                $this->viewData = $data;
                echo "View: $view";
                foreach ($data as $key => $value) {
                    echo "\nData[$key]: $value";
                }
            }
        };

        // Executar método
        ob_start();
        $controller->notFound();
        $output = ob_get_clean();

        // Verificar se a view correta foi renderizada
        $this->assertEquals('errors/not_found', $controller->viewName);
        $this->assertEquals('Página não encontrada', $controller->viewData['title']);
        $this->assertStringContainsString('View: errors/not_found', $output);
    }

    /**
     * Testa se a página de erro 500 é exibida corretamente
     */
    public function testServerErrorDisplaysCorrectly(): void
    {
        // Criar controlador mockado
        $controller = new class extends ErrorController {
            public $viewData = [];
            public $viewName = '';

            public function render(string $view, array $data = []): void
            {
                $this->viewName = $view;
                $this->viewData = $data;
                echo "View: $view";
                foreach ($data as $key => $value) {
                    echo "\nData[$key]: $value";
                }
            }
        };

        // Executar método
        ob_start();
        $controller->serverError();
        $output = ob_get_clean();

        // Verificar se a view correta foi renderizada
        $this->assertEquals('errors/server_error', $controller->viewName);
        $this->assertEquals('Erro interno do servidor', $controller->viewData['title']);
        $this->assertStringContainsString('View: errors/server_error', $output);
    }
}
