<?php

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\EmployeesController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\HRController;
use App\Controllers\ProfileController;
use App\Controllers\UserController;
use Core\Router\Route;

Route::get('/', [HomeController::class, 'index'])->name('root');

Route::get('/login', [AuthController::class, 'loginForm'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::get('/admin', [AdminController::class, 'home'])->name('admin.home');
Route::get('/hr', [HRController::class, 'home'])->name('hr.home');
Route::get('/user', [UserController::class, 'home'])->name('user.home');

// Rota do perfil - disponível para todos os usuários autenticados
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');


Route::middleware('admin-hr')->group(function () {
    Route::get('/employees', [EmployeesController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeesController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeesController::class, 'store'])->name('employees.store');

    Route::get('/employees/{id}', [EmployeesController::class, 'show'])->name('employees.show');

    Route::get('/employees/{id}/edit', [EmployeesController::class, 'edit'])->name('employees.edit');
    Route::post('/employees/update', [EmployeesController::class, 'update'])->name('employees.update');

    Route::post('/employees/destroy', [EmployeesController::class, 'destroy'])->name('employees.destroy');

    Route::get('/employee', [EmployeesController::class, 'index'])->name('employee.redirect');
});


Route::get('/not-found', [ErrorController::class, 'notFound'])->name('error.not_found');
Route::get('/server-error', [ErrorController::class, 'serverError'])->name('error.server_error');

Route::get('/{any}', [ErrorController::class, 'notFound'])->name('error.fallback');
