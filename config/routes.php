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



Route::middleware('user')->group(function () {
  Route::get('/home', [HomeController::class, 'index'])->name('home');
  Route::get('/users', [UserController::class, 'listUsers'])->name('users.list');

  Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('profile.edit');
  Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
});

Route::middleware('admin')->group(function () {
  Route::get('/home/admin', [HomeController::class, 'admin'])->name('home.admin');

  Route::get('/admin/register', [AdminController::class, 'showRegisterForm'])->name('register.admin');
  Route::post('/admin/register', [AdminController::class, 'register'])->name('register.admin.create');

  Route::get('/users', [UserController::class, 'listUsers'])->name('users.list');
  Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');

  Route::post('/users/{id}/delete', [AdminController::class, 'deleteUser'])->name('users.delete');
  Route::post('/users/{id}/update', [AdminController::class, 'updateUser'])->name('users.update');

  Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('profile.edit');
  Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
});

Route::get('/404', [ErrorController::class, 'notFound'])->name('errors.404');
