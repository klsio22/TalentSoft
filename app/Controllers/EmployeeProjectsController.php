<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\EmployeeProject;
use App\Models\Project;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class EmployeeProjectsController extends Controller
{
    protected string $layout = 'application';

    public const EMPLOYEE_NOT_FOUND = 'Funcionário não encontrado';
    public const PROJECT_NOT_FOUND = 'Projeto não encontrado';
    public const ASSIGNMENT_CREATED = 'Funcionário atribuído ao projeto com sucesso!';
    public const ASSIGNMENT_REMOVED = 'Funcionário removido do projeto com sucesso!';
    public const MY_PROJECTS = 'Meus Projetos';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        }
    }

    public function assignEmployee(Request $request): void
    {
      // Inicializar variáveis para evitar erro de variável não definida
        $projectId = 0;
        $redirectUrl = '';

        try {
            $data = $request->getParams();
            $projectId = (int) $data['project_id'];
            $employeeId = (int) $data['employee_id'];
            $role = $data['role'] ?? '';
            $redirectUrl = route('projects.show', ['id' => $projectId]);

            $project = Project::findById($projectId);
            $employee = Employee::findById($employeeId);

          // Validate project and employee
            if (!$project) {
                FlashMessage::danger(self::PROJECT_NOT_FOUND);
                $this->redirectTo(route('projects.index'));
                return;
            }

            if (!$employee) {
                FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
                $this->redirectTo($redirectUrl);
                return;
            }

          // Attempt to assign the employee to the project using the model
            $result = EmployeeProject::assignEmployeeToProject($employeeId, $projectId, $role);

            if ($result) {
                FlashMessage::success(self::ASSIGNMENT_CREATED);
            } else {
                FlashMessage::warning('Funcionário já está atribuído a este projeto');
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

      // Corrigido: Usar verificação de string vazia em vez de operador de coalescência nula
        $this->redirectTo($redirectUrl !== '' ? $redirectUrl : route('projects.show', ['id' => $projectId]));
    }

    public function removeEmployee(Request $request): void
    {
      // Inicializar variáveis para evitar erro de variável não definida
        $projectId = 0;
        $redirectUrl = '';

        try {
            $data = $request->getParams();
            $projectId = (int) $data['project_id'];
            $employeeId = (int) $data['employee_id'];
            $redirectUrl = route('projects.show', ['id' => $projectId]);

            $project = Project::findById($projectId);
            $employee = Employee::findById($employeeId);

            if (!$project) {
                FlashMessage::danger(self::PROJECT_NOT_FOUND);
                $this->redirectTo(route('projects.index'));
                return;
            }

            if (!$employee) {
                FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
                $this->redirectTo($redirectUrl);
                return;
            }

          // Remove employee from project using the model
            $result = EmployeeProject::removeEmployeeFromProject($employeeId, $projectId);

            if ($result) {
                FlashMessage::success(self::ASSIGNMENT_REMOVED);
            } else {
                FlashMessage::danger('Erro ao remover funcionário do projeto');
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

      // Corrigido: Usar verificação de string vazia em vez de operador de coalescência nula
        $this->redirectTo($redirectUrl !== '' ? $redirectUrl : route('projects.show', ['id' => $projectId]));
    }

    public function employeeProjects(int $employeeId): void
    {
        $employee = Employee::findById($employeeId);

        if (!$employee) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('home.index'));
            return;
        }

        $projects = Project::getEmployeeProjects($employeeId);

      // Formatar os projetos no formato esperado pela view
        $projectsWithDetails = [];
        foreach ($projects as $project) {
          // Buscar a função do funcionário neste projeto
            $employeeProject = EmployeeProject::findEmployeeProject($employeeId, $project->id);
            $role = $employeeProject ? $employeeProject->role : 'Membro';

          // Contar membros da equipe
            $teamCount = count($project->employees()->get());

            $projectsWithDetails[] = [
            'project' => $project,
            'role' => $role,
            'team_count' => $teamCount
            ];
        }

        $this->render('employee_projects/index', [
        'title' => self::MY_PROJECTS,
        'employee' => $employee,
        'projectsWithDetails' => $projectsWithDetails
        ]);
    }

    public function userProjects(): void
    {
        $userId = Auth::user()->id;
        $this->employeeProjects($userId);
    }

  /**
   * Atualiza a função de um funcionário em um projeto específico
   *
   * @param Request $request Dados da requisição
   * @return void
   */
    public function updateEmployeeRole(Request $request): void
    {
      // Inicializar variáveis para evitar erro de variável não definida
        $projectId = 0;
        $redirectUrl = '';

        try {
            $data = $request->getParams();
            $projectId = (int) $data['project_id'];
            $employeeId = (int) $data['employee_id'];
            $newRole = $data['new_role'] ?? '';
            $redirectUrl = route('projects.show', ['id' => $projectId]);

            $project = Project::findById($projectId);
            $employee = Employee::findById($employeeId);

            if (!$project) {
                FlashMessage::danger(self::PROJECT_NOT_FOUND);
                $this->redirectTo(route('projects.index'));
                return;
            }

            if (!$employee) {
                FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
                $this->redirectTo($redirectUrl);
                return;
            }

          // Buscar a relação entre funcionário e projeto
            $employeeProject = EmployeeProject::findEmployeeProject($employeeId, $projectId);

            if (!$employeeProject) {
                FlashMessage::danger('Funcionário não está associado a este projeto');
                $this->redirectTo($redirectUrl);
                return;
            }

          // Atualizar a função do funcionário no projeto
            $employeeProject->role = $newRole;
            $result = $employeeProject->save();

            if ($result) {
                FlashMessage::success('Função do funcionário atualizada com sucesso!');
            } else {
                FlashMessage::danger('Erro ao atualizar função do funcionário');
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

      // Usar verificação de string vazia em vez de operador de coalescência nula
        $this->redirectTo($redirectUrl !== '' ? $redirectUrl : route('projects.show', ['id' => $projectId]));
    }

  /**
   * Verifica se o usuário atual tem acesso ao projeto
   *
   * @param int $projectId ID do projeto
   * @return bool True se o usuário tem acesso, false caso contrário
   */
    public function hasProjectAccess(int $projectId): bool
    {
        $userId = Auth::user()->id;
        $employeeProject = EmployeeProject::findEmployeeProject($userId, $projectId);
        return $employeeProject !== null;
    }

  /**
   * @param int $projectId
   * @return array<int, string>
   */
    public function getEmployeeProjectRoles(int $projectId): array
    {
        return EmployeeProject::getEmployeeProjectRoles($projectId);
    }
}
