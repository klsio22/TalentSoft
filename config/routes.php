<?php

use App\Controllers\HomeController;
use App\Controllers\LoginController;
use Core\Router\Route;

// Authentication
Route::get('/', [HomeController::class, 'index'])->name('root');
Route::get('/login', [LoginController::class, 'index'])->name('login');
