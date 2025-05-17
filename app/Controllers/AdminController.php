<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;

class AdminController extends Controller
{
    protected string $layout = 'authenticated';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check() || !Auth::isAdmin()) {
            $this->redirectTo(route('auth.login'));
        }
    }

    public function home(): void
    {
        $title = 'Painel do Administrador';
        $employee = Auth::user();

        $this->render('admin/home', compact('title', 'employee'));
    }
}
