<?php

namespace App\Controllers;

use App\Models\Approval;
use App\Models\Employee;
use App\Models\Project;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class ApprovalsController extends Controller
{
    protected string $layout = 'application';

    private const EMPLOYEE_NOT_FOUND = 'Employee not found';
    private const PROJECT_NOT_FOUND = 'Project not found';
    private const APPROVAL_NOT_FOUND = 'Approval not found';
    private const ACCESS_DENIED = 'Access denied';
    private const APPROVAL_CREATED = 'Approval request created successfully!';
    private const APPROVAL_APPROVED = 'Request approved successfully!';
    private const APPROVAL_REJECTED = 'Request rejected!';
    private const APPROVAL_DELETED = 'Approval request deleted!';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        }
    }

    public function index(): void
    {
        // Regular users can only see their own approvals
        // Admin and HR can see all approvals
        $approvals = [];

        if (Auth::isAdmin() || Auth::isHR()) {
            $approvals = Approval::all();
            $title = 'All Approval Requests';
        } else {
            $currentEmployee = Auth::user();
            $approvals = $currentEmployee->approvals()->get();
            $title = 'My Approval Requests';
        }

        $this->render('approvals/index', compact('approvals', 'title'));
    }

    public function create(): void
    {
        $approval = new Approval();
        $employees = Employee::all();
        $projects = Project::all();
        $title = 'New Approval Request';

        $this->render('approvals/create', compact('approval', 'employees', 'projects', 'title'));
    }

    public function store(Request $request): void
    {
        try {
            $data = $request->getParams();

            // If regular user, set employee_id to current user
            if (!Auth::isAdmin() && !Auth::isHR()) {
                $currentEmployee = Auth::user();
                $data['employee_id'] = $currentEmployee->id;
            }

            $approval = new Approval($data);

            if ($approval->save()) {
                FlashMessage::success(self::APPROVAL_CREATED);
                $this->redirectTo(route('approvals.index'));
            } else {
                $employees = Employee::all();
                $projects = Project::all();
                $this->renderWithErrors('approvals/create', compact('approval', 'employees', 'projects'), $approval->getErrors());
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
            $this->redirectTo(route('approvals.create'));
        }
    }

    public function show(int $id): void
    {
        $approval = Approval::findById($id);

        if (!$approval) {
            FlashMessage::danger(self::APPROVAL_NOT_FOUND);
            $this->redirectTo(route('approvals.index'));
            return;
        }

        // Check if user has permission to view this approval
        $currentEmployee = Auth::user();
        if (!Auth::isAdmin() && !Auth::isHR() && $approval->employee_id !== $currentEmployee->id) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('approvals.index'));
            return;
        }

        $title = 'Approval Request Details';
        $this->render('approvals/show', compact('approval', 'title'));
    }

    public function approve(Request $request): void
    {
        // Only admin and HR can approve requests
        if (!Auth::isAdmin() && !Auth::isHR()) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('approvals.index'));
            return;
        }

        try {
            $data = $request->getParams();
            $id = (int) $data['id'];
            $approval = Approval::findById($id);

            if (!$approval) {
                FlashMessage::danger(self::APPROVAL_NOT_FOUND);
                $this->redirectTo(route('approvals.index'));
                return;
            }

            if ($approval->approve()) {
                FlashMessage::success(self::APPROVAL_APPROVED);
            } else {
                FlashMessage::danger('Failed to approve request');
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

        $this->redirectTo(route('approvals.index'));
    }

    public function reject(Request $request): void
    {
        // Only admin and HR can reject requests
        if (!Auth::isAdmin() && !Auth::isHR()) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('approvals.index'));
            return;
        }

        try {
            $data = $request->getParams();
            $id = (int) $data['id'];
            $approval = Approval::findById($id);

            if (!$approval) {
                FlashMessage::danger(self::APPROVAL_NOT_FOUND);
                $this->redirectTo(route('approvals.index'));
                return;
            }

            if ($approval->reject()) {
                FlashMessage::success(self::APPROVAL_REJECTED);
            } else {
                FlashMessage::danger('Failed to reject request');
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

        $this->redirectTo(route('approvals.index'));
    }

    public function destroy(Request $request): void
    {
        try {
            $data = $request->getParams();
            $id = (int) $data['id'];
            $approval = Approval::findById($id);

            if (!$approval) {
                FlashMessage::danger(self::APPROVAL_NOT_FOUND);
                $this->redirectTo(route('approvals.index'));
                return;
            }

            // Check if user has permission to delete this approval
            $currentEmployee = Auth::user();
            if (!Auth::isAdmin() && !Auth::isHR() && $approval->employee_id !== $currentEmployee->id) {
                FlashMessage::danger(self::ACCESS_DENIED);
                $this->redirectTo(route('approvals.index'));
                return;
            }

            // Only pending approvals can be deleted
            if ($approval->status !== 'Pending') {
                FlashMessage::danger('Only pending requests can be deleted');
                $this->redirectTo(route('approvals.show', ['id' => $approval->id]));
                return;
            }

            if ($approval->delete()) {
                FlashMessage::success(self::APPROVAL_DELETED);
                $this->redirectTo(route('approvals.index'));
            } else {
                FlashMessage::danger('Failed to delete approval request');
                $this->redirectTo(route('approvals.show', ['id' => $approval->id]));
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
            $this->redirectTo(route('approvals.index'));
        }
    }

    public function employeeApprovals(int $employeeId): void
    {
        // Only admin and HR can view other employees' approvals
        if (!Auth::isAdmin() && !Auth::isHR()) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('user.home'));
            return;
        }

        $employee = Employee::findById($employeeId);

        if (!$employee) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('employees.index'));
            return;
        }

        // Get approvals for this employee
        $approvals = $employee->approvals()->get();

        $title = 'Approval Requests for ' . $employee->name;
        $this->render('approvals/employee', compact('employee', 'approvals', 'title'));
    }

    public function projectApprovals(int $projectId): void
    {
        // Only admin and HR can view project approvals
        if (!Auth::isAdmin() && !Auth::isHR()) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('user.home'));
            return;
        }

        $project = Project::findById($projectId);

        if (!$project) {
            FlashMessage::danger(self::PROJECT_NOT_FOUND);
            $this->redirectTo(route('projects.index'));
            return;
        }

        // Get approvals for this project
        $approvals = $project->approvals()->get();

        $title = 'Approval Requests for Project: ' . $project->name;
        $this->render('approvals/project', compact('project', 'approvals', 'title'));
    }
}
