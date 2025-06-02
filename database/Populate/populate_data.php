<?php

require __DIR__ . '/../../config/bootstrap.php';

use App\Models\Employee;
use App\Models\Role;
use App\Models\UserCredential;
use Core\Database\Database;

Database::migrate();

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

echo "Criando usuário administrador Klesio...\n";
$admin = Employee::findByEmail('klesio@admin.com');
if (!$admin) {
    $admin = new Employee([
        'name' => 'Klesio Nascimento',
        'cpf' => '123.456.789-09',
        'email' => 'klesio@admin.com',
        'birth_date' => '1990-05-15',
        'role_id' => $adminRole->id,
        'salary' => 15000.00,
        'hire_date' => '2020-01-15',
        'status' => 'Active',
        'address' => 'Rua das Flores, 123',
        'city' => 'Curitiba',
        'state' => 'PR',
        'zipcode' => '80010-000',
        'notes' => 'Administrador principal do sistema'
    ]);
    $admin->save();

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
        'name' => 'Caio Silva Santos',
        'cpf' => '987.654.321-00',
        'email' => 'caio@rh.com',
        'birth_date' => '1985-08-20',
        'role_id' => $hrRole->id,
        'salary' => 8500.00,
        'hire_date' => '2021-03-10',
        'status' => 'Active',
        'address' => 'Avenida Central, 456',
        'city' => 'Curitiba',
        'state' => 'PR',
        'zipcode' => '80020-000',
        'notes' => 'Responsável pelos recursos humanos'
    ]);
    $hr->save();

    $hrCredential = new UserCredential([
        'employee_id' => $hr->id,
        'password' => '123456',
        'password_confirmation' => '123456'
    ]);
    $hrCredential->save();
}

// Array de usuários para criar
$usuarios = [
    ['name' => 'Ana Beatriz Silva', 'cpf' => '123.456.789-10', 'birth_date' => '1992-01-15', 'city' => 'São Paulo', 'state' => 'SP'],
    ['name' => 'Bruno Costa Santos', 'cpf' => '234.567.891-01', 'birth_date' => '1988-03-22', 'city' => 'Rio de Janeiro', 'state' => 'RJ'],
    ['name' => 'Carlos Eduardo Lima', 'cpf' => '345.678.912-02', 'birth_date' => '1995-07-10', 'city' => 'Belo Horizonte', 'state' => 'MG'],
    ['name' => 'Daniela Oliveira', 'cpf' => '456.789.123-03', 'birth_date' => '1990-11-05', 'city' => 'Porto Alegre', 'state' => 'RS'],
    ['name' => 'Eduardo Ferreira', 'cpf' => '567.891.234-04', 'birth_date' => '1987-09-18', 'city' => 'Salvador', 'state' => 'BA'],
    ['name' => 'Fernanda Ribeiro', 'cpf' => '678.912.345-05', 'birth_date' => '1993-12-28', 'city' => 'Fortaleza', 'state' => 'CE'],
    ['name' => 'Gabriel Souza', 'cpf' => '789.123.456-06', 'birth_date' => '1991-04-14', 'city' => 'Recife', 'state' => 'PE'],
    ['name' => 'Helena Martins', 'cpf' => '891.234.567-07', 'birth_date' => '1989-06-30', 'city' => 'Manaus', 'state' => 'AM'],
    ['name' => 'Igor Pereira', 'cpf' => '912.345.678-08', 'birth_date' => '1994-02-08', 'city' => 'Belém', 'state' => 'PA'],
    ['name' => 'Julia Almeida', 'cpf' => '123.456.789-09', 'birth_date' => '1986-10-25', 'city' => 'Goiânia', 'state' => 'GO'],
    ['name' => 'Kevin Rodrigues', 'cpf' => '234.567.891-11', 'birth_date' => '1996-05-12', 'city' => 'Vitória', 'state' => 'ES'],
    ['name' => 'Larissa Castro', 'cpf' => '345.678.912-12', 'birth_date' => '1988-08-07', 'city' => 'João Pessoa', 'state' => 'PB'],
    ['name' => 'Marcos Vieira', 'cpf' => '456.789.123-13', 'birth_date' => '1992-12-19', 'city' => 'Aracaju', 'state' => 'SE'],
    ['name' => 'Natália Gomes', 'cpf' => '567.891.234-14', 'birth_date' => '1990-03-04', 'city' => 'Teresina', 'state' => 'PI'],
    ['name' => 'Otávio Barbosa', 'cpf' => '678.912.345-15', 'birth_date' => '1985-07-21', 'city' => 'Natal', 'state' => 'RN'],
    ['name' => 'Patrícia Cunha', 'cpf' => '789.123.456-16', 'birth_date' => '1993-11-16', 'city' => 'Maceió', 'state' => 'AL'],
    ['name' => 'Rafael Torres', 'cpf' => '891.234.567-17', 'birth_date' => '1987-01-29', 'city' => 'Campo Grande', 'state' => 'MS'],
    ['name' => 'Sabrina Dias', 'cpf' => '912.345.678-18', 'birth_date' => '1994-09-03', 'city' => 'Cuiabá', 'state' => 'MT'],
    ['name' => 'Thiago Moreira', 'cpf' => '123.456.789-19', 'birth_date' => '1991-05-26', 'city' => 'Florianópolis', 'state' => 'SC'],
    ['name' => 'Vanessa Cardoso', 'cpf' => '234.567.891-20', 'birth_date' => '1989-04-11', 'city' => 'Brasília', 'state' => 'DF'],
    ['name' => 'William Araújo', 'cpf' => '345.678.912-21', 'birth_date' => '1995-08-17', 'city' => 'Palmas', 'state' => 'TO'],
    ['name' => 'Ximena Nogueira', 'cpf' => '456.789.123-22', 'birth_date' => '1986-12-02', 'city' => 'Boa Vista', 'state' => 'RR'],
    ['name' => 'Yago Freitas', 'cpf' => '567.891.234-23', 'birth_date' => '1992-06-13', 'city' => 'Macapá', 'state' => 'AP'],
    ['name' => 'Zara Mendes', 'cpf' => '678.912.345-24', 'birth_date' => '1988-10-08', 'city' => 'Rio Branco', 'state' => 'AC'],
    ['name' => 'André Lopes', 'cpf' => '789.123.456-25', 'birth_date' => '1993-02-23', 'city' => 'São Luís', 'state' => 'MA'],
    ['name' => 'Bianca Ramos', 'cpf' => '891.234.567-26', 'birth_date' => '1990-07-01', 'city' => 'Curitiba', 'state' => 'PR'],
    ['name' => 'Cláudio Xavier', 'cpf' => '912.345.678-27', 'birth_date' => '1987-11-14', 'city' => 'Londrina', 'state' => 'PR'],
    ['name' => 'Débora Fonseca', 'cpf' => '123.456.789-28', 'birth_date' => '1994-03-09', 'city' => 'Maringá', 'state' => 'PR']
];

