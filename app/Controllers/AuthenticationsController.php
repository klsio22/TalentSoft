<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Lib\Authentication\Auth;

class AuthenticationsController extends Controller
{
    public function showLoginForm(): void
    {
        if (Auth::check() && Auth::user()->role === 'user') {
            $this->redirectTo(route('home'));
        }

        if (Auth::check() && Auth::user()->role === 'admin') {
            $this->redirectTo(route('home.admin'));
        }

        $this->render('auth/login');
    }
}
