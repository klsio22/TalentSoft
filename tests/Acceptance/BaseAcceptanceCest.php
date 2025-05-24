<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use App\Models\Employee;
use App\Models\Role;
use App\Models\UserCredential;
use Core\Database\Database;
use Core\Env\EnvLoader;
use Tests\Support\AcceptanceTester;

/**
 * Classe Base para Testes de Aceitação
 *
 * Esta classe fornece a estrutura base para todos os testes de aceitação
 * da aplicação TalentSoft. Ela é responsável por:
 * - Configurar o ambiente de teste
 * - Inicializar e popular o banco de dados
 * - Limpar os dados após cada teste
 *
 * @author TalentSoft Team
 * @package Tests\Acceptance
 */
class BaseAcceptanceCest
{
    /** Senha padrão para todos os usuários de teste */
    private const DEFAULT_PASSWORD = '123456';

    /**
     * Método executado antes de cada teste
     *
     * Prepara o ambiente de teste criando e populando o banco de dados
     * com dados necessários para os testes de aceitação.
     *
     * @param AcceptanceTester $page Instância do testador de aceitação
     * @return void
     */
    public function _before(AcceptanceTester $page): void
    {
        EnvLoader::init();
        Database::create();
        Database::migrate();
        $this->populateTestData();
    }

    /**
     * Método executado após cada teste
     *
     * Limpa o ambiente de teste removendo o banco de dados
     * para garantir isolamento entre os testes.
     *
     * @param AcceptanceTester $page Instância do testador de aceitação
     * @return void
     */
    public function _after(AcceptanceTester $page): void
    {
        Database::drop();
    }

    /**
     * Popula o banco de dados com dados de teste
     *
     * Cria roles (perfis) e usuários necessários para execução
     * dos testes de aceitação da aplicação.
     *
     * @return void
     */
    private function populateTestData(): void
    {
        $adminRole = new Role([
            'name' => 'admin',
            'description' => 'Administrador com acesso completo ao sistema'
        ]);
        $adminRole->save();

        $hrRole = new Role([
            'name' => 'hr',
            'description' => 'Recursos humanos com acesso a funções de RH'
        ]);
        $hrRole->save();

        $userRole = new Role([
            'name' => 'user',
            'description' => 'Usuário comum com acesso limitado'
        ]);
        $userRole->save();

        $admin = new Employee([
            'name' => 'Klesio Nascimento',
            'cpf' => '111.111.111-11',
            'email' => 'klesio@admin.com',
            'role_id' => $adminRole->id,
            'hire_date' => date('Y-m-d'),
            'status' => 'Active',
        ]);
        $admin->save();

        $adminCredential = new UserCredential([
            'employee_id' => $admin->id,
            'password' => self::DEFAULT_PASSWORD,
            'password_confirmation' => self::DEFAULT_PASSWORD
        ]);
        $adminCredential->save();

        $hr = new Employee([
            'name' => 'Caio Silva',
            'cpf' => '222.222.222-22',
            'email' => 'caio@rh.com',
            'role_id' => $hrRole->id,
            'hire_date' => date('Y-m-d'),
            'status' => 'Active',
        ]);
        $hr->save();

        $hrCredential = new UserCredential([
            'employee_id' => $hr->id,
            'password' => self::DEFAULT_PASSWORD,
            'password_confirmation' => self::DEFAULT_PASSWORD
        ]);
        $hrCredential->save();

        $user = new Employee([
            'name' => 'Flavio Santos',
            'cpf' => '333.333.333-33',
            'email' => 'flavio@user.com',
            'role_id' => $userRole->id,
            'hire_date' => date('Y-m-d'),
            'status' => 'Active',
        ]);
        $user->save();

        $userCredential = new UserCredential([
            'employee_id' => $user->id,
            'password' => self::DEFAULT_PASSWORD,
            'password_confirmation' => self::DEFAULT_PASSWORD
        ]);
        $userCredential->save();
    }
}
