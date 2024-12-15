<?php

namespace App\Controllers;

use App\Models\User;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class AdminController extends Controller
{
    public function showLoginForm(Request $request)
    {

        if (Auth::check() && Auth::user()->role === 'admin') {
            $this->redirectTo(route('home.admin'));
            return;
        }

        $this->render('auth/admin/login');
    }

    public function login(Request $request): void
    {
        $credentials = $request->only(['email', 'password']);

        if (empty($credentials['email']) || empty($credentials['password'])) {
            FlashMessage::danger('Por favor, preencha todos os campos.');
            $this->redirectTo(route('admin.login'));
        }

        echo $credentials['email'];

        $user = User::attempt($credentials);

        if ($user && $user->role === 'admin') {
            Auth::login($user);
            FlashMessage::success('Login realizado com sucesso');
            $this->redirectTo(route('home.admin'));
        } else {
            FlashMessage::danger('Credenciais inválidas ou você não tem permissão para acessar essa página');
            $this->redirectTo(route('admin.login'));
        }
    }

    public function logout(): void
    {
        Auth::logout();
        FlashMessage::success('Logout realizado com sucesso');
        $this->redirectTo(route('admin.login'));
    }
}
