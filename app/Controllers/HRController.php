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

        if (!Auth::check() || !Auth::isHR()) {
            $this->redirectTo(route('auth.login'));
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
