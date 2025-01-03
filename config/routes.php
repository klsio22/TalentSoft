<?php

use App\Controllers\AdminController;
use App\Controllers\AuthenticationsController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use Core\Router\Route;

Route::get('/', [AuthenticationsController::class, 'showLoginForm'])->name('root');
Route::get('/login', [AuthenticationsController::class, 'showLoginForm'])->name('users.login');
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->name('users.logout');

Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::middleware('auth')->group(function () {
  Route::get('/home', [HomeController::class, 'index'])->name('home');
});

Route::middleware('admin')->group(function () {
  Route::get('/home/admin', [HomeController::class, 'admin'])->name('home.admin');
});


Route::get('/404', [ErrorController::class, 'notFound'])->name('errors.404');
