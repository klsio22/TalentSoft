<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;
use App\Models\User;

class UserController extends Controller
{

  public function login(Request $request): void
  {
    $credentials = $request->only(['email', 'password']);

    $user = User::attempt($credentials);

    if ($user) {
      Auth::login($user);
      FlashMessage::success('Login realizado com sucesso');
      $this->redirectTo(route('home'));
    } else {
      FlashMessage::danger('Credenciais inválidas ou user não encontrado');
      $this->redirectTo(route('users.login'));
    }
  }

  public function logout(): void
  {
    Auth::logout();
    FlashMessage::success('Logout realizado com sucesso');
    $this->redirectTo(route('users.login'));
  }

  public function listUsers(): void
  {
    $users = User::all();
    $this->render('users/list', ['users' => $users]);
  }
}
