<?php

use App\Controllers\AdminController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use Core\Router\Route;

/* Routes for authentication user */

Route::get('/', [UserController::class, 'showLoginForm'])->name('root');
Route::get('/', [UserController::class, 'showLoginForm'])->name('users.login');
Route::post('/', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->name('users.logout');


/* Routes for authentication admin */
Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

Route::middleware('admin')->group(function () {
  Route::get('/home/admin', [HomeController::class, 'admin'])->name('root');
  Route::get('/home/admin', [HomeController::class, 'admin'])->name('home.admin');
});

Route::middleware('auth')->group(function () {
  Route::get('/home', [HomeController::class, 'index'])->name('root');
  Route::get('/home', [HomeController::class, 'index'])->name('home');
});


Route::get('/404', [ErrorController::class, 'notFound'])->name('errors.404');
