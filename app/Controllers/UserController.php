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

  public function editProfile(): void
  {
    if (!Auth::check()) {
      FlashMessage::danger('Você precisa estar logado para acessar essa página.');
      $this->redirectTo(route('users.login'));
      return;
    }

    $user = Auth::user();
    $this->render('users/edit', [
      'user' => $user,
      'isProfile' => true
    ]);
  }

  public function updateProfile(Request $request): void
  {
    if (!Auth::check()) {
      FlashMessage::danger('Você precisa estar logado para acessar essa página.');
      $this->redirectTo(route('users.login'));
      return;
    }

    try {
      $user = Auth::user();
      $data = User::sanitizeData([
        'id' => $user->id,
        'name' => $request->getData('name'),
        'email' => $request->getData('email')
      ]);

      if (User::update($data)) {
        FlashMessage::success('Perfil atualizado com sucesso!');
        $this->redirectTo(route('home'));
      } else {
        FlashMessage::danger('Erro ao atualizar perfil.');
        $this->redirectTo(route('profile.edit'));
      }
    } catch (\Exception $error) {
      error_log("Erro: " . $error->getMessage());
      FlashMessage::danger('Erro ao processar atualização.');
      $this->redirectTo(route('profile.edit'));
    }
  }
}
