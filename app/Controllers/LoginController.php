<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;

class LoginController extends Controller
{
    public function index(Request $request): void
    {
        $title = 'Login';
        $this->render('login/index', compact('title'));
    }
}
