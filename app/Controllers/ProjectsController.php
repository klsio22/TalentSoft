<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Project;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class ProjectsController extends Controller
{
    protected string $layout = 'application';

    private const PROJECT_NOT_FOUND = 'Project not found';
    private const ACCESS_DENIED = 'Access denied';
    private const PROJECT_CREATED = 'Project created successfully!';
    private const PROJECT_UPDATED = 'Project updated successfully!';
    private const PROJECT_DELETED = 'Project deactivated successfully!';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        } elseif (!Auth::isHR() && !Auth::isAdmin()) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('user.home'));
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

        $title = 'Projects List';

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
        $title = 'New Project';

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

    public function show(Request $request): void
    {


        $id = (int) $request->getParam('id');

        $project = Project::findById($id);

        if (!$project) {
            FlashMessage::danger(self::PROJECT_NOT_FOUND);
            $this->redirectTo(route('projects.index'));
            return;
        }

        try {
            $projectEmployees = $project->employees()->get();
            $employeeRoles = $this->getEmployeeProjectRoles($project->id);

            $projectTeam = [];
            foreach ($projectEmployees as $employee) {
                $projectTeam[] = [
                    'employee' => $employee,
                    'role' => $employeeRoles[$employee->id] ?? 'Not specified'
                ];
            }

            // Get all employees for the assignment form
            error_log('Getting all employees');
            $allEmployees = Employee::all();
            error_log('All employees count: ' . count($allEmployees));
        } catch (\Exception $e) {
            error_log('Exception in show method: ' . $e->getMessage());
            error_log('Exception trace: ' . $e->getTraceAsString());
            FlashMessage::danger('Error loading project data: ' . $e->getMessage());
            $this->redirectTo(route('projects.index'));
            return;
        }

        error_log('Filtering employees');
        // Filter out employees already assigned to the project
        $availableEmployees = array_filter($allEmployees, function ($employee) use ($projectEmployees) {
            foreach ($projectEmployees as $projectEmployee) {
                if ($projectEmployee->id === $employee->id) {
                    return false;
                }
            }
            return true;
        });
        error_log('Available employees count: ' . count($availableEmployees));

        $title = 'Project Details';
        error_log('Rendering view');

        try {
            $this->render('projects/show', compact('project', 'projectTeam', 'availableEmployees', 'title'));
            error_log('View rendered successfully');
        } catch (\Exception $e) {
            error_log('Exception in render: ' . $e->getMessage());
            error_log('Exception trace: ' . $e->getTraceAsString());
            FlashMessage::danger('Error rendering project view: ' . $e->getMessage());
            $this->redirectTo(route('projects.index'));
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

        $title = 'Edit Project';
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

    private function getEmployeeProjectRoles(int $projectId): array
    {
        $roles = [];
        $pdo = \Core\Database\Database::getDatabaseConn();

        $sql = "SELECT employee_id, role FROM Employee_Projects WHERE project_id = :project_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':project_id', $projectId);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            $roles[$row['employee_id']] = $row['role'];
        }

        return $roles;
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

            // Instead of deleting, set status to 'Inactive'
            $project->status = 'Inactive';

            if ($project->save()) {
                FlashMessage::success(self::PROJECT_DELETED);
            } else {
                FlashMessage::danger('Failed to deactivate project');
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

        $this->redirectTo(route('projects.index'));
    }
}
