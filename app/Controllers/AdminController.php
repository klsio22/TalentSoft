<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Lib\Authentication\Auth;

class AdminController extends Controller
{
    protected string $layout = 'application';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        } elseif (!Auth::isAdmin()) {
            \Lib\FlashMessage::danger('Acesso negado');
            $this->redirectTo(route('user.home'));
        }
    }

    public function home(): void
    {
        $title = 'Painel do Administrador';
        $employee = Auth::user();

        if (isset($_GET['welcome']) && $_GET['welcome'] === 'true') {
            \Lib\FlashMessage::info('Bem-vindo ao painel de administração!');
        }

        $this->render('admin/home', compact('title', 'employee'));
    }
}
