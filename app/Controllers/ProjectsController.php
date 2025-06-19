<?php

namespace App\Controllers;

use App\Controllers\EmployeeProjectsController;
use App\Models\Employee;
use App\Models\Project;
use App\Models\UserCredential;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class ProjectsController extends Controller
{
    protected string $layout = 'application';

    private const PROJECT_NOT_FOUND = 'Projeto não encontrado';
    private const ACCESS_DENIED = 'Acesso negado';
    private const PROJECT_CREATED = 'Projeto criado com sucesso!';
    private const PROJECT_UPDATED = 'Projeto atualizado com sucesso!';
    private const PROJECT_DELETED = 'Projeto desativado com sucesso!';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        }
    }

    public function index(): void
    {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS);
        $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
        $perPage = 10;

        // Get all projects
        $allProjects = Project::all();

        // Filter projects
        $filteredProjects = $this->filterProjects($allProjects, $search, $status);

        // Create pagination
        $projects = $this->createPaginator($filteredProjects, $page, $perPage);

        $title = 'Lista de projetos';

        // Prepare query parameters for pagination URLs
        $queryParams = $this->prepareQueryParams($search, $status);

        $this->render('projects/index', compact('projects', 'title', 'queryParams'));
    }

    /**
     * Filter projects based on search and status
     * @param array<Project> $projects
     * @param string|null $search
     * @param string|null $status
     * @return array<Project>
     */
    private function filterProjects(array $projects, ?string $search, ?string $status): array
    {
        return array_filter($projects, function ($project) use ($search, $status) {
            $matchesSearch = !$search || stripos($project->name, $search) !== false ||
                            stripos($project->description ?? '', $search) !== false;

            $matchesStatus = !$status || $project->status === $status;

            return $matchesSearch && $matchesStatus;
        });
    }

    /**
     * Create a paginator for projects
     * @param array<Project> $projects
     * @param int $page
     * @param int $perPage
     * @return array<Project>
     */
    private function createPaginator(array $projects, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        return array_slice($projects, $offset, $perPage);
    }

    /**
     * Prepare query parameters for pagination URLs
     * @return array<string, string>
     */
    private function prepareQueryParams(?string $search, ?string $status): array
    {
        $queryParams = [];
        if ($search) {
            $queryParams['search'] = $search;
        }
        if ($status) {
            $queryParams['status'] = $status;
        }
        return $queryParams;
    }

    public function create(): void
    {
        $project = new Project();
        $title = 'Novo Projeto';

        $this->render('projects/create', compact('project', 'title'));
    }

    public function store(Request $request): void
    {
        try {
            $data = $request->getParams();
            $project = new Project($data);

            if ($project->save()) {
                FlashMessage::success(self::PROJECT_CREATED);
                $this->redirectTo(route('projects.show', ['id' => $project->id]));
            } else {
                $this->renderWithErrors('projects/create', compact('project'), $project->errors());
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
            $this->redirectTo(route('projects.create'));
        }
    }

    /**
     * Verifica se um usuário tem acesso a um projeto específico
     * Utiliza o método centralizado no EmployeeProjectsController
     *
     * @param int $projectId ID do projeto
     * @return bool True se o usuário tem acesso, false caso contrário
     */
    private function userHasProjectAccess(int $projectId): bool
    {
        // Utiliza o controlador de funcionários-projetos para verificação de acesso
        $employeeProjectsController = new EmployeeProjectsController();
        return $employeeProjectsController->hasProjectAccess($projectId);
    }

    /**
     * Prepara a equipe do projeto para exibição
     *
     * @param Project $project Projeto
     * @return array<int, array<string, mixed>> Equipe do projeto com detalhes
     */
    private function prepareProjectTeam(Project $project): array
    {
        $projectEmployees = $project->employees()->get();
        $employeeRoles = $this->getEmployeeProjectRoles($project->id);

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
     * Filtra funcionários disponíveis para atribuição ao projeto
     *
     * @param array<int, object> $allEmployees Todos os funcionários
     * @param array<int, object> $projectEmployees Funcionários já atribuídos ao projeto
     * @return array<int, object> Funcionários disponíveis
     */
    private function filterAvailableEmployees(array $allEmployees, array $projectEmployees): array
    {
        return array_filter($allEmployees, function ($employee) use ($projectEmployees) {
            foreach ($projectEmployees as $projectEmployee) {
                if ($projectEmployee->id === $employee->id) {
                    return false;
                }
            }
            return true;
        });
    }

    public function show(Request $request): void
    {
        $id = (int) $request->getParam('id');
        $project = Project::findById($id);

        if (!$project) {
            FlashMessage::danger(self::PROJECT_NOT_FOUND);
            $this->redirectTo(route('projects.index'));
            return;
        }

        // Para admin e HR, sem verificação adicional
        if (Auth::isAdmin() || Auth::isHR()) {
            try {
                // Preparar dados do projeto
                $projectTeam = $this->prepareProjectTeam($project);
                $projectEmployees = $project->employees()->get();
                $allEmployees = Employee::all();
                $availableEmployees = $this->filterAvailableEmployees($allEmployees, $projectEmployees);
                $title = 'Project Details';

                // Admin e HR veem a view original com todos os detalhes
                $this->render('projects/show', compact('project', 'projectTeam', 'availableEmployees', 'title'));
            } catch (\Exception $e) {
                FlashMessage::danger('Erro ao carregar dados do projeto: ' . $e->getMessage());
                $this->redirectTo(route('projects.index'));
            }
            return;
        }

        // Para usuários comuns, verificação de acesso
        if (!$this->userHasProjectAccess($project->id)) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('projects.user'));
            return;
        }

        try {
            // Preparar dados do projeto para usuário comum
            $projectTeam = $this->prepareProjectTeam($project);
            $title = 'Project Details';

            // Usuários comuns veem a view simplificada sem orçamento
            $this->render('projects/show_user', compact('project', 'projectTeam', 'title'));
        } catch (\Exception $e) {
            FlashMessage::danger('Erro ao carregar dados do projeto: ' . $e->getMessage());
            $this->redirectTo(route('projects.user'));
        }
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->getParam('id');
        $project = Project::findById($id);

        if (!$project) {
            FlashMessage::danger(self::PROJECT_NOT_FOUND);
            $this->redirectTo(route('projects.index'));
            return;
        }

        $title = 'Editar Projeto';
        $this->render('projects/edit', compact('project', 'title'));
    }

    public function update(Request $request): void
    {
        try {
            $data = $request->getParams();
            $id = (int) $request->getParam('id');
            $project = Project::findById($id);

            if (!$project) {
                FlashMessage::danger(self::PROJECT_NOT_FOUND);
                $this->redirectTo(route('projects.index'));
                return;
            }

            // Update project attributes from data array
            foreach ($data as $key => $value) {
                if (in_array($key, Project::columns())) {
                    $project->$key = $value;
                }
            }

            if ($project->save()) {
                FlashMessage::success(self::PROJECT_UPDATED);
                $this->redirectTo(route('projects.show', ['id' => $project->id]));
            } else {
                $this->renderWithErrors('projects/edit', compact('project'), $project->errors());
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
            $this->redirectTo(route('projects.index'));
        }
    }

    /**
     * Obtem os papéis dos funcionários em um projeto
     * Delega para o método centralizado no EmployeeProjectsController
     *
     * @param int $projectId ID do projeto
     * @return array<int, string> Array associativo com [employee_id => role]
     */
    private function getEmployeeProjectRoles(int $projectId): array
    {
        $employeeProjectsController = new EmployeeProjectsController();
        return $employeeProjectsController->getEmployeeProjectRoles($projectId);
    }

    public function destroy(Request $request): void
    {
        try {
            $id = (int) $request->getParam('id');
            $project = Project::findById($id);

            if (!$project) {
                FlashMessage::danger(self::PROJECT_NOT_FOUND);
                $this->redirectTo(route('projects.index'));
                return;
            }

            // Instead of deleting, set status to 'Em pausa'
            $project->status = 'Em pausa';

            if ($project->save()) {
                FlashMessage::success(self::PROJECT_DELETED);
            } else {
                FlashMessage::danger('Falha ao desativar o projeto');
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

        $this->redirectTo(route('projects.index'));
    }

    /**
     * Lista os projetos associados ao usuário atual
     * Redireciona para o método userProjects do EmployeeProjectsController
     * Este método existe apenas para manter compatibilidade com rotas existentes
     */
    public function userProjects(): void
    {
        // Delega para o EmployeeProjectsController
        $employeeProjectsController = new EmployeeProjectsController();
        $employeeProjectsController->userProjects();
    }
}
