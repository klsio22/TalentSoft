<?php

namespace App\Controllers;

use App\Models\AdminUser;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;
use App\Models\User;

class AdminController extends Controller
{

  public function showLoginForm()
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
    error_log("Tentativa de login admin com: " . print_r($credentials, true));

    $user = User::attempt($credentials);
    error_log("Usuário retornado: " . ($user ? "sim" : "não"));

    if ($user) {
      error_log("Role do usuário: " . $user->role);
    }

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


  public function showRegisterForm(): void
  {
    if (!Auth::check() || Auth::user()->role !== 'admin') {
      FlashMessage::danger('Acesso negado.');
      $this->redirectTo(route('admin.login'));
      return;
    }

    $this->render('register/admin/register');
  }

  public function register(Request $request): void
  {
    if (!Auth::check() || Auth::user()->role !== 'admin') {
      FlashMessage::danger('Acesso negado.');
      $this->redirectTo(route('admin.login'));
      return;
    }

    $data = $request->only(['name', 'email', 'password']);
    $admin = AdminUser::register($data);

    if ($admin) {
      $this->redirectTo(route('home.admin'));
    } else {
      $this->redirectTo(route('register.admin'));
    }
  }

  public function editUser(Request $request): void
  {
    $id = (int) $request->getParam('id');
    $user = User::findById($id);

    if (!$user) {
      FlashMessage::danger('Usuário não encontrado.');
      $this->redirectTo(route('users.list'));
      return;
    }

    if (Auth::user()->role !== 'admin' && Auth::user()->id !== $id) {
      FlashMessage::danger('Sem permissão para editar este usuário.');
      $this->redirectTo(route('users.list'));
      return;
    }

    $this->render('users/edit', ['user' => $user]);
  }

  public function updateUser(Request $request): void
  {
    try {
      $id = (int) $request->getParam('id');
      error_log("ID recebido: " . $id);

      $data = [
        'id' => $id,
        'name' => $request->getData('name'),
        'email' => $request->getData('email')
      ];

      if (User::update($data)) {
        FlashMessage::success('Usuário atualizado com sucesso!');
      } else {
        FlashMessage::danger('Erro ao atualizar usuário.');
      }

      $this->redirectTo('/users');
    } catch (\Exception $e) {
      error_log("Erro: " . $e->getMessage());
      FlashMessage::danger('Erro ao processar atualização.');
      $this->redirectTo('/users');
    }
  }


  public function deleteUser(Request $request): void
  {
    try {
      if (!Auth::check() || Auth::user()->role !== 'admin') {
        FlashMessage::danger('Sem permissão para deletar usuários.');
        $this->redirectTo('/users');
        return;
      }

      $id = (int) $request->getParam('id');

      if (User::delete($id)) {
        FlashMessage::success('Usuário deletado com sucesso!');
      } else {
        FlashMessage::danger('Erro ao deletar usuário.');
      }

      $this->redirectTo('/users');
    } catch (\Exception $e) {
      error_log("Erro ao deletar: " . $e->getMessage());
      FlashMessage::danger('Erro ao processar exclusão.');
      $this->redirectTo('/users');
    }
  }
}
