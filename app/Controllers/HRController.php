<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;

class HRController extends Controller
{
    protected string $layout = 'authenticated';

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

        $this->render('hr/home', compact('title', 'employee'));
    }
}
