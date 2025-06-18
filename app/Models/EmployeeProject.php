<?php

namespace App\Models;

use Core\Database\ActiveRecord\Model;
use Core\Database\Database;
use Lib\Authentication\Auth;

/**
 * Model para gerenciar a relação entre funcionários e projetos
 */
class EmployeeProject extends Model
{
    protected static string $table = 'Employee_Projects';
    protected static array $columns = ['employee_id', 'project_id', 'role'];

    /**
     * Atribui um funcionário a um projeto com um papel específico
     *
     * @param int $employeeId ID do funcionário
     * @param int $projectId ID do projeto
     * @param string $role Papel do funcionário no projeto
     * @return bool True se a atribuição foi bem-sucedida, false caso contrário
     */
    public static function assignEmployeeToProject(int $employeeId, int $projectId, string $role = ''): bool
    {
        $project = Project::findById($projectId);
        $employee = Employee::findById($employeeId);

        if (!$project || !$employee) {
            return false;
        }

        // Verificar se o funcionário já está atribuído ao projeto
        if (self::isEmployeeAssignedToProject($employeeId, $projectId)) {
            return false;
        }

        // Atribuir funcionário ao projeto com papel
        $project->employees()->attach($employeeId, ['role' => $role]);
        return true;
    }

    /**
     * Remove um funcionário de um projeto
     *
     * @param int $employeeId ID do funcionário
     * @param int $projectId ID do projeto
     * @return bool True se a remoção foi bem-sucedida, false caso contrário
     */
    public static function removeEmployeeFromProject(int $employeeId, int $projectId): bool
    {
        $project = Project::findById($projectId);
        $employee = Employee::findById($employeeId);

        if (!$project || !$employee) {
            return false;
        }

        $project->employees()->detach($employeeId);
        return true;
    }

    /**
     * Verifica se um funcionário está atribuído a um projeto
     *
     * @param int $employeeId ID do funcionário
     * @param int $projectId ID do projeto
     * @return bool True se o funcionário está atribuído, false caso contrário
     */
    public static function isEmployeeAssignedToProject(int $employeeId, int $projectId): bool
    {
        $project = Project::findById($projectId);

        if (!$project) {
            return false;
        }

        $existingAssignments = $project->employees()->get();
        foreach ($existingAssignments as $existingEmployee) {
            if ((int)$existingEmployee->id === $employeeId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtém os projetos de um funcionário com detalhes adicionais
     *
     * @param Employee $employee Funcionário
     * @return array Array de projetos com detalhes
     */
    public static function getEmployeeProjectsWithDetails(Employee $employee): array
    {
        if (!$employee) {
            return [];
        }

        $userProjects = $employee->projects()->get();
        $projectsWithDetails = [];

        foreach ($userProjects as $project) {
            // Garantir que o projeto é uma instância válida
            if (!($project instanceof Project) || !method_exists($project, 'employees')) {
                continue;
            }

            $projectEmployees = $project->employees()->get();
            $employeeRoles = self::getEmployeeProjectRoles($project->id);

            $projectsWithDetails[] = [
                'project' => $project,
                'role' => $employeeRoles[$employee->id] ?? 'Não especificado',
                'team_size' => count($projectEmployees)
            ];
        }

        return $projectsWithDetails;
    }

    /**
     * Verifica se um usuário tem acesso a um projeto específico
     *
     * @param int $projectId ID do projeto
     * @return bool True se o usuário tem acesso, false caso contrário
     */
    public static function currentUserHasProjectAccess(int $projectId): bool
    {
        return Project::currentUserHasProjectAccess($projectId);
    }

    /**
     * Retorna o papel de cada funcionário em um projeto específico
     *
     * @param int $projectId ID do projeto
     * @return array Array associativo com [employee_id => role]
     */
    public static function getEmployeeProjectRoles(int $projectId): array
    {
        $roles = [];
        try {
            $pdo = Database::getDatabaseConn();
            $query = "SELECT employee_id, role FROM Employee_Projects WHERE project_id = :project_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':project_id', $projectId);
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($results as $result) {
                $roles[$result['employee_id']] = !empty($result['role']) ? $result['role'] : 'Membro da equipe';
            }
        } catch (\Exception $e) {
            // Log error and continue with empty roles array
            error_log("Erro ao buscar roles dos funcionários: " . $e->getMessage());
        }

        return $roles;
    }
}
