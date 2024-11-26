<?php

use App\Controllers\HomeController;
use App\Controllers\LoginController;
use Core\Router\Route;

// Authentication
Route::get('/', [HomeController::class, 'index'])->name('root');
Route::get('/auth/login', [LoginController::class, 'showLoginForm'])->name('users.login');
Route::post('/auth/login', [LoginController::class, 'login']);
Route::post('/auth/logout', [LoginController::class, 'logout'])->name('users.logout');
