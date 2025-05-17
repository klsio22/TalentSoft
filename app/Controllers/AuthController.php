<?php

namespace App\Controllers;

use App\Models\Employee;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class AuthController extends Controller
{
    /**
     * Exibe o formulário de login
     */
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirectToHome();
            return;
        }

        $title = 'Login';
        $this->render('auth/login', compact('title'));
    }

    /**
     * Realiza o login do usuário
     */
    public function login(Request $request): void
    {
        $email = $request->getParam('email');
        $password = $request->getParam('password');

        if (empty($email) || empty($password)) {
            FlashMessage::danger('Email e senha são obrigatórios');
            $this->redirectTo(route('auth.login'));
            return;
        }

        $employee = Employee::findByEmail($email);

        if (!$employee || !$employee->authenticate($password)) {
            FlashMessage::danger('Email ou senha incorretos');
            $this->redirectTo(route('auth.login'));
            return;
        }

        Auth::login($employee);
        FlashMessage::success('Login realizado com sucesso');

        $this->redirectToHome();
    }

    /**
     * Realiza o logout do usuário
     */
    public function logout(): void
    {
        Auth::logout();
        FlashMessage::success('Logout realizado com sucesso');
        $this->redirectTo(route('auth.login'));
    }

    /**
     * Redireciona o usuário para sua página inicial com base no papel
     */
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
