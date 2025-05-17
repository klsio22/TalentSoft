<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;

class UserController extends Controller
{
    protected string $layout = 'authenticated';

    public function __construct()
    {
        parent::__construct();

        // Verifica se o usuário está logado e é um usuário comum
        if (!Auth::check() || !Auth::isUser()) {
            $this->redirectTo(route('auth.login'));
        }
    }

    /**
     * Página inicial do usuário comum
     */
    public function home(): void
    {
        $title = 'Área do Usuário';
        $employee = Auth::user();

        $this->render('user/home', compact('title', 'employee'));
    }
}
