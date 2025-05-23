<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Lib\Authentication\Auth;

class HRController extends Controller
{
    protected string $layout = 'application';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        } elseif (!Auth::isHR() && !Auth::isAdmin()) {
            \Lib\FlashMessage::danger('Acesso negado');
            $this->redirectTo(route('user.home'));
        }
    }

    public function home(): void
    {
        $title = 'Área de Recursos Humanos';
        $employee = Auth::user();

        if (isset($_GET['notification']) && $_GET['notification'] === 'true') {
            \Lib\FlashMessage::warning('Você tem novas solicitações pendentes de aprovação.');
        }

        $this->render('hr/home', compact('title', 'employee'));
    }
}
