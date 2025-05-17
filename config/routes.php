<?php

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\HRController;
use App\Controllers\UserController;
use Core\Router\Route;

Route::get('/', [HomeController::class, 'index'])->name('root');

Route::get('/login', [AuthController::class, 'loginForm'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::get('/admin', [AdminController::class, 'home'])->name('admin.home');

Route::get('/rh', [HRController::class, 'home'])->name('hr.home');

Route::get('/user', [UserController::class, 'home'])->name('user.home');
