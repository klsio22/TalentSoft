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

    $data = $request->only([
      'name',
      'email',
      'password',
      'cpf',
      'phone',
      'birth_date',
      'salary',
      'address_street',
      'address_number',
      'address_complement',
      'address_neighborhood',
      'address_city',
      'address_state',
      'address_zipcode',
      'nationality',
      'marital_status',
      'notes'
    ]);

    error_log("Dados recebidos no controller: " . print_r($data, true));

    try {
      $admin = AdminUser::register($data);

      FlashMessage::success('Usuário criado com sucesso', $admin);

      if ($admin) {
        FlashMessage::success('Usuário cadastrado com sucesso!');
        $this->redirectTo(route('home.admin'));
      } else {
        FlashMessage::danger('Erro ao cadastrar usuário.');
        error_log("Erro no cadastro: " . print_r($admin, true));
        $this->redirectTo(route('register.admin'));
      }
    } catch (\Exception $e) {
      error_log("Erro no registro: " . $e->getMessage());
      FlashMessage::danger('Erro ao processar cadastro.');
      $this->redirectTo(route('register.admin'));
    }
  }
  public function editUser(Request $request): void
  {
    try {
      $id = (int) $request->getParam('id');
      $user = User::findById($id);

      if (!$user) {
        FlashMessage::danger('Usuário não encontrado.');
        $this->redirectTo(route('users.list'));
        return;
      }

      if (Auth::user()->role !== 'admin') {
        FlashMessage::danger('Sem permissão para editar este usuário.');
        $this->redirectTo(route('users.list'));
        return;
      }

      error_log("Editando usuário: " . json_encode($user));
      $this->render('users/edit', ['user' => $user]);
    } catch (\Exception $e) {
      error_log("Erro ao editar: " . $e->getMessage());
      FlashMessage::danger('Erro ao carregar usuário.');
      $this->redirectTo(route('users.list'));
    }
  }

  public function updateUser(Request $request): void
  {
    try {
      $id = (int) $request->getParam('id');
      error_log("ID recebido: " . $id);

      if ($id <= 0) {
        FlashMessage::danger('ID inválido');
        $this->redirectTo(route('users.list'));
        return;
      }

      $data = [
        'id' => $id,
        'name' => $request->getData('name'),
        'email' => $request->getData('email'),
        'cpf' => $request->getData('cpf'),
        'phone' => $request->getData('phone'),
        'birth_date' => $request->getData('birth_date'),
        'salary' => $request->getData('salary'),
        'address_street' => $request->getData('address_street'),
        'address_number' => $request->getData('address_number'),
        'address_complement' => $request->getData('address_complement'),
        'address_neighborhood' => $request->getData('address_neighborhood'),
        'address_city' => $request->getData('address_city'),
        'address_state' => $request->getData('address_state'),
        'address_zipcode' => $request->getData('address_zipcode'),
        'nationality' => $request->getData('nationality'),
        'marital_status' => $request->getData('marital_status'),
        'notes' => $request->getData('notes')
      ];

      error_log("Dados para atualização: " . print_r($data, true));

      if (AdminUser::update($data)) {
        FlashMessage::success('Usuário atualizado com sucesso!');
        $this->redirectTo(route('users.list'));
      } else {
        FlashMessage::danger('Erro ao atualizar usuário.');
        $this->render('users/edit', ['user' => (object)$data]);
      }
    } catch (\Exception $e) {
      error_log("Erro na atualização: " . $e->getMessage());
      FlashMessage::danger('Erro ao processar atualização.');
      $this->redirectTo(route('users.list'));
    }
  }


  public function deleteUser(Request $request): void
  {
    error_log("ID recebido: " . $request->getParam('id'));
    try {
      if (!Auth::check() || Auth::user()->role !== 'admin') {
        FlashMessage::danger('Sem permissão para deletar usuários.');
        $this->redirectTo(route('users.list'));
        return;
      }

      $id = (int) $request->getParam('id');

      if (AdminUser::delete($id)) {
        FlashMessage::success('Usuário deletado com sucesso!');
      } else {
        FlashMessage::danger('Erro ao deletar usuário.');
      }

      $this->redirectTo(route('users.list'));
    } catch (\Exception $e) {
      error_log("Erro ao deletar: " . $e->getMessage());
      FlashMessage::danger('Erro ao processar exclusão.');
      $this->redirectTo(route('users.list'));
    }
  }
}
