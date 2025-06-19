<?php

namespace App\Controllers;

use App\Models\Employee;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirectToHome();
            return;
        }

        $title = 'Login';
        $this->setLayout('public');
        $this->render('auth/login', compact('title'));
    }

    public function login(Request $request): void
    {
        $email = $request->getParam('email');
        $password = $request->getParam('password');
        $token = $request->getParam('_token');

        if (!csrf_check($token)) {
            FlashMessage::danger('Erro de validação do formulário');
            $this->redirectTo(route('auth.login'));
            return;
        }

        if (empty($email) || empty($password)) {
            FlashMessage::danger('Email e senha são obrigatórios');
            $this->redirectTo(route('auth.login'));
            return;
        }

        $employee = Employee::findByEmail($email);

        if (!$employee || !$employee->authenticate($password)) {
            FlashMessage::danger('Email ou senha incorretos ou não possui acesso');
            $this->redirectTo(route('auth.login'));
            return;
        }

        Auth::login($employee);
        FlashMessage::success('Login realizado com sucesso');

        $this->redirectToHome();
    }


    public function logout(): void
    {
        Auth::logout();
        FlashMessage::success('Logout realizado com sucesso');
        $this->redirectTo(route('auth.login'));
    }

    private function redirectToHome(): void
    {
        if (Auth::isAdmin()) {
            $this->redirectTo(route('admin.home'));
        } elseif (Auth::isHR()) {
            $this->redirectTo(route('hr.home'));
        } else {
            $this->redirectTo(route('user.home'));
        }
    }
}
