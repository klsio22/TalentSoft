<?php

namespace Tests\Unit\Controllers;

use App\Models\AdminUser;
use Tests\TestCase;
use Tests\Exceptions\TestSetupException;
use Lib\Authentication\Auth;
use Core\Http\Request;
use App\Controllers\AdminController;
use Core\Database\Database;
use Lib\FlashMessage;

class AdminControllerTest extends TestCase
{
  private AdminController $controller;
  private AdminUser $admin;

  public function setUp(): void
  {
    parent::setUp();

    // Garantir que a tabela roles existe e tem o admin
    $db = Database::getInstance();
    $db->exec("
            INSERT IGNORE INTO roles (id, name, description)
            VALUES (1, 'admin', 'Administrador do sistema')
        ");

    // Obter ID da role admin
    $roleId = $db->query("SELECT id FROM roles WHERE name = 'admin'")->fetchColumn();

    if (!$roleId) {
      throw new TestSetupException(
        'Role "admin" não encontrada no banco de dados.',
        'setup_role_check'
      );
    }

    // Dados para o usuário admin
    $userData = [
      'name' => 'Admin Test',
      'email' => 'admin@test.com',
      'password' => password_hash('123456', PASSWORD_DEFAULT),
      'role_id' => $roleId,
      'cpf' => '123.456.789-00',
      'phone' => '(11) 99999-9999',
      'birth_date' => '1990-01-01',
      'salary' => 5000.00,
      'address_street' => 'Rua Teste',
      'address_number' => '123',
      'address_complement' => 'Apto 1',
      'address_neighborhood' => 'Centro',
      'address_city' => 'São Paulo',
      'address_state' => 'SP',
      'address_zipcode' => '01001-000',
      'hire_date' => date('Y-m-d'),
      'status' => 'Active',
      'notes' => 'Usuário de teste para AdminControllerTest'
    ];

    $this->admin = new AdminUser($userData);

    if (!$this->admin->create()) {
      throw new TestSetupException(
        'Falha ao criar usuário admin para testes.',
        'setup_user_creation'
      );
    }

    $this->controller = new AdminController();
  }

  public function tearDown(): void
  {
    if (isset($this->admin) && $this->admin->id) {
      AdminUser::delete($this->admin->id);
    }

    // Limpar sessão
    Auth::logout();

    parent::tearDown();
  }

  public function testShowRegisterFormRequiresAuth(): void
  {
    Auth::logout();

    $this->controller->showRegisterForm();

    $headers = headers_list();
    $this->assertContains('Location: /admin/login', $headers);
  }

  public function testShowRegisterFormAllowedForAdmin(): void
  {
    Auth::login($this->admin);

    ob_start();
    $this->controller->showRegisterForm();
    $output = ob_get_clean();

    $this->assertStringContainsString('register/admin/register', $output);
  }

  public function testRegisterDeniedForNonAdmin(): void
  {
    Auth::logout();

    $request = new Request([
      'name' => 'Test User',
      'email' => 'test@example.com'
    ]);

    $this->controller->register($request);

    $headers = headers_list();
    $this->assertContains('Location: /admin/login', $headers);
  }

  public function testRegisterAllowedForAdmin(): void
  {
    Auth::login($this->admin);

    $userData = [
      'name' => 'New Employee',
      'email' => 'employee@test.com',
      'password' => '123456',
      'cpf' => '987.654.321-00',
      'role_id' => 2, // role funcionário
      'phone' => '(11) 88888-8888',
      'birth_date' => '1995-01-01',
      'salary' => 3000.00
    ];

    $request = new Request($userData);
    $this->controller->register($request);

    $headers = headers_list();
    $this->assertContains('Location: /admin/home', $headers);

    // Verificar se usuário foi criado
    $newUser = AdminUser::findByEmail('employee@test.com');
    $this->assertNotNull($newUser);
    $this->assertEquals($userData['name'], $newUser->name);
  }

  public function testRegisterValidatesRequiredFields(): void
  {
    Auth::login($this->admin);

    $request = new Request([
      'name' => '',
      'email' => 'invalid-email'
    ]);

    $this->controller->register($request);

    $flashMessages = FlashMessage::get();
    $this->assertStringContainsString('Nome é obrigatório', $flashMessages['danger']);
    $this->assertStringContainsString('Email inválido', $flashMessages['danger']);
  }
}
