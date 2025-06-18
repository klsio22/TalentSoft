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
     * ID do funcionário associado ao projeto
     * @var int
     * @property-read int $employee_id Nome original da coluna no banco de dados
     */
    public int $employeeId;

    /**
     * ID do projeto associado ao funcionário
     * @var int
     * @property-read int $project_id Nome original da coluna no banco de dados
     */
    public int $projectId;

    /**
     * Papel/função do funcionário no projeto
     * @var string
     */
    public string $role;

    /**
     * Método mágico para acessar propriedades usando os nomes originais das colunas
     * @param string $name Nome da propriedade
     * @return mixed Valor da propriedade
     */
    public function __get(string $name): mixed
    {
        if ($name === 'employee_id') {
            return $this->employeeId;
        }
        if ($name === 'project_id') {
            return $this->projectId;
        }
        return parent::__get($name);
    }

    /**
     * Método mágico para definir propriedades usando os nomes originais das colunas
     * @param string $name Nome da propriedade
     * @param mixed $value Valor a ser definido
     */
    public function __set(string $name, $value): void
    {
        if ($name === 'employee_id') {
            $this->employeeId = $value;
            return;
        }
        if ($name === 'project_id') {
            $this->projectId = $value;
            return;
        }
        parent::__set($name, $value);
    }

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
   * @return array<int, array<string, mixed>> Array de projetos com detalhes
   */
    public static function getEmployeeProjectsWithDetails(Employee $employee): array
    {
        // O objeto Employee já foi verificado antes de chamar este método
        // então não precisamos verificar novamente

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
   * @return array<int, string> Array associativo com [employee_id => role]
   */
    public static function getEmployeeProjectRoles(int $projectId): array
    {
        $roles = [];
        try {
          // Usando o método where do framework para buscar os registros
            $employeeProjects = self::where([
            'project_id' => $projectId
            ]);

          // Construir o array associativo com employee_id => role
            foreach ($employeeProjects as $employeeProject) {
                  $roleValue = $employeeProject->role;
                  $roles[$employeeProject->employeeId] = !empty($roleValue) ? $roleValue : 'Membro da equipe';
            }
        } catch (\Exception $e) {
          // Log error and continue with empty roles array
            error_log("Erro ao buscar roles dos funcionários: " . $e->getMessage());
        }

        return $roles;
    }

  /**
   * Atualiza o papel de um funcionário em um projeto específico
   *
   * @param int $employeeId ID do funcionário
   * @param int $projectId ID do projeto
   * @param string $newRole Novo papel do funcionário no projeto
   * @return bool True se a atualização foi bem-sucedida, false caso contrário
   */
    public static function updateEmployeeRole(int $employeeId, int $projectId, string $newRole): bool
    {
        try {
          // Verificar se o funcionário está atribuído ao projeto e buscar o registro
            if (
                !self::isEmployeeAssignedToProject($employeeId, $projectId) ||
                ($employeeProject = self::findEmployeeProject($employeeId, $projectId)) === null
            ) {
                return false;
            }

          // Atualizar o papel usando o model
            $employeeProject->role = $newRole;
            return $employeeProject->save();
        } catch (\Exception $e) {
            error_log("Erro ao atualizar papel do funcionário: " . $e->getMessage());
            return false;
        }
    }

  /**
   * Busca o registro da relação entre funcionário e projeto
   *
   * @param int $employeeId ID do funcionário
   * @param int $projectId ID do projeto
   * @return EmployeeProject|null Instância da relação ou null se não existir
   */
    public static function findEmployeeProject(int $employeeId, int $projectId): ?self
    {
      // Usando o método findBy do framework para buscar pela condição composta
        return self::findBy([
        'employee_id' => $employeeId,
        'project_id' => $projectId
        ]);
    }

  /**
   * Verifica se o usuário atual é administrador ou RH
   *
   * @return bool True se o usuário é admin ou RH, false caso contrário
   */
    public static function currentUserCanManageRoles(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

      // Obter o funcionário associado ao usuário
        $employee = Employee::getCurrentUserEmployee();
        if (!$employee) {
            return false;
        }

      // Verificar se o funcionário tem papel de administrador ou RH
        return $employee->isAdmin() || $employee->isHR();
    }
}
