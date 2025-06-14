<?php

use App\Controllers\AdminController;
use App\Controllers\ApprovalsController;
use App\Controllers\AuthController;
use App\Controllers\EmployeesController;
use App\Controllers\EmployeeProjectsController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\HRController;
use App\Controllers\NotificationsController;
use App\Controllers\ProfileController;
use App\Controllers\ProjectsController;
use App\Controllers\UserController;
use Core\Router\Route;

Route::get('/', [HomeController::class, 'index'])->name('root');

Route::get('/login', [AuthController::class, 'loginForm'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::get('/admin', [AdminController::class, 'home'])->name('admin.home');
Route::get('/hr', [HRController::class, 'home'])->name('hr.home');
Route::get('/user', [UserController::class, 'home'])->name('user.home');

// Profile route - available for all authenticated users
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

// Middleware for admin and HR routes
Route::middleware('admin-hr')->group(function () {
    // Employee routes
    Route::get('/employees', [EmployeesController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeesController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeesController::class, 'store'])->name('employees.store');
    Route::get('/employees/{id}', [EmployeesController::class, 'show'])->name('employees.show');
    Route::get('/employees/{id}/edit', [EmployeesController::class, 'edit'])->name('employees.edit');
    Route::post('/employees/update', [EmployeesController::class, 'update'])->name('employees.update');
    Route::post('/employees/destroy', [EmployeesController::class, 'destroy'])->name('employees.destroy');
    Route::get('/employee', [EmployeesController::class, 'index'])->name('employee.redirect');

    // Project routes
    Route::get('/projects', [ProjectsController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectsController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectsController::class, 'store'])->name('projects.store');
    Route::get('/projects/{id}', [ProjectsController::class, 'show'])->name('projects.show');
    Route::get('/projects/{id}/edit', [ProjectsController::class, 'edit'])->name('projects.edit');
    Route::post('/projects/{id}/update', [ProjectsController::class, 'update'])->name('projects.update');
    Route::post('/projects/{id}/destroy', [ProjectsController::class, 'destroy'])->name('projects.destroy');

    // Employee-Project relationship routes
    Route::post('/employee-projects/assign', [EmployeeProjectsController::class, 'assignEmployee'])->name('employee-projects.assign');
    Route::post('/employee-projects/remove', [EmployeeProjectsController::class, 'removeEmployee'])->name('employee-projects.remove');
    Route::get('/employees/{id}/projects', [EmployeeProjectsController::class, 'employeeProjects'])->name('employee-projects.employee');

    // Notification admin routes
    Route::get('/notifications/admin', [NotificationsController::class, 'adminIndex'])->name('notifications.admin');
    Route::get('/notifications/create', [NotificationsController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [NotificationsController::class, 'store'])->name('notifications.store');
    Route::get('/employees/{id}/notifications', [NotificationsController::class, 'employeeNotifications'])->name('notifications.employee');

    // Approval admin routes
    Route::get('/approvals/admin', [ApprovalsController::class, 'index'])->name('approvals.admin');
    Route::get('/employees/{id}/approvals', [ApprovalsController::class, 'employeeApprovals'])->name('approvals.employee');
    Route::get('/projects/{id}/approvals', [ApprovalsController::class, 'projectApprovals'])->name('approvals.project');
    Route::post('/approvals/approve', [ApprovalsController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/reject', [ApprovalsController::class, 'reject'])->name('approvals.reject');
});

// Routes for authenticated users (any role)
Route::middleware('auth')->group(function () {
    // Notification routes
    Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [NotificationsController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/destroy', [NotificationsController::class, 'destroy'])->name('notifications.destroy');

    // Approval routes
    Route::get('/approvals', [ApprovalsController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/create', [ApprovalsController::class, 'create'])->name('approvals.create');
    Route::post('/approvals', [ApprovalsController::class, 'store'])->name('approvals.store');
    Route::get('/approvals/{id}', [ApprovalsController::class, 'show'])->name('approvals.show');
    Route::post('/approvals/destroy', [ApprovalsController::class, 'destroy'])->name('approvals.destroy');
});

Route::get('/not-found', [ErrorController::class, 'notFound'])->name('error.not_found');
Route::get('/server-error', [ErrorController::class, 'serverError'])->name('error.server_error');

Route::get('/{any}', [ErrorController::class, 'notFound'])->name('error.fallback');
