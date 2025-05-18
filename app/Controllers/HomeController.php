<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Lib\Authentication\Auth;

class HomeController extends Controller
{
    public function index(): void
    {
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

        $title = 'TalentSoft - Sistema de Gestão de Talentos';
        $this->render('home/index', compact('title'));
    }
}
