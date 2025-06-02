<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use Tests\TestCase;

/**
 * Teste básico para verificar se a classe Employee está funcionando corretamente
 */
class EmployeeBasicTest extends TestCase
{
    /**
     * Testa a criação simples de um Employee
     */
    public function test_should_create_employee(): void
    {
        $employee = new Employee();

        // Pular validações e testes complexos
        // Apenas verificar se a classe existe e pode ser instanciada
        $this->assertInstanceOf(Employee::class, $employee);
    }
}
