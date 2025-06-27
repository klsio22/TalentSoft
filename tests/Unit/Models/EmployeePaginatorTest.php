<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\EmployeePaginator;
use App\Models\Role;
use Tests\TestCase;

class EmployeePaginatorTest extends TestCase
{
    /** @var array<int, Employee> */
    private array $employees = [];

    public function setUp(): void
    {
        parent::setUp();

        // Criar um cargo para os funcionários
        $role = new Role(['name' => 'Developer']);
        $role->save();

        // Criar 15 funcionários para teste
        for ($i = 1; $i <= 15; $i++) {
            $employee = new Employee([
                'name' => "Funcionário Teste $i",
                'email' => "funcionario$i@example.com",
                'cpf' => "1234567890$i",
                'role_id' => $role->id,
                'hire_date' => '2025-01-01'
            ]);
            $employee->save();
            $this->employees[] = $employee;
        }
    }

    public function test_paginate_creates_paginator_object(): void
    {
        $paginator = EmployeePaginator::paginate($this->employees, 1, 10);

        // Verificamos se o objeto tem os métodos esperados
        $this->assertObjectHasMethod('items', $paginator);
        $this->assertObjectHasMethod('total', $paginator);
        $this->assertObjectHasMethod('getPage', $paginator);
        $this->assertObjectHasMethod('perPage', $paginator);
        $this->assertObjectHasMethod('getTotalPages', $paginator);
    }

    public function test_items_returns_paginated_employees(): void
    {
        $perPage = 5;
        $paginator = EmployeePaginator::paginate($this->employees, 1, $perPage);

        $items = $paginator->items();

        // Verifica se temos exatamente 5 itens na primeira página
        $this->assertCount($perPage, $items);

        // Verifica se são os primeiros 5 funcionários
        for ($i = 0; $i < $perPage; $i++) {
            $this->assertEquals($this->employees[$i]->id, $items[$i]->id);
        }
    }

    public function test_total_returns_total_employees_count(): void
    {
        $paginator = EmployeePaginator::paginate($this->employees, 1, 10);

        $this->assertEquals(count($this->employees), $paginator->total());
        $this->assertEquals(count($this->employees), $paginator->totalOfRegisters());
    }

    public function test_get_page_returns_correct_page(): void
    {
        $page = 2;
        $paginator = EmployeePaginator::paginate($this->employees, $page, 5);

        $this->assertEquals($page, $paginator->getPage());
    }

    public function test_per_page_returns_correct_per_page(): void
    {
        $perPage = 7;
        $paginator = EmployeePaginator::paginate($this->employees, 1, $perPage);

        $this->assertEquals($perPage, $paginator->perPage());
    }

    public function test_get_total_pages_calculates_correctly(): void
    {
        // 15 registros, 7 por página = 3 páginas (2 completas e 1 com 1 registro)
        $paginator = EmployeePaginator::paginate($this->employees, 1, 7);

        $this->assertEquals(3, $paginator->getTotalPages());
    }

    public function test_total_of_registers_of_page(): void
    {
        // Primeira página deve ter 10 registros
        $paginator1 = EmployeePaginator::paginate($this->employees, 1, 10);
        $this->assertEquals(10, $paginator1->totalOfRegistersOfPage());

        // Segunda página deve ter 5 registros (restantes)
        $paginator2 = EmployeePaginator::paginate($this->employees, 2, 10);
        $this->assertEquals(5, $paginator2->totalOfRegistersOfPage());
    }

    public function test_paginate_with_empty_array(): void
    {
        $paginator = EmployeePaginator::paginate([], 1, 10);

        $this->assertCount(0, $paginator->items());
        $this->assertEquals(0, $paginator->total());
        $this->assertEquals(0, $paginator->totalOfRegisters());
        $this->assertEquals(1, $paginator->getPage()); // Página deve ser 1 mesmo sem itens
        $this->assertEquals(0, $paginator->getTotalPages()); // Sem itens, 0 páginas
    }

    public function test_paginate_with_page_out_of_range(): void
    {
        // Se página for maior que o total, deve retornar última página
        $paginator = EmployeePaginator::paginate($this->employees, 100, 5);

        // Deve ajustar para a última página (3 com 15 itens e 5 por página)
        $this->assertEquals(3, $paginator->getPage());
        $this->assertCount(5, $paginator->items());
    }

    /**
     * Helper para verificar se um objeto tem um método específico
     */
    private function assertObjectHasMethod(string $method, object $object): void
    {
        $this->assertTrue(
            method_exists($object, $method),
            "O objeto não possui o método '$method'"
        );
    }
}
