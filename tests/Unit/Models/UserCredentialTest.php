<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\Role;
use App\Models\UserCredential;
use Tests\TestCase;

/**
 * Testes unitários para o modelo UserCredential
 */
class UserCredentialTest extends TestCase
{
    private ?Employee $testEmployee = null;

    /**
     * Configura dados para teste
     */
    public function setUp(): void
    {
        parent::setUp();

        // Criar um cargo para o funcionário
        $role = new Role([
            'name' => 'Cargo Teste Credential',
            'description' => 'Cargo para teste de credenciais'
        ]);
        $role->save();

        // Criar um funcionário para teste
        $this->testEmployee = new Employee([
            'name' => 'Funcionário Teste Credential',
            'email' => 'credential_test_' . uniqid() . '@example.com',
            'cpf' => uniqid(),
            'birth_date' => '1990-01-01',
            'role_id' => $role->id,
            'salary' => 5000.00,
            'hire_date' => date('Y-m-d'),
            'status' => 'Active'
        ]);
        $this->testEmployee->save();
    }

    /**
     * Testa a criação de credenciais de usuário
     */
    public function test_create_user_credential(): void
    {
        $credentialData = [
            'employee_id' => $this->testEmployee->id,
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
            'last_updated' => date('Y-m-d H:i:s')
        ];

        $credential = new UserCredential($credentialData);
        $this->assertTrue($credential->save());
        $this->assertNotNull($credential->id);

        // Verificar se os dados foram salvos corretamente
        $savedCredential = UserCredential::findById($credential->id);
        $this->assertNotNull($savedCredential);
        $this->assertEquals($credentialData['employee_id'], $savedCredential->employee_id);
        $this->assertNotEmpty($savedCredential->password_hash);

        // Verificar se a senha foi hasheada corretamente
        $this->assertTrue(password_verify($credentialData['password'], $savedCredential->password_hash));
    }

    /**
     * Testa a validação de dados das credenciais
     */
    public function test_credential_validation(): void
    {
        // Credencial sem employee_id (obrigatório)
        $credential = new UserCredential([
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
            'last_updated' => date('Y-m-d H:i:s')
        ]);

        $this->assertFalse($credential->save());
        $this->assertNotEmpty($credential->errors('employee_id'));

        // Credencial com senhas diferentes
        $credential = new UserCredential([
            'employee_id' => $this->testEmployee->id,
            'password' => 'senha123',
            'password_confirmation' => 'senha456',
            'last_updated' => date('Y-m-d H:i:s')
        ]);

        $this->assertFalse($credential->save());
    }

    /**
     * Testa o método __set e __get para password_confirmation
     */
    public function test_password_confirmation_magic_methods(): void
    {
        $credential = new UserCredential([
            'employee_id' => $this->testEmployee->id,
            'last_updated' => date('Y-m-d H:i:s')
        ]);

        // Testar __set
        $credential->password_confirmation = 'senha123';

        // Testar __get
        $this->assertEquals('senha123', $credential->password_confirmation);
    }

    /**
     * Testa o método authenticate
     */
    public function test_authenticate(): void
    {
        $password = 'senha123';

        $credential = new UserCredential([
            'employee_id' => $this->testEmployee->id,
            'password' => $password,
            'password_confirmation' => $password,
            'last_updated' => date('Y-m-d H:i:s')
        ]);

        $this->assertTrue($credential->save());

        // Verificar autenticação com senha correta
        $this->assertTrue($credential->authenticate($password));

        // Verificar autenticação com senha incorreta
        $this->assertFalse($credential->authenticate('senha_errada'));
    }

    /**
     * Testa a relação belongsTo com Employee
     */
    public function test_employee_relationship(): void
    {
        $credential = new UserCredential([
            'employee_id' => $this->testEmployee->id,
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
            'last_updated' => date('Y-m-d H:i:s')
        ]);

        $this->assertTrue($credential->save());

        // Verificar se o método employee() retorna o funcionário correto
        $employee = $credential->employee();
        $this->assertNotNull($employee);
        $this->assertEquals($this->testEmployee->id, $employee->id);
        $this->assertEquals($this->testEmployee->name, $employee->name);
    }
}
