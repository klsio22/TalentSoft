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
            $existingAssignments = $project->employees()->get();
            foreach ($existingAssignments as $existingEmployee) {
                if ($existingEmployee->id === $employeeId) {
                    FlashMessage::warning('Employee is already assigned to this project');
                    $this->redirectTo(route('projects.show', ['id' => $projectId]));
                    return;
                }
            }

          // Assign employee to project with role
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
}
