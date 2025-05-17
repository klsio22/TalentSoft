<?php

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\HRController;
use App\Controllers\UserController;
use Core\Router\Route;

// Página inicial (pública)
Route::get('/', [HomeController::class, 'index'])->name('root');

// Autenticação
Route::get('/login', [AuthController::class, 'loginForm'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

// Rotas de Admin (protegidas)
Route::get('/admin', [AdminController::class, 'home'])->name('admin.home');

// Rotas de HR (protegidas)
Route::get('/rh', [HRController::class, 'home'])->name('hr.home');

// Rotas de Usuário (protegidas)
Route::get('/user', [UserController::class, 'home'])->name('user.home');
