<?php

use App\Controllers\HomeController;
use App\Controllers\LoginController;
use Core\Router\Route;
use App\Middleware\Authenticate;

Route::get('/', [LoginController::class, 'showLoginForm'])->name('root');
Route::get('/', [LoginController::class, 'showLoginForm'])->name('users.login');
Route::post('/', [LoginController::class, 'login'])->name('users.login.post');
Route::post('/', [LoginController::class, 'logout'])->name('users.logout');

Route::group(['middleware' => Authenticate::class], function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});
