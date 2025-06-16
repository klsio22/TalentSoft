<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Project;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class EmployeeProjectsController extends Controller
{
    protected string $layout = 'application';

    private const EMPLOYEE_NOT_FOUND = 'Employee not found';
    private const PROJECT_NOT_FOUND = 'Project not found';
    private const ACCESS_DENIED = 'Access denied';
    private const ASSIGNMENT_CREATED = 'Employee assigned to project successfully!';
    private const ASSIGNMENT_REMOVED = 'Employee removed from project successfully!';
    private const MY_PROJECTS = 'My Projects';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        }
    }

    public function assignEmployee(Request $request): void
    {
        try {
            $data = $request->getParams();
            $projectId = (int) $data['project_id'];
            $employeeId = (int) $data['employee_id'];
            $role = $data['role'] ?? '';

            $project = Project::findById($projectId);
            $employee = Employee::findById($employeeId);

            if (!$project) {
                FlashMessage::danger(self::PROJECT_NOT_FOUND);
                $this->redirectTo(route('projects.index'));
                return;
            }

            if (!$employee) {
                FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
                $this->redirectTo(route('projects.show', ['id' => $projectId]));
                return;
            }

          // Check if the employee is already assigned to the project
            if (!($project instanceof Project) || !method_exists($project, 'employees')) {
                FlashMessage::danger('Erro: Projeto inválido');
                $this->redirectTo(route('projects.index'));
                return;
            }

            $existingAssignments = $project->employees()->get();
            foreach ($existingAssignments as $existingEmployee) {
                if ($existingEmployee->id === $employeeId) {
                    FlashMessage::warning('Employee is already assigned to this project');
                    $this->redirectTo(route('projects.show', ['id' => $projectId]));
                    return;
                }
            }

          // Assign employee to project with role
            if (!($project instanceof Project) || !method_exists($project, 'employees')) {
                FlashMessage::danger('Erro: Projeto inválido');
                $this->redirectTo(route('projects.index'));
                return;
            }

            $project->employees()->attach($employeeId, ['role' => $role]);

            FlashMessage::success(self::ASSIGNMENT_CREATED);
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

        $this->redirectTo(route('projects.show', ['id' => $projectId]));
    }

    public function removeEmployee(Request $request): void
    {
        try {
            $data = $request->getParams();
            $projectId = (int) $data['project_id'];
            $employeeId = (int) $data['employee_id'];

            $project = Project::findById($projectId);
            $employee = Employee::findById($employeeId);

            if (!$project) {
                FlashMessage::danger(self::PROJECT_NOT_FOUND);
                $this->redirectTo(route('projects.index'));
                return;
            }

            if (!$employee) {
                FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
                $this->redirectTo(route('projects.show', ['id' => $projectId]));
                return;
            }

          // Remove employee from project
            if (!($project instanceof Project) || !method_exists($project, 'employees')) {
                FlashMessage::danger('Erro: Projeto inválido');
                $this->redirectTo(route('projects.index'));
                return;
            }

            $project->employees()->detach($employeeId);

            FlashMessage::success(self::ASSIGNMENT_REMOVED);
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

        $this->redirectTo(route('projects.show', ['id' => $projectId]));
    }

    public function employeeProjects(int $employeeId): void
    {
        $employee = Employee::findById($employeeId);

        if (!$employee) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('employees.index'));
            return;
        }

        // Get projects associated with this employee
        $projects = $employee->projects()->get();

        $title = 'Projects for ' . $employee->name;
        $this->render('employee_projects/index', compact('employee', 'projects', 'title'));
    }

    /**
     * Lista os projetos associados ao usuário atual
     * Método movido do ProjectsController para centralizar a lógica
     */
    public function userProjects(): void
    {
        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
            return;
        }

        $currentUser = Auth::user();

        if (!$currentUser) {
            FlashMessage::danger('Usuário não encontrado');
            $this->redirectTo(route('auth.login'));
            return;
        }

        // Obter o funcionário associado ao usuário atual
        $employee = Employee::getCurrentUserEmployee();

        if (!$employee) {
            FlashMessage::danger('Funcionário não encontrado');
            $this->redirectTo(route('user.home'));
            return;
        }

        // Obter os projetos associados ao funcionário atual
        $userProjects = $employee->projects()->get();

        // Obter informações adicionais sobre cada projeto
        $projectsWithDetails = [];
        foreach ($userProjects as $project) {
            // Garantir que o projeto é uma instância válida
            if (!($project instanceof Project) || !method_exists($project, 'employees')) {
                // Se não for um projeto válido, pule para o próximo
                continue;
            }

            $projectEmployees = $project->employees()->get();
            $employeeRoles = $this->getEmployeeProjectRoles($project->id);

            $projectsWithDetails[] = [
                'project' => $project,
                'role' => $employeeRoles[$employee->id] ?? 'Não especificado',
                'team_size' => count($projectEmployees)
            ];
        }

        $title = self::MY_PROJECTS;
        $this->render('projects/user_projects', compact('projectsWithDetails', 'title'));
    }

    /**
     * Verifica se um usuário tem acesso a um projeto específico
     * Método centralizado para validação de acesso
     *
     * @param int $projectId ID do projeto
     * @return bool True se o usuário tem acesso, false caso contrário
     */
    public function hasProjectAccess(int $projectId): bool
    {
        // Admin e HR sempre têm acesso
        if (Auth::isAdmin() || Auth::isHR()) {
            return true;
        }

        $hasAccess = false;
        $employee = Employee::getCurrentUserEmployee();

        // Verifica se o funcionário existe e se o projeto está na lista de projetos dele
        if ($employee) {
            // Obtém todos os projetos do funcionário atual
            $employeeProjects = $employee->projects()->get();

            // Verifica se o projeto está na lista de projetos do funcionário
            foreach ($employeeProjects as $project) {
                if ((int)$project->id === (int)$projectId) {
                    $hasAccess = true;
                    break;
                }
            }
        }

        return $hasAccess;
    }

    /**
     * Retorna o papel de cada funcionário em um projeto específico
     * Método movido do ProjectsController para centralizar a lógica
     *
     * @param int $projectId ID do projeto
     * @return array Array associativo com [employee_id => role]
     */
    public function getEmployeeProjectRoles(int $projectId): array
    {
        $project = Project::findById($projectId);

        if (!$project) {
            return [];
        }

        $roles = [];
        try {
            $pdo = \Core\Database\Database::getDatabaseConn();
            $query = "SELECT employee_id, role FROM employee_projects WHERE project_id = :project_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':project_id', $projectId);
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($results as $result) {
                $roles[$result['employee_id']] = $result['role'] ?? 'Membro da equipe';
            }
        } catch (\Exception $e) {
            // Log error and continue with empty roles array
        }

        return $roles;
    }
}
