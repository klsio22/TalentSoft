<?php

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\EmployeesController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\HRController;
use App\Controllers\UserController;
use Core\Router\Route;

// Rota principal
Route::get('/', [HomeController::class, 'index'])->name('root');

// Rotas de autenticação
Route::get('/login', [AuthController::class, 'loginForm'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

// Rotas de painéis principais
Route::get('/admin', [AdminController::class, 'home'])->name('admin.home');
Route::get('/hr', [HRController::class, 'home'])->name('hr.home');
Route::get('/user', [UserController::class, 'home'])->name('user.home');

// Rotas de funcionários (CRUD)
Route::middleware('admin-hr')->group(function () {
    // Listagem e criação
    Route::get('/employees', [EmployeesController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeesController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeesController::class, 'store'])->name('employees.store');

    // Mostrar funcionário
    Route::get('/employees/{id}', [EmployeesController::class, 'show'])->name('employees.show');

    // Editar funcionário
    Route::get('/employees/{id}/edit', [EmployeesController::class, 'edit'])->name('employees.edit');
    Route::post('/employees/update', [EmployeesController::class, 'update'])->name('employees.update');

    // Excluir funcionário
    Route::post('/employees/destroy', [EmployeesController::class, 'destroy'])->name('employees.destroy');

    // Rota alternativa (singular) - redireciona para o plural
    Route::get('/employee', [EmployeesController::class, 'index'])->name('employee.redirect');
});

// Rotas de erro
Route::get('/not-found', [ErrorController::class, 'notFound'])->name('error.not_found');
Route::get('/server-error', [ErrorController::class, 'serverError'])->name('error.server_error');

// Rota de fallback - captura todas as rotas não definidas
Route::get('/{any}', [ErrorController::class, 'notFound'])->name('error.fallback');

// Captura adicional para qualquer método e qualquer URI não identificada acima
// Esta route deve ser sempre a última a ser definida
Route::middleware('auth')->group(function () {
    // Este grupo está vazio intencionalmente - apenas para garantir que o middleware 'auth' existe
});
