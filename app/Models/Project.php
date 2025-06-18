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
        if (!$employee->id) {
            return false;
        }

        $projectEmployees = $this->employees()->get();

        // Verificação mais robusta
        if (empty($projectEmployees)) {
            return false;
        }

        $employeeId = (int)$employee->id;

        foreach ($projectEmployees as $projectEmployee) {
            // Garantir que estamos comparando IDs como inteiros
            $projectEmployeeId = (int)$projectEmployee->id;

            if ($projectEmployeeId === $employeeId) {
                return true;
            }
        }

        return false;
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
}
