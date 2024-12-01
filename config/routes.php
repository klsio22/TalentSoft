<?php

use App\Controllers\AdminController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\LoginController;
use Core\Router\Route;

Route::get('/', [LoginController::class, 'showLoginForm'])->name('root');
Route::get('/', [LoginController::class, 'showLoginForm'])->name('users.login');
Route::post('/', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('users.logout');

Route::middleware('auth')->group(function() {
  Route::get('/', [HomeController::class, 'index'])->name('root');
  Route::get('/home', [HomeController::class, 'index'])->name('home');
});

Route::middleware('admin')->group(function() {
  Route::get('/admin-home', [AdminController::class, 'index'])->name('home.admin');
});


Route::get('/404', [ErrorController::class, 'notFound'])->name('errors.404');