$salarios = [4500.00, 5000.00, 5500.00, 6000.00, 6500.00, 7000.00];
$enderecos = [
    'Rua das Palmeiras', 'Avenida Brasil', 'Rua do Comércio', 'Avenida Paulista',
    'Rua XV de Novembro', 'Avenida Beira Mar', 'Rua da Liberdade', 'Avenida Atlântica'
];

echo "Criando 28 usuários adicionais...\n";
foreach ($usuarios as $index => $usuario) {
    $email = strtolower(str_replace(' ', '.', $usuario['name'])) . '@user.com';
    $email = str_replace(['ç', 'ã', 'á', 'à', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú'],
                        ['c', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u'], $email);

    $existingUser = Employee::findByEmail($email);
    if (!$existingUser) {
        $numeroRua = rand(100, 999);
        $endereco = $enderecos[array_rand($enderecos)];
        $salario = $salarios[array_rand($salarios)];
        $zipcode = sprintf('%05d-%03d', rand(10000, 99999), rand(100, 999));

        // Datas de contratação entre 2020 e 2024
        $anoContratacao = rand(2020, 2024);
        $mesContratacao = rand(1, 12);
        $diaContratacao = rand(1, 28);
        $hireDate = sprintf('%04d-%02d-%02d', $anoContratacao, $mesContratacao, $diaContratacao);

        $employee = new Employee([
            'name' => $usuario['name'],
            'cpf' => $usuario['cpf'],
            'email' => $email,
            'birth_date' => $usuario['birth_date'],
            'role_id' => $userRole->id,
            'salary' => $salario,
            'hire_date' => $hireDate,
            'status' => 'Active',
            'address' => $endereco . ', ' . $numeroRua,
            'city' => $usuario['city'],
            'state' => $usuario['state'],
            'zipcode' => $zipcode,
            'notes' => 'Usuário criado automaticamente para testes'
        ]);
        $employee->save();

        $credential = new UserCredential([
            'employee_id' => $employee->id,
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $credential->save();

        echo "Usuário criado: {$usuario['name']} - {$email}\n";
    }
}

echo "\n=== RESUMO ===\n";
echo "Total de funcionários criados: 30\n";
echo "Admin: klesio@admin.com (senha: 123456)\n";
echo "RH: caio@rh.com (senha: 123456)\n";
echo "28 usuários com emails @user.com (senha: 123456)\n";
echo "\nDados inseridos com sucesso!\n";
