<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use Core\Database\ActiveRecord\HasMany;
use Tests\TestCase;

/**
 * Testes unitários para o modelo Role
 */
class RoleTest extends TestCase
{
    /**
     * Testa a criação de um cargo
     */
    public function test_create_role(): void
    {
        $roleData = [
            'name' => 'Cargo Teste',
            'description' => 'Descrição do cargo teste'
        ];

        $role = new Role($roleData);
        $this->assertTrue($role->save());
        $this->assertNotNull($role->id);

        // Verificar se os dados foram salvos corretamente
        $savedRole = Role::findById($role->id);
        $this->assertNotNull($savedRole);
        $this->assertEquals($roleData['name'], $savedRole->name);
        $this->assertEquals($roleData['description'], $savedRole->description);
    }

    /**
     * Testa a validação de dados do cargo
     */
    public function test_role_validation(): void
    {
        // Cargo sem nome (obrigatório)
        $role = new Role([
            'description' => 'Descrição do cargo teste'
        ]);

        $this->assertFalse($role->save());
        $this->assertNotEmpty($role->errors('name'));
    }

    /**
     * Testa a relação HasMany com Employee
     */
    public function test_employees_relationship(): void
    {
        $role = new Role([
            'name' => 'Cargo Relacionamento',
            'description' => 'Teste de relacionamento'
        ]);

        $this->assertTrue($role->save());

        // Verificar se o método employees() retorna um objeto HasMany
        $this->assertInstanceOf(HasMany::class, $role->employees());
    }

    /**
     * Testa o método findByName
     */
    public function test_find_by_name(): void
    {
        // Criar um cargo com nome único para teste
        $uniqueName = 'Cargo Único ' . uniqid();
        $role = new Role([
            'name' => $uniqueName,
            'description' => 'Cargo para teste de busca por nome'
        ]);

        $this->assertTrue($role->save());

        // Buscar o cargo pelo nome
        $foundRole = Role::findByName($uniqueName);
        $this->assertNotNull($foundRole);
        $this->assertEquals($uniqueName, $foundRole->name);

        // Buscar um cargo que não existe
        $notFoundRole = Role::findByName('Cargo Inexistente ' . uniqid());
        $this->assertNull($notFoundRole);
    }
}
