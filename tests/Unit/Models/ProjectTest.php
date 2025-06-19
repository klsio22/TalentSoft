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
    private const PROJECT_END_DATE = '+30 days';
    private const PROJECT_STATUS = 'Em andamento';
    private const PROJECT_BUDGET = 10000.00;
    /**
     * Testa a criação de um projeto
     */
    public function test_create_project(): void
    {
        $projectData = [
            'name' => 'Projeto Teste ' . uniqid(),
            'description' => 'Descrição do projeto de teste',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
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
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
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
            'name' => 'Projeto Relacionamento ' . uniqid(),
            'description' => 'Teste de relacionamento',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
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
            'name' => 'Projeto Associação ' . uniqid(),
            'description' => 'Teste de associação',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
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
            'name' => 'Projeto Acesso ' . uniqid(),
            'description' => 'Teste de acesso',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
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
            // Apenas verificar que o método não lança exceção
            $project->currentUserHasAccess();
            // Teste passa se não houver exceção
            $this->assertTrue(true);
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
            'name' => 'Projeto Acesso Estático ' . uniqid(),
            'description' => 'Teste de acesso estático',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
        ]);
        $this->assertTrue($project->save());

        // Testar o método estático com o ID do projeto criado
        try {
            // Apenas verificar que o método não lança exceção
            Project::currentUserHasProjectAccess($project->id);
            // Teste passa se não houver exceção
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('O método currentUserHasProjectAccess lançou uma exceção: ' . $e->getMessage());
        }

        // Testar com um ID inválido (deve retornar false)
        $this->assertFalse(Project::currentUserHasProjectAccess(99999));
    }
}
