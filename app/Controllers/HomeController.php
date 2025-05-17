<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Lib\Authentication\Auth;

class HomeController extends Controller
{
    public function index(): void
    {
        // Se o usuário já estiver logado, redireciona para a página inicial adequada
        if (Auth::check()) {
            if (Auth::isAdmin()) {
                $this->redirectTo(route('admin.home'));
                return;
            } elseif (Auth::isHR()) {
                $this->redirectTo(route('hr.home'));
                return;
            } elseif (Auth::isUser()) {
                $this->redirectTo(route('user.home'));
                return;
            }
        }

        // Se não estiver logado, exibe a página inicial pública
        $title = 'TalentSoft - Sistema de Gestão de Talentos';
        $this->render('home/index', compact('title'));
    }
}
