<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;
use App\Models\User;

class UserController extends Controller
{
  public function showLoginForm(): bool
  {
    if (Auth::check() && Auth::user()->role === 'user') {
      $this->redirectTo(route('home'));
      return true;
    }

    if (Auth::check() && Auth::user()->role === 'admin') {
      $this->redirectTo(route('home.admin'));
      return true;
    }

    $this->render('auth/login');
    return false;
  }

  private function validateCredentials(array $credentials): bool
  {
    if (empty($credentials['email']) || empty($credentials['password'])) {
      FlashMessage::danger('Por favor, preencha todos os campos.');
      return false;
    }

    $user = User::attempt($credentials);

    if ($user) {
      Auth::login($user);
      FlashMessage::success('Login realizado com sucesso');
      return true;
    } else {
      FlashMessage::danger('Credenciais inválidas');
      return false;
    }
  }

  public function login(Request $request): void
  {
    $credentials = $request->only(['email', 'password']);

    if ($this->validateCredentials($credentials)) {
      $this->redirectTo(route('home'));
    } else {
      $this->redirectTo(route('users.login'));
    }
  }

  public function logout(): bool
  {
    Auth::logout();
    FlashMessage::success('Logout realizado com sucesso');
    $this->redirectTo(route('users.login'));
    return true;
  }
}
