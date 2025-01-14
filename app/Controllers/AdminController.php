<?php

namespace App\Controllers;

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
    if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
      FlashMessage::danger('Por favor, preencha todos os campos.');
      $this->redirectTo(route('register.admin'));
      return;
    }

    // Mapear name para username e adicionar role admin
    $userData = [
      'username' => $data['name'],
      'email' => $data['email'],
      'password' => password_hash($data['password'], PASSWORD_DEFAULT), // Hash da senha
      'role' => 'admin'
    ];

    $user = User::create($userData);

    if ($user) {
      FlashMessage::success('Usuário cadastrado com sucesso.');
      $this->redirectTo(route('home.admin'));
    } else {
      FlashMessage::danger('Erro ao cadastrar usuário. Email pode já estar em uso.');
      $this->redirectTo(route('register.admin'));
    }
  }

  public function listUsers(): void
  {
    $users = User::all();

    foreach ($users as $user) {
      error_log("ID: {$user->id}, Nome: {$user->username}, Email: {$user->email}, Role: {$user->role}");
    }
  }
}
