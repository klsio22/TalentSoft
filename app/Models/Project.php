<?php

namespace App\Models;

use Core\Database\ActiveRecord\BelongsToMany;
use Core\Database\ActiveRecord\HasMany;
use Core\Database\ActiveRecord\Model;
use Lib\Authentication\Auth;
use Lib\Validations;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $start_date
 * @property string $end_date
 * @property string $status
 * @property float $budget
 */
class Project extends Model
{
    protected static string $table = 'Projects';
    protected static array $columns = ['name', 'description', 'start_date', 'end_date', 'status', 'budget'];

    public function validates(): void
    {
        Validations::notEmpty('name', $this);
    }

    public function employees(): BelongsToMany
    {
        return $this->BelongsToMany(
            Employee::class,
            'Employee_Projects',
            'project_id',
            'employee_id'
        );
    }

    /**
     * Verifica se um usuário tem acesso a este projeto
     *
     * @return bool True se o usuário tem acesso, false caso contrário
     */
    public function currentUserHasAccess(): bool
    {
        // Admin e HR sempre têm acesso
        if (Auth::isAdmin() || Auth::isHR()) {
            return true;
        }

        // Verificar se o usuário atual é um funcionário associado a este projeto
        $employee = Employee::getCurrentUserEmployee();
        if (!$employee) {
            return false;
        }

        return $this->isEmployeeAssociated($employee);
    }

    /**
     * Verifica se um funcionário está associado a este projeto
     *
     * @param Employee $employee Funcionário
     * @return bool True se o funcionário está associado, false caso contrário
     */
    public function isEmployeeAssociated(Employee $employee): bool
    {
        $isAssociated = false;

        // Só prosseguir se o funcionário tiver ID válido
        if ($employee->id && !empty($this->employees()->get())) {
            $employeeId = (int)$employee->id;
            $projectEmployees = $this->employees()->get();

            // Procurar o funcionário na lista de funcionários do projeto
            foreach ($projectEmployees as $projectEmployee) {
                $projectEmployeeId = (int)$projectEmployee->id;

                if ($projectEmployeeId === $employeeId) {
                    $isAssociated = true;
                    break;
                }
            }
        }

        return $isAssociated;
    }

    /**
     * Obtém todos os projetos associados a um funcionário específico
     *
     * @param int $employeeId ID do funcionário
     * @return array<int, Project> Lista de projetos do funcionário
     */
    public static function getEmployeeProjects(int $employeeId): array
    {
        $employee = Employee::findById($employeeId);

        if (!$employee) {
            return [];
        }

        $projects = $employee->projects()->get();

        // Garantir que todos os elementos do array são instâncias de Project
        $typedProjects = [];
        foreach ($projects as $project) {
            if ($project instanceof Project) {
                $typedProjects[] = $project;
            }
        }

        return $typedProjects;
    }

    /**
     * Verifica se um usuário tem acesso a um projeto específico (método estático)
     *
     * @param int $projectId ID do projeto
     * @return bool True se o usuário tem acesso, false caso contrário
     */
    public static function currentUserHasProjectAccess(int $projectId): bool
    {
        $project = self::findById($projectId);
        if (!$project) {
            return false;
        }

        return $project->currentUserHasAccess();
    }

    /**
     * Filtra projetos com base em critérios de busca e status
     *
     * @param array<Project> $projects Lista de projetos a serem filtrados
     * @param string|null $search Termo de busca (nome e descrição)
     * @param string|null $status Status do projeto
     * @return array<Project> Lista de projetos filtrados
     */
    public static function filterProjects(array $projects, ?string $search, ?string $status): array
    {
        return array_filter($projects, function ($project) use ($search, $status) {
            $matchesSearch = !$search || stripos($project->name, $search) !== false ||
                            stripos($project->description ?? '', $search) !== false;

            $matchesStatus = !$status || $project->status === $status;

            return $matchesSearch && $matchesStatus;
        });
    }

    /**
     * Prepara a equipe do projeto com informações adicionais para exibição
     *
     * @return array<int, array<string, mixed>> Equipe do projeto com detalhes sobre papéis
     */
    public function prepareProjectTeam(): array
    {
        $projectEmployees = $this->employees()->get();
        $employeeRoles = $this->getEmployeeRoles();

        $projectTeam = [];
        foreach ($projectEmployees as $employee) {
            $projectTeam[] = [
                'employee' => $employee,
                'role' => isset($employeeRoles[$employee->id]) ? $employeeRoles[$employee->id] : 'Membro da equipe'
            ];
        }

        return $projectTeam;
    }

    /**
     * Obtém os papéis dos funcionários associados a este projeto
     *
     * @return array<int, string> Array associativo com [employee_id => role]
     */
    public function getEmployeeRoles(): array
    {
        return EmployeeProject::getEmployeeProjectRoles($this->id);
    }

    /**
     * Deleta o projeto e todos os seus relacionamentos
     * Utiliza o método genérico da classe Model para deletar relacionamentos
     *
     * @param array<string, string> $relationships Array de relacionamentos a serem deletados
     * @return bool True se a exclusão foi bem-sucedida, false caso contrário
     */
    public function destroyWithRelationships(array $relationships = []): bool
    {
        // Se nenhum relacionamento for passado, usar os padrões do projeto
        if (empty($relationships)) {
            $relationships = [
                'Employee_Projects' => 'project_id'
            ];
        }

        // Usar o método genérico da classe pai
        return parent::destroyWithRelationships($relationships);
    }
}
