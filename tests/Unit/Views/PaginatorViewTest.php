<?php

namespace Tests\Unit\Views;

use App\Models\Employee;
use App\Models\EmployeePaginator;
use App\Models\Role;
use Lib\Paginator;
use Tests\TestCase;

class PaginatorViewTest extends TestCase
{
    private string $paginatorPath;
    /** @var array<int, Employee> */
    private array $employees = [];

    public function setUp(): void
    {
        parent::setUp();
        if (!defined('PHPUNIT_RUNNING')) {
            define('PHPUNIT_RUNNING', true);
        }
        $this->paginatorPath = \Core\Constants\Constants::rootPath()->join('app/views/paginator/_pages.phtml');

        // Criar um cargo para os funcionários
        $role = new Role(['name' => 'Tester']);
        $role->save();

        // Criar funcionários para teste
        for ($i = 1; $i <= 20; $i++) {
            $employee = new Employee([
                'name' => "Funcionário View $i",
                'email' => "view$i@example.com",
                'cpf' => "9876543210$i",
                'role_id' => $role->id,
                'hire_date' => '2025-01-01'
            ]);
            $employee->save();
            $this->employees[] = $employee;
        }
    }

    public function test_paginator_file_exists(): void
    {
        $this->assertFileExists($this->paginatorPath);
    }

    public function test_paginator_view_renders_with_lib_paginator(): void
    {
        $paginator = new Paginator(Employee::class, 2, 5, 'Employees', ['name']);

        // Capturar a saída do renderPagesNavigation
        ob_start();
        $paginator->renderPagesNavigation();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Mostrando', $output);
        $this->assertStringContainsString('<nav', $output);
        $this->assertStringContainsString('aria-label="Página 1"', $output); // Link para página 1
        $this->assertStringContainsString('aria-current="page"', $output); // Página atual
    }

    public function test_paginator_view_renders_with_employee_paginator(): void
    {
        $employeePaginator = EmployeePaginator::paginate($this->employees, 2, 5);

        // Incluir o arquivo do paginador diretamente
        ob_start();
        $paginator = $employeePaginator; // Nome da variável esperada no partial
        require $this->paginatorPath;
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('Mostrando', $output);
        $this->assertStringContainsString('<nav', $output);

        // Verificar navegação
        $this->assertStringContainsString('Anterior', $output);
        $this->assertStringContainsString('Próxima', $output);
    }

    public function test_paginator_displays_correct_page_range(): void
    {
        $employeePaginator = EmployeePaginator::paginate($this->employees, 2, 5);

        ob_start();
        $paginator = $employeePaginator;
        require $this->paginatorPath;
        $output = ob_get_clean();

        // Para 20 registros com 5 por página = 4 páginas
        for ($i = 1; $i <= 4; $i++) {
            if ($i == 2) {
                // A página atual deve estar destacada
                $this->assertStringContainsString('aria-current="page"', $output);
            } else {
                // Outras páginas devem ser links
                $this->assertStringContainsString("aria-label=\"Página $i\"", $output);
            }
        }
    }

    public function test_paginator_shows_disabled_navigation_when_appropriate(): void
    {
        // Teste para primeira página (botão anterior desabilitado)
        $paginator1 = EmployeePaginator::paginate($this->employees, 1, 10);

        ob_start();
        $paginator = $paginator1;
        require $this->paginatorPath;
        $output1 = ob_get_clean();

        $this->assertStringContainsString('cursor-not-allowed', $output1);

        // Teste para última página (botão próximo desabilitado)
        $paginator2 = EmployeePaginator::paginate($this->employees, 2, 10);

        ob_start();
        $paginator = $paginator2;
        require $this->paginatorPath;
        $output2 = ob_get_clean();

        $this->assertStringContainsString('cursor-not-allowed', $output2);
    }

    public function test_paginator_entries_info_displays_correctly(): void
    {
        // Para 20 registros, página 2 com 7 por página:
        // Deve mostrar de 8-14 de 20
        $paginator = EmployeePaginator::paginate($this->employees, 2, 7);

        ob_start();
        require $this->paginatorPath;
        $output = ob_get_clean();

        $this->assertStringContainsString('Mostrando 8 - 14 de 20', $output);
    }
}
