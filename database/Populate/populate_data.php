<?php

require __DIR__ . '/../../config/bootstrap.php';

use App\Models\Employee;
use App\Models\Role;
use App\Models\UserCredential;
use Core\Database\Database;

Database::migrate();

// Garantir que os papéis padrão existam
$adminRole = Role::findByName('admin');
$hrRole = Role::findByName('hr');
$userRole = Role::findByName('user');

if (!$adminRole) {
    echo "Criando papel de admin...\n";
    $adminRole = new Role(['name' => 'admin', 'description' => 'Administrador com acesso completo ao sistema']);
    $adminRole->save();
}

if (!$hrRole) {
    echo "Criando papel de RH...\n";
    $hrRole = new Role(['name' => 'hr', 'description' => 'Recursos humanos com acesso a funções de RH']);
    $hrRole->save();
}

if (!$userRole) {
    echo "Criando papel de usuário...\n";
    $userRole = new Role(['name' => 'user', 'description' => 'Usuário comum com acesso limitado']);
    $userRole->save();
}

// Criar funcionários para teste com emails solicitados
echo "Criando usuário administrador Klesio...\n";
$admin = Employee::findByEmail('klesio@admin.com');
if (!$admin) {
    $admin = new Employee([
        'name' => 'Klesio Nascimento',
        'cpf' => '111.111.111-11',
        'email' => 'klesio@admin.com',
        'role_id' => $adminRole->id,
        'hire_date' => date('Y-m-d'),
        'status' => 'Active',
    ]);
    $admin->save();

    // Criar credencial para admin
    $adminCredential = new UserCredential([
        'employee_id' => $admin->id,
        'password' => '123456',
        'password_confirmation' => '123456'
    ]);
    $adminCredential->save();
}

echo "Criando usuário RH Caio...\n";
$hr = Employee::findByEmail('caio@rh.com');
if (!$hr) {
    $hr = new Employee([
        'name' => 'Caio Silva',
        'cpf' => '222.222.222-22',
        'email' => 'caio@rh.com',
        'role_id' => $hrRole->id,
        'hire_date' => date('Y-m-d'),
        'status' => 'Active',
    ]);
    $hr->save();

    // Criar credencial para RH
    $hrCredential = new UserCredential([
        'employee_id' => $hr->id,
        'password' => '123456',
        'password_confirmation' => '123456'
    ]);
    $hrCredential->save();
}

echo "Criando usuário comum Flavio...\n";
$user = Employee::findByEmail('flavio@user.com');
if (!$user) {
    $user = new Employee([
        'name' => 'Flavio Santos',
        'cpf' => '333.333.333-33',
        'email' => 'flavio@user.com',
        'role_id' => $userRole->id,
        'hire_date' => date('Y-m-d'),
        'status' => 'Active',
    ]);
    $user->save();

    // Criar credencial para usuário comum
    $userCredential = new UserCredential([
        'employee_id' => $user->id,
        'password' => '123456',
        'password_confirmation' => '123456'
    ]);
    $userCredential->save();
}

echo "Dados inseridos com sucesso!\n";
