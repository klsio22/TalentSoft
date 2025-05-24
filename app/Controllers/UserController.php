<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Lib\Authentication\Auth;

class UserController extends Controller
{
    protected string $layout = 'application';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        } elseif (!Auth::isUser() && !Auth::isHR() && !Auth::isAdmin()) {
            \Lib\FlashMessage::danger('Acesso negado');
            $this->redirectTo(route('auth.login'));
        }
    }

    public function home(): void
    {
        $title = 'Área do Usuário';
        $employee = Auth::user();

        if (isset($_GET['profile']) && $_GET['profile'] === 'updated') {
            \Lib\FlashMessage::success('Seu perfil foi atualizado com sucesso!');
        }

        $this->render('user/home', compact('title', 'employee'));
    }
}
