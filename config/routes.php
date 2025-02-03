<?php

use App\Controllers\AdminController;
use App\Controllers\AuthenticationsController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use Core\Router\Route;

const LOGIN_ROUTE = '/login';
const LOGIN_ADMIN_ROUTE = '/admin/login';


// Rotas de Autenticação
Route::group(['prefix' => '/'], function () {
  Route::get('/', [AuthenticationsController::class, 'showLoginForm'])->name('root');
  Route::get(LOGIN_ROUTE, [AuthenticationsController::class, 'showLoginForm'])->name('users.login');
  Route::post(LOGIN_ROUTE, [UserController::class, 'login']);
  Route::post('/logout', [UserController::class, 'logout'])->name('users.logout');
});

// Rotas de Autenticação Admin
Route::group(['prefix' => '/admin'], function () {
  Route::get(LOGIN_ADMIN_ROUTE, [AdminController::class, 'showLoginForm'])->name('admin.login');
  Route::post(LOGIN_ADMIN_ROUTE, [AdminController::class, 'login']);
  Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
});

// Rotas protegidas para Usuários
Route::middleware('user')->group(function () {
  // Dashboard e listagem
  Route::get('/home', [HomeController::class, 'index'])->name('home');
  Route::get('/users', [UserController::class, 'listUsers'])->name('users.list');

  // Perfil do usuário
  Route::group(['prefix' => '/profile'], function () {
    Route::get('/edit', [UserController::class, 'editProfile'])->name('profile.edit');
    Route::post('/update', [UserController::class, 'updateProfile'])->name('profile.update');
  });
});

// Rotas protegidas para Administradores
Route::middleware('admin')->group(function () {
  // Dashboard Admin
  Route::get('/home/admin', [HomeController::class, 'admin'])->name('home.admin');

  // Gerenciamento de Admins
  Route::group(['prefix' => '/admin'], function () {
    Route::get('/register', [AdminController::class, 'showRegisterForm'])->name('register.admin');
    Route::post('/register', [AdminController::class, 'register'])->name('register.admin.create');
  });

  // Gerenciamento de Usuários
  Route::group(['prefix' => '/users'], function () {
    Route::get('/', [UserController::class, 'listUsers'])->name('users.list');
    Route::get('/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::post('/{id}/delete', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/{id}/update', [AdminController::class, 'updateUser'])->name('users.update');
  });

  // Perfil do Admin
  Route::group(['prefix' => '/profile'], function () {
    Route::get('/edit', [UserController::class, 'editProfile'])->name('profile.edit');
    Route::post('/update', [UserController::class, 'updateProfile'])->name('profile.update');
  });
});

// Rotas de Erro
Route::get('/404', [ErrorController::class, 'notFound'])->name('errors.404');
