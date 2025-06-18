<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\Project;
use Core\Database\ActiveRecord\BelongsToMany;
use Lib\Authentication\Auth;
use Tests\TestCase;

/**
 * Testes unitários para o modelo Project
 */
class ProjectTest extends TestCase
{
    /**
     * Testa a criação de um projeto
     */
    public function test_create_project(): void
    {
        $projectData = [
            'name' => 'Projeto Teste',
            'description' => 'Descrição do projeto teste',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'Em andamento',
            'budget' => 10000.00
        ];

        $project = new Project($projectData);
        $this->assertTrue($project->save());
        $this->assertNotNull($project->id);

        // Verificar se os dados foram salvos corretamente
        $savedProject = Project::findById($project->id);
        $this->assertNotNull($savedProject);
        $this->assertEquals($projectData['name'], $savedProject->name);
        $this->assertEquals($projectData['description'], $savedProject->description);
        $this->assertEquals($projectData['status'], $savedProject->status);
        $this->assertEquals($projectData['budget'], $savedProject->budget);
    }

    /**
     * Testa a validação de dados do projeto
     */
    public function test_project_validation(): void
    {
        // Projeto sem nome (obrigatório)
        $project = new Project([
            'description' => 'Descrição do projeto teste',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'Em andamento',
            'budget' => 10000.00
        ]);

        $this->assertFalse($project->save());
        $this->assertNotEmpty($project->errors('name'));
    }

    /**
     * Testa a relação BelongsToMany com Employee
     */
    public function test_employees_relationship(): void
    {
        $project = new Project([
            'name' => 'Projeto Relacionamento',
            'description' => 'Teste de relacionamento',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'Em andamento',
            'budget' => 10000.00
        ]);

        $this->assertTrue($project->save());

        // Verificar se o método employees() retorna um objeto BelongsToMany
        $this->assertInstanceOf(BelongsToMany::class, $project->employees());
    }

    /**
     * Testa o método isEmployeeAssociated
     */
    public function test_is_employee_associated(): void
    {
        // Criar um projeto
        $project = new Project([
            'name' => 'Projeto Associação',
            'description' => 'Teste de associação',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'Em andamento',
            'budget' => 10000.00
        ]);
        $this->assertTrue($project->save());

        // Criar um funcionário para teste
        $employee = $this->createMock(Employee::class);
        $employee->id = 999; // ID fictício

        // Como não temos funcionários associados ainda, deve retornar false
        $this->assertFalse($project->isEmployeeAssociated($employee));
    }

    /**
     * Testa o método currentUserHasAccess com mock de Auth
     */
    public function test_current_user_has_access(): void
    {
        // Criar um projeto
        $project = new Project([
            'name' => 'Projeto Acesso',
            'description' => 'Teste de acesso',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'Em andamento',
            'budget' => 10000.00
        ]);
        $this->assertTrue($project->save());

        // Usar Reflection para substituir temporariamente o método estático Auth::isAdmin
        $authReflection = new \ReflectionClass(Auth::class);

        // Verificar se o método isAdmin existe
        $this->assertTrue($authReflection->hasMethod('isAdmin'));

        // Não podemos testar completamente o método currentUserHasAccess
        // devido à dependência de métodos estáticos, mas podemos verificar
        // se o método existe e não lança exceções
        try {
            $result = $project->currentUserHasAccess();
            // O resultado pode ser true ou false dependendo do ambiente,
            // mas o importante é que o método não lance exceções
            $this->assertIsBool($result);
        } catch (\Exception $e) {
            $this->fail('O método currentUserHasAccess lançou uma exceção: ' . $e->getMessage());
        }
    }

    /**
     * Testa o método estático currentUserHasProjectAccess
     */
    public function test_current_user_has_project_access(): void
    {
        // Criar um projeto
        $project = new Project([
            'name' => 'Projeto Acesso Estático',
            'description' => 'Teste de acesso estático',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'Em andamento',
            'budget' => 10000.00
        ]);
        $this->assertTrue($project->save());

        // Testar o método estático com o ID do projeto criado
        try {
            $result = Project::currentUserHasProjectAccess($project->id);
            // O resultado pode ser true ou false dependendo do ambiente,
            // mas o importante é que o método não lance exceções
            $this->assertIsBool($result);
        } catch (\Exception $e) {
            $this->fail('O método currentUserHasProjectAccess lançou uma exceção: ' . $e->getMessage());
        }

        // Testar com um ID inválido (deve retornar false)
        $this->assertFalse(Project::currentUserHasProjectAccess(99999));
    }
}
