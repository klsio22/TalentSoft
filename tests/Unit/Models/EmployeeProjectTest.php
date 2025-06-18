<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\EmployeeProject;
use App\Models\Project;
use App\Models\Role;
use Tests\TestCase;

/**
 * Testes unitários para o modelo EmployeeProject
 */
class EmployeeProjectTest extends TestCase
{
    private ?Employee $testEmployee = null;
    private ?Project $testProject = null;

    /**
     * Configura dados para teste
     */
    public function setUp(): void
    {
        parent::setUp();

        // Criar um cargo para o funcionário
        $role = new Role([
            'name' => 'Cargo Teste EP',
            'description' => 'Cargo para teste de EmployeeProject'
        ]);
        $role->save();

        // Criar um funcionário para teste
        $this->testEmployee = new Employee([
            'name' => 'Funcionário Teste EP',
            'email' => 'ep_test_' . uniqid() . '@example.com',
            'cpf' => uniqid(),
            'birth_date' => '1990-01-01',
            'role_id' => $role->id,
            'salary' => 5000.00,
            'hire_date' => date('Y-m-d'),
            'status' => 'Active'
        ]);
        $this->testEmployee->save();

        // Criar um projeto para teste
        $this->testProject = new Project([
            'name' => 'Projeto Teste EP',
            'description' => 'Projeto para teste de EmployeeProject',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'Em andamento',
            'budget' => 10000.00
        ]);
        $this->testProject->save();
    }

    /**
     * Testa a atribuição de um funcionário a um projeto
     */
    public function test_assign_employee_to_project(): void
    {
        // Atribuir funcionário ao projeto
        $result = EmployeeProject::assignEmployeeToProject(
            $this->testEmployee->id,
            $this->testProject->id,
            'Desenvolvedor'
        );

        $this->assertTrue($result);

        // Verificar se o funcionário foi atribuído corretamente
        $isAssigned = EmployeeProject::isEmployeeAssignedToProject(
            $this->testEmployee->id,
            $this->testProject->id
        );

        $this->assertTrue($isAssigned);

        // Tentar atribuir novamente (deve falhar)
        $result = EmployeeProject::assignEmployeeToProject(
            $this->testEmployee->id,
            $this->testProject->id,
            'Desenvolvedor'
        );

        $this->assertFalse($result);
    }

    /**
     * Testa a remoção de um funcionário de um projeto
     */
    public function test_remove_employee_from_project(): void
    {
        // Atribuir funcionário ao projeto
        EmployeeProject::assignEmployeeToProject(
            $this->testEmployee->id,
            $this->testProject->id,
            'Desenvolvedor'
        );

        // Verificar se o funcionário foi atribuído
        $isAssigned = EmployeeProject::isEmployeeAssignedToProject(
            $this->testEmployee->id,
            $this->testProject->id
        );

        $this->assertTrue($isAssigned);

        // Remover funcionário do projeto
        $result = EmployeeProject::removeEmployeeFromProject(
            $this->testEmployee->id,
            $this->testProject->id
        );

        $this->assertTrue($result);

        // Verificar se o funcionário foi removido
        $isAssigned = EmployeeProject::isEmployeeAssignedToProject(
            $this->testEmployee->id,
            $this->testProject->id
        );

        $this->assertFalse($isAssigned);
    }

    /**
     * Testa a verificação se um funcionário está atribuído a um projeto
     */
    public function test_is_employee_assigned_to_project(): void
    {
        // Inicialmente o funcionário não está atribuído
        $isAssigned = EmployeeProject::isEmployeeAssignedToProject(
            $this->testEmployee->id,
            $this->testProject->id
        );

        $this->assertFalse($isAssigned);

        // Atribuir funcionário ao projeto
        EmployeeProject::assignEmployeeToProject(
            $this->testEmployee->id,
            $this->testProject->id,
            'Desenvolvedor'
        );

        // Agora o funcionário deve estar atribuído
        $isAssigned = EmployeeProject::isEmployeeAssignedToProject(
            $this->testEmployee->id,
            $this->testProject->id
        );

        $this->assertTrue($isAssigned);
    }

    /**
     * Testa a obtenção dos projetos de um funcionário com detalhes
     */
    public function test_get_employee_projects_with_details(): void
    {
        // Atribuir funcionário ao projeto
        EmployeeProject::assignEmployeeToProject(
            $this->testEmployee->id,
            $this->testProject->id,
            'Desenvolvedor'
        );

        // Obter projetos com detalhes
        $projectsWithDetails = EmployeeProject::getEmployeeProjectsWithDetails($this->testEmployee);

        // Verificar que temos resultados
        $this->assertNotEmpty($projectsWithDetails, 'O array de projetos não deve estar vazio');

        // Verificar estrutura do resultado
        $firstProject = $projectsWithDetails[0];
        $this->assertArrayHasKey('project', $firstProject);
        $this->assertArrayHasKey('role', $firstProject);
        $this->assertArrayHasKey('team_size', $firstProject);

        // Verificar dados do projeto
        $this->assertEquals($this->testProject->id, $firstProject['project']->id);
        $this->assertEquals('Desenvolvedor', $firstProject['role']);
        $this->assertEquals(1, $firstProject['team_size']);
    }

    /**
     * Testa a atualização do papel de um funcionário em um projeto
     */
    public function test_update_employee_role(): void
    {
        // Atribuir funcionário ao projeto
        EmployeeProject::assignEmployeeToProject(
            $this->testEmployee->id,
            $this->testProject->id,
            'Desenvolvedor'
        );

        // Atualizar papel do funcionário
        $result = EmployeeProject::updateEmployeeRole(
            $this->testEmployee->id,
            $this->testProject->id,
            'Gerente de Projeto'
        );

        $this->assertTrue($result);

        // Verificar se o papel foi atualizado
        $employeeProject = EmployeeProject::findEmployeeProject(
            $this->testEmployee->id,
            $this->testProject->id
        );

        $this->assertNotNull($employeeProject);
        /** @var string $employeeProjectRole */
        $employeeProjectRole = $employeeProject->role;
        $this->assertEquals('Gerente de Projeto', $employeeProjectRole);
    }

    /**
     * Testa a busca de um registro de relação entre funcionário e projeto
     */
    public function test_find_employee_project(): void
    {
        // Inicialmente não deve existir relação
        $employeeProject = EmployeeProject::findEmployeeProject(
            $this->testEmployee->id,
            $this->testProject->id
        );

        $this->assertNull($employeeProject);

        // Atribuir funcionário ao projeto
        EmployeeProject::assignEmployeeToProject(
            $this->testEmployee->id,
            $this->testProject->id,
            'Desenvolvedor'
        );

        // Agora deve existir uma relação
        $employeeProject = EmployeeProject::findEmployeeProject(
            $this->testEmployee->id,
            $this->testProject->id
        );

        $this->assertNotNull($employeeProject);
        /** @var int $employeeId */
        $employeeId = $employeeProject->employeeId;
        /** @var int $projectId */
        $projectId = $employeeProject->projectId;
        /** @var string $role */
        $role = $employeeProject->role;

        $this->assertEquals($this->testEmployee->id, $employeeId);
        $this->assertEquals($this->testProject->id, $projectId);
        $this->assertEquals('Desenvolvedor', $role);
    }
}
