<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\Notification;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class NotificationsController extends Controller
{
    protected string $layout = 'application';

    private const EMPLOYEE_NOT_FOUND = 'Employee not found';
    private const NOTIFICATION_NOT_FOUND = 'Notification not found';
    private const ACCESS_DENIED = 'Access denied';
    private const NOTIFICATION_CREATED = 'Notification created successfully!';
    private const NOTIFICATION_DELETED = 'Notification deleted successfully!';
    private const NOTIFICATION_READ = 'Notification marked as read';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        }
    }

    public function index(): void
    {
        // Get current logged in employee
        $currentEmployee = Auth::user();

        if (!$currentEmployee) {
            FlashMessage::danger(self::EMPLOYEE_NOT_FOUND);
            $this->redirectTo(route('auth.login'));
            return;
        }

        // Get notifications for the current employee
        $notifications = $currentEmployee->notifications()->get();

        $title = 'My Notifications';
        $this->render('notifications/index', compact('notifications', 'title'));
    }

    public function create(): void
    {
        // Only admin and HR can create notifications for other employees
        if (!Auth::isAdmin() && !Auth::isHR()) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('user.home'));
            return;
        }

        $notification = new Notification();
        $employees = Employee::all();
        $title = 'New Notification';

        $this->render('notifications/create', compact('notification', 'employees', 'title'));
    }

    public function store(Request $request): void
    {
        // Only admin and HR can create notifications for other employees
        if (!Auth::isAdmin() && !Auth::isHR()) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('user.home'));
            return;
        }

        try {
            $data = $request->getParams();
            $notification = new Notification($data);

            if ($notification->save()) {
                FlashMessage::success(self::NOTIFICATION_CREATED);
                $this->redirectTo(route('notifications.admin'));
            } else {
                $employees = Employee::all();
                $this->renderWithErrors('notifications/create', compact('notification', 'employees'), $notification->errors());
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
            $this->redirectTo(route('notifications.create'));
        }
    }

    public function markAsRead(Request $request): void
    {
        try {
            $data = $request->getParams();
            $id = (int) $data['id'];
            $notification = Notification::findById($id);

            if (!$notification) {
                FlashMessage::danger(self::NOTIFICATION_NOT_FOUND);
                $this->redirectTo(route('notifications.index'));
                return;
            }

            // Check if the notification belongs to the current user
            $currentEmployee = Auth::user();
            if ($notification->employee_id !== $currentEmployee->id) {
                FlashMessage::danger(self::ACCESS_DENIED);
                $this->redirectTo(route('notifications.index'));
                return;
            }

            if ($notification->markAsRead()) {
                FlashMessage::success(self::NOTIFICATION_READ);
            } else {
                FlashMessage::danger('Failed to mark notification as read');
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

        $this->redirectTo(route('notifications.index'));
    }

    public function destroy(Request $request): void
    {
        try {
            $data = $request->getParams();
            $id = (int) $data['id'];
            $notification = Notification::findById($id);

            if (!$notification) {
                FlashMessage::danger(self::NOTIFICATION_NOT_FOUND);
                $this->redirectTo(route('notifications.index'));
                return;
            }

            // Check if user has permission to delete this notification
            $currentEmployee = Auth::user();
            if (!Auth::isAdmin() && !Auth::isHR() && $notification->employee_id !== $currentEmployee->id) {
                FlashMessage::danger(self::ACCESS_DENIED);
                $this->redirectTo(route('notifications.index'));
                return;
            }

            if ($notification->destroy()) {
                FlashMessage::success(self::NOTIFICATION_DELETED);
            } else {
                FlashMessage::danger('Failed to delete notification');
            }
        } catch (\Exception $e) {
            FlashMessage::danger($e->getMessage());
        }

        // Redirect to appropriate page based on user role
        if (Auth::isAdmin() || Auth::isHR()) {
            $this->redirectTo(route('notifications.admin'));
        } else {
            $this->redirectTo(route('notifications.index'));
        }
    }

    public function adminIndex(): void
    {
        // Only admin and HR can view all notifications
        if (!Auth::isAdmin() && !Auth::isHR()) {
            FlashMessage::danger(self::ACCESS_DENIED);
            $this->redirectTo(route('user.home'));
            return;
        }

        $notifications = Notification::all();
        $employees = Employee::all();
        $title = 'All Notifications';

        $this->render('notifications/admin', compact('notifications', 'employees', 'title'));
    }

    public function employeeNotifications(int $employeeId): void
    {
        // Only admin and HR can view other employees' notifications
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

        // Get notifications for this employee
        $notifications = $employee->notifications()->get();

        $title = 'Notifications for ' . $employee->name;
        $this->render('notifications/employee', compact('employee', 'notifications', 'title'));
    }
}
