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

    private const EMPLOYEE_NOT_FOUND = 'Employee not found';
    private const PROJECT_NOT_FOUND = 'Project not found';
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
                FlashMessage::warning('Employee is already assigned to this project');
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

        $this->redirectTo($redirectUrl ?? route('projects.show', ['id' => $projectId]));
    }

    public function removeEmployee(Request $request): void
    {
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

        $this->redirectTo($redirectUrl ?? route('projects.show', ['id' => $projectId]));
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

      // Obter projetos com detalhes usando o modelo
        $projectsWithDetails = EmployeeProject::getEmployeeProjectsWithDetails($employee);

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
        return EmployeeProject::currentUserHasProjectAccess($projectId);
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
        return EmployeeProject::getEmployeeProjectRoles($projectId);
    }

  /**
   * Atualiza o papel de um funcionário em um projeto específico
   * Apenas administradores e RH podem realizar esta ação
   *
   * @param Request $request Requisição HTTP
   * @return void
   */
    public function updateEmployeeRole(Request $request): void
    {
      // Initialize variables
        $projectId = null;
        $redirectUrl = route('projects.index');

        try {
          // Verificar se o usuário tem permissão para atualizar papéis
            if (!EmployeeProject::currentUserCanManageRoles()) {
                FlashMessage::danger('Você não tem permissão para atualizar papéis de funcionários');
            } else {
                $data = $request->getParams();
                $projectId = (int) $data['project_id'];
                $employeeId = (int) $data['employee_id'];
                $newRole = $data['new_role'] ?? '';
                $redirectUrl = route('projects.show', ['id' => $projectId]);

              // Validar projeto e funcionário
                $project = Project::findById($projectId);
                $employee = Employee::findById($employeeId);

                if (!$project) {
                    FlashMessage::danger(self::PROJECT_NOT_FOUND);
                    $redirectUrl = route('projects.index');
                } elseif (!$employee) {
                    FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
                } else {
                  // Atualizar o papel do funcionário no projeto
                    $result = EmployeeProject::updateEmployeeRole($employeeId, $projectId, $newRole);

                    if ($result) {
                        FlashMessage::success('Papel do funcionário atualizado com sucesso!');
                    } else {
                        FlashMessage::danger('Erro ao atualizar papel do funcionário');
                    }
                }
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
          // Em caso de erro, garantir que o usuário seja redirecionado para a página do projeto se o ID estiver disponível
            if ($projectId) {
                $redirectUrl = route('projects.show', ['id' => $projectId]);
            }
        }

      // Redirecionar para a página apropriada
        $this->redirectTo($redirectUrl);
    }
}
