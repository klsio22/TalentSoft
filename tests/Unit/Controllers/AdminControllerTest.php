<?php

namespace Tests\Unit\Controllers;

use App\Models\AdminUser;
use Tests\TestCase;
use Core\Database\Database;
use Lib\Authentication\Auth;
use PDO;

class AdminControllerTest extends TestCase
{
  private AdminUser $admin;

  public function setUp(): void
  {
    parent::setUp();

    // Criar role admin se não existir
    $db = Database::getInstance();
    $db->exec("INSERT IGNORE INTO roles (id, name, description) VALUES (1, 'admin', 'Administrador')");

    // Gerar dados aleatórios para o admin
    $adminData = [
      'name' => 'Admin Test ' . uniqid(),
      'email' => 'admin_' . uniqid() . '@test.com',
      'password' => password_hash('123456', PASSWORD_DEFAULT),
      'cpf' => $this->generateRandomCPF(),
      'role_id' => 1,
      'phone' => '(11) 99999-' . rand(1000, 9999),
      'birth_date' => '1990-01-01',
      'salary' => 5000.00
    ];

    // Criar usuário admin
    $this->admin = new AdminUser($adminData);
  }

  private function generateRandomCPF(): string
  {
    $n1 = rand(0, 9);
    $n2 = rand(0, 9);
    $n3 = rand(0, 9);
    return sprintf(
      "%d%d%d.%d%d%d.%d%d%d-00",
      $n1,
      $n2,
      $n3,
      rand(0, 9),
      rand(0, 9),
      rand(0, 9),
      rand(0, 9),
      rand(0, 9),
      rand(0, 9)
    );
  }

  public function testAdminUserCreation(): void
  {
    $this->assertInstanceOf(AdminUser::class, $this->admin);
    $this->assertEquals(1, $this->admin->roleId);
  }

  public function testAdminHasRequiredFields(): void
  {
    $this->assertNotEmpty($this->admin->name);
    $this->assertNotEmpty($this->admin->email);
    $this->assertNotEmpty($this->admin->cpf);
    $this->assertEquals(1, $this->admin->roleId);
  }

  public function tearDown(): void
  {
    // Limpar dados de teste
    if (isset($this->admin) && $this->admin->id) {
      AdminUser::delete($this->admin->id);
    }
    parent::tearDown();
  }


  public function testAdminUserIsSavedInDatabase(): void
  {
    // Salvar o usuário no banco
    $saved = $this->admin->create();
    $this->assertTrue($saved, "Falha ao salvar usuário no banco");

    // Buscar o usuário salvo do banco
    $db = Database::getInstance();
    $stmt = $db->prepare("
        SELECT e.*, r.name as role
        FROM employees e
        JOIN roles r ON e.role_id = r.id
        WHERE e.email = ?
    ");
    $stmt->execute([$this->admin->email]);
    $savedUser = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar se os dados foram salvos corretamente
    $this->assertNotNull($savedUser, "Usuário não encontrado no banco");
    $this->assertEquals($this->admin->name, $savedUser['name']);
    $this->assertEquals($this->admin->email, $savedUser['email']);
    $this->assertEquals($this->admin->cpf, $savedUser['cpf']);
    $this->assertEquals('admin', $savedUser['role']);
  }
}
