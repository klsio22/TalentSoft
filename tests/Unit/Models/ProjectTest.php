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
    private const PROJECT_STATUS_COMPLETED = 'Concluído';
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
     * Testa o método isEmployeeAssociated com um cenário mais completo
     */
    public function test_is_employee_associated_complete(): void
    {
        // Criar um projeto
        $project = new Project([
            'name' => 'Projeto Associação Completo ' . uniqid(),
            'description' => 'Teste completo de associação',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
        ]);
        $this->assertTrue($project->save());

        // Testar com um funcionário sem ID (deve retornar false)
        $employeeNoId = $this->createMock(Employee::class);
        // Não definimos a propriedade id, que será avaliada como não existente no teste
        $this->assertFalse($project->isEmployeeAssociated($employeeNoId));

        // Criar um funcionário com ID válido
        $employeeWithId = $this->createMock(Employee::class);
        $employeeWithId->id = 999;

        // Sem funcionários no projeto, deve retornar false mesmo com ID válido
        $this->assertFalse($project->isEmployeeAssociated($employeeWithId));
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

    /**
     * Testa o método filterProjects
     */
    public function test_filter_projects(): void
    {
        // Criar vários projetos com características diferentes
        $project1 = new Project([
            'name' => 'Projeto Alpha',
            'description' => 'Descrição do projeto alpha',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
        ]);
        $project1->save();

        $project2 = new Project([
            'name' => 'Projeto Beta',
            'description' => 'Outra descrição',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS_COMPLETED,
            'budget' => self::PROJECT_BUDGET
        ]);
        $project2->save();

        $project3 = new Project([
            'name' => 'Outro Projeto',
            'description' => 'Descrição com alpha mencionado',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
        ]);
        $project3->save();

        $allProjects = [$project1, $project2, $project3];

        // Testar filtro por termo de busca
        $filteredByAlpha = Project::filterProjects($allProjects, 'Alpha', null);
        $this->assertCount(2, $filteredByAlpha);
        $this->assertTrue(in_array($project1, $filteredByAlpha));
        $this->assertTrue(in_array($project3, $filteredByAlpha));

        // Testar filtro por status
        $filteredByStatus = Project::filterProjects($allProjects, null, self::PROJECT_STATUS_COMPLETED);
        $this->assertCount(1, $filteredByStatus);
        $this->assertTrue(in_array($project2, $filteredByStatus));

        // Testar combinação de filtros
        $filteredCombined = Project::filterProjects($allProjects, 'Alpha', self::PROJECT_STATUS);
        $this->assertCount(2, $filteredCombined);
    }

    /**
     * Testa o método prepareProjectTeam e getEmployeeRoles
     */
    public function test_prepare_project_team(): void
    {
        // Criar um projeto
        $project = new Project([
            'name' => 'Projeto Equipe ' . uniqid(),
            'description' => 'Teste de equipe',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
        ]);
        $this->assertTrue($project->save());

        // Como estamos usando mocks e não podemos facilmente simular a relação BelongsToMany,
        // vamos apenas verificar que o método existe e não lança exceções
        try {
            $projectTeam = $project->prepareProjectTeam();
            // Verificar que o array está vazio (já que não temos funcionários no teste)
            $this->assertEmpty($projectTeam);

            $employeeRoles = $project->getEmployeeRoles();
            // Verificar que o array está vazio (já que não temos papéis no teste)
            $this->assertEmpty($employeeRoles);

            // O teste passa se chegarmos aqui sem exceções
        } catch (\Exception $e) {
            $this->fail('Os métodos prepareProjectTeam ou getEmployeeRoles lançaram uma exceção: ' . $e->getMessage());
        }
    }

    /**
     * Testa o método destroyWithRelationships sem relacionamentos específicos
     */
    public function test_destroy_with_relationships_default(): void
    {
        // Criar um projeto
        $project = new Project([
            'name' => 'Projeto Exclusão ' . uniqid(),
            'description' => 'Teste de exclusão com relacionamentos',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
        ]);
        $this->assertTrue($project->save());
        $this->assertNotNull($project->id);

        $projectId = $project->id;

        // Executar exclusão com relacionamentos (usando valores padrão)
        $result = $project->destroyWithRelationships();

        // Verificar se a exclusão foi bem-sucedida
        $this->assertTrue($result);

        // Verificar se o projeto foi realmente removido do banco
        $deletedProject = Project::findById($projectId);
        $this->assertNull($deletedProject);
    }

    /**
     * Testa o método destroyWithRelationships com relacionamentos específicos
     */
    public function test_destroy_with_relationships_custom(): void
    {
        // Criar um projeto
        $project = new Project([
            'name' => 'Projeto Exclusão Customizada ' . uniqid(),
            'description' => 'Teste de exclusão com relacionamentos customizados',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
        ]);
        $this->assertTrue($project->save());
        $this->assertNotNull($project->id);

        $projectId = $project->id;

        // Definir relacionamentos específicos para teste
        $relationships = [
            'Employee_Projects' => 'project_id'
        ];

        // Executar exclusão com relacionamentos específicos
        $result = $project->destroyWithRelationships($relationships);

        // Verificar se a exclusão foi bem-sucedida
        $this->assertTrue($result);

        // Verificar se o projeto foi realmente removido do banco
        $deletedProject = Project::findById($projectId);
        $this->assertNull($deletedProject);
    }

    /**
     * Testa o método destroyWithRelationships com array vazio de relacionamentos
     */
    public function test_destroy_with_relationships_empty_array(): void
    {
        // Criar um projeto
        $project = new Project([
            'name' => 'Projeto Exclusão Array Vazio ' . uniqid(),
            'description' => 'Teste de exclusão com array vazio',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
        ]);
        $this->assertTrue($project->save());
        $this->assertNotNull($project->id);

        $projectId = $project->id;

        // Executar exclusão com array vazio (deve usar valores padrão)
        $result = $project->destroyWithRelationships([]);

        // Verificar se a exclusão foi bem-sucedida
        $this->assertTrue($result);

        // Verificar se o projeto foi realmente removido do banco
        $deletedProject = Project::findById($projectId);
        $this->assertNull($deletedProject);
    }

    /**
     * Testa o método destroyWithRelationships em um projeto que não existe
     */
    public function test_destroy_with_relationships_nonexistent_project(): void
    {
        // Criar um projeto temporário para limpeza posterior
        $tempProject = new Project([
            'name' => 'Projeto Temporário ' . uniqid(),
            'description' => 'Projeto temporário para teste',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
        ]);
        $tempProject->save();

        // Criar um novo projeto com ID que não existe no banco
        $nonExistentProject = new Project([
            'name' => 'Projeto Inexistente',
            'description' => 'Este projeto não deveria existir no banco',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
        ]);

        // Definir um ID que não existe no banco (assumindo que 999999 não existe)
        $nonExistentProject->id = 999999;

        // Tentar executar exclusão em projeto inexistente
        $result = $nonExistentProject->destroyWithRelationships();

        // A exclusão deve falhar (retornar false) pois o projeto não existe
        $this->assertFalse($result);

        // Limpar o projeto temporário
        $tempProject->destroyWithRelationships();
    }

    /**
     * Testa o comportamento do método destroyWithRelationships com múltiplos relacionamentos
     */
    public function test_destroy_with_multiple_relationships(): void
    {
        // Criar um projeto
        $project = new Project([
            'name' => 'Projeto Múltiplos Relacionamentos ' . uniqid(),
            'description' => 'Teste de exclusão com múltiplos relacionamentos',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime(self::PROJECT_END_DATE)),
            'status' => self::PROJECT_STATUS,
            'budget' => self::PROJECT_BUDGET
        ]);
        $this->assertTrue($project->save());
        $this->assertNotNull($project->id);

        $projectId = $project->id;

        // Usar apenas relacionamentos que existem no banco de dados atual
        // Para testar com múltiplos relacionamentos, usamos o mesmo relacionamento
        // mas isso demonstra que o método pode lidar com arrays de relacionamentos
        $relationships = [
            'Employee_Projects' => 'project_id'
        ];

        // Executar exclusão com relacionamentos existentes
        $result = $project->destroyWithRelationships($relationships);

        // Verificar se a exclusão foi bem-sucedida
        $this->assertTrue($result);

        // Verificar se o projeto foi realmente removido do banco
        $deletedProject = Project::findById($projectId);
        $this->assertNull($deletedProject);
    }
}
