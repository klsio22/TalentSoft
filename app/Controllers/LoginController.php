<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm(): void
    {
        $this->render('auth/login');
    }

    public function login(Request $request): void
    {
        $credentials = $request->only(['email', 'password']);
        $user = User::attempt($credentials);

        if ($user) {
            Auth::login($user);
            FlashMessage::success('Login realizado com sucesso');
            $this->redirectTo(route('root'));
        } else {
            FlashMessage::danger('Credenciais inválidas');
            $this->redirectTo(route('users.login'));
        }
    }

    public function logout(): void
    {
        Auth::logout();
        FlashMessage::success('Logout realizado com sucesso');
        $this->redirectTo(route('root'));
    }

    protected function redirectTo(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }
}
