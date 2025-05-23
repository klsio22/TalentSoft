<?php

namespace Tests\Acceptance;

use App\Models\Employee;
use App\Models\Role;
use App\Models\UserCredential;
use Core\Database\Database;
use Core\Env\EnvLoader;
use Tests\Support\AcceptanceTester;

class BaseAcceptanceCest
{
    public function _before(AcceptanceTester $page): void
    {
        EnvLoader::init();
        Database::create();
        Database::migrate();
        $this->populateTestData();
    }

    public function _after(AcceptanceTester $page): void
    {
        Database::drop();
    }

    private function populateTestData(): void
    {
        // Criar roles
        $adminRole = new Role(['name' => 'admin', 'description' => 'Administrador com acesso completo ao sistema']);
        $adminRole->save();

        $hrRole = new Role(['name' => 'hr', 'description' => 'Recursos humanos com acesso a funções de RH']);
        $hrRole->save();

        $userRole = new Role(['name' => 'user', 'description' => 'Usuário comum com acesso limitado']);
        $userRole->save();

        // Criar funcionários de teste
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
            'password' => '123456',
            'password_confirmation' => '123456'
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
            'password' => '123456',
            'password_confirmation' => '123456'
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
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $userCredential->save();
    }
}
