<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;
use App\Models\User;

class UserController extends Controller
{
    private function validateCredentials(array $credentials): void
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            FlashMessage::danger('Por favor, preencha todos os campos.');
        }

        $user = User::attempt($credentials);

        if ($user) {
            Auth::login($user);
            FlashMessage::success('Login realizado com sucesso');
        } else {
            FlashMessage::danger('Credenciais inválidas');
        }
    }

    public function login(Request $request): void
    {
        $credentials = $request->only(['email', 'password']);

        $this->validateCredentials($credentials);
        if (Auth::check()) {
            $this->redirectTo(route('home'));
        } else {
            $this->redirectTo(route('users.login'));
        }
    }

    public function logout(): void
    {
        Auth::logout();
        FlashMessage::success('Logout realizado com sucesso');
        $this->redirectTo(route('users.login'));
    }
}
