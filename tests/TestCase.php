<?php

namespace Tests;

use Core\Database\Database;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

class TestCase extends FrameworkTestCase
{
    /**
     * @var array<string, callable> Lista de funções originais que foram substituídas
     */
    private array $originalFunctions = [];

    public function setUp(): void
    {
        Database::create();
        Database::migrate();
    }

    public function tearDown(): void
    {
        // Restaurar as funções originais que foram mockadas
        foreach ($this->originalFunctions as $functionName => $originalFunction) {
            $this->restoreFunction($functionName);
        }

        Database::drop();
    }

    protected function getOutput(callable $callable): string
    {
        ob_start();
        $callable();
        return ob_get_clean();
    }

    /**
     * Define um mock para uma função global
     * @param string $functionName Nome da função a ser mockada
     * @param callable $mockFunction Função de mock
     */
    protected function setFunctionMock(string $functionName, callable $mockFunction): void
    {
        // Usar variável estática para armazenar o mock da função
        $GLOBALS['__function_mock_' . $functionName] = $mockFunction;

        // Registrar a função para restauração posterior
        if (!isset($this->originalFunctions[$functionName])) {
            $this->originalFunctions[$functionName] = true;
        }
    }

    /**
     * Restaura a implementação original de uma função
     * @param string $functionName Nome da função a ser restaurada
     */
    protected function restoreFunction(string $functionName): void
    {
        unset($GLOBALS['__function_mock_' . $functionName]);
    }
}
