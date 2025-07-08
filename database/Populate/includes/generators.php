<?php

/**
 * Constante para formato de data e hora
 */
const DATETIME_FORMAT = 'Y-m-d H:i:s';

/**
 * Função para gerar um CPF válido
 * Implementa o algoritmo de validação do CPF brasileiro
 *
 * @return string CPF formatado (###.###.###-##)
 */
function gerarCpfValido()
{
  $n1 = rand(0, 9);
  $n2 = rand(0, 9);
  $n3 = rand(0, 9);
  $n4 = rand(0, 9);
  $n5 = rand(0, 9);
  $n6 = rand(0, 9);
  $n7 = rand(0, 9);
  $n8 = rand(0, 9);
  $n9 = rand(0, 9);

  // Cálculo do primeiro dígito verificador
  $j = 10 * $n1 + 9 * $n2 + 8 * $n3 + 7 * $n4 + 6 * $n5 + 5 * $n6 + 4 * $n7 + 3 * $n8 + 2 * $n9;
  $j = $j % 11;
  $j = $j < 2 ? 0 : 11 - $j;

  // Cálculo do segundo dígito verificador
  $k = 11 * $n1 + 10 * $n2 + 9 * $n3 + 8 * $n4 + 7 * $n5 + 6 * $n6 + 5 * $n7 + 4 * $n8 + 3 * $n9 + 2 * $j;
  $k = $k % 11;
  $k = $k < 2 ? 0 : 11 - $k;

  // Formatação do CPF
  return sprintf(
    '%d%d%d.%d%d%d.%d%d%d-%d%d',
    $n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8, $n9, $j, $k
  );
}

/**
 * Cria um usuário administrador
 *
 * @param int $roleId ID da role de administrador
 * @return \App\Models\Employee Objeto do funcionário criado
 */
function createAdminUser($roleId)
{
  $adminEmail = 'klesio@admin.com';
  $existingAdmin = \App\Models\Employee::findByEmail($adminEmail);

  if (!$existingAdmin) {
    echo "Criando usuário administrador...\n";

    // Primeiro criamos o funcionário sem avatar
    $admin = new \App\Models\Employee([
      'name' => 'Klesio Nascimento',
      'cpf' => gerarCpfValido(),
      'email' => $adminEmail,
      'birth_date' => '1990-01-01',
      'role_id' => $roleId,
      'salary' => 10000.00,
      'hire_date' => '2020-01-01',
      'status' => 'Active',
      'address' => 'Rua das Flores, 123',
      'city' => 'Curitiba',
      'state' => 'PR',
      'zipcode' => '80000-000',
      'notes' => 'Administrador do sistema',
      'created_at' => (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format(DATETIME_FORMAT),
    ]);
    $admin->save();

    // Criar avatar para o administrador usando o ID do funcionário
    $adminAvatar = createUserAvatar('admin', $admin->id);
    // Atualiza o funcionário com o nome do avatar
    $admin->avatar_name = $adminAvatar;
    $admin->save();

    $credential = new \App\Models\UserCredential([
      'employee_id' => $admin->id,
      'password' => DEFAULT_PASSWORD,
      'password_confirmation' => DEFAULT_PASSWORD
    ]);
    $credential->save();

    echo "Administrador criado com sucesso!\n";
    return $admin;
  } else {
    echo "Administrador já existe.\n";
    return $existingAdmin;
  }
}

/**
 * Cria um usuário de RH
 *
 * @param int $roleId ID da role de RH
 * @return \App\Models\Employee Objeto do funcionário criado
 */
function createHRUser($roleId)
{
  $hrEmail = 'caio@rh.com';
  $existingHR = \App\Models\Employee::findByEmail($hrEmail);

  if (!$existingHR) {
    echo "Criando usuário de RH...\n";

    // Primeiro criamos o funcionário sem avatar
    $hr = new \App\Models\Employee([
      'name' => 'Caio Henrique',
      'cpf' => gerarCpfValido(),
      'email' => $hrEmail,
      'birth_date' => '1992-05-15',
      'role_id' => $roleId,
      'salary' => 8000.00,
      'hire_date' => '2020-02-01',
      'status' => 'Active',
      'address' => 'Avenida Brasil, 456',
      'city' => 'Curitiba',
      'state' => 'PR',
      'zipcode' => '80000-000',
      'notes' => 'Usuário de RH',
      'created_at' => (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format(DATETIME_FORMAT),
    ]);
    $hr->save();

    // Criar avatar para o usuário de RH usando o ID do funcionário
    $hrAvatar = createUserAvatar('hr', $hr->id);
    // Atualiza o funcionário com o nome do avatar
    $hr->avatar_name = $hrAvatar;
    $hr->save();

    $credential = new \App\Models\UserCredential([
      'employee_id' => $hr->id,
      'password' => DEFAULT_PASSWORD,
      'password_confirmation' => DEFAULT_PASSWORD
    ]);
    $credential->save();

    echo "Usuário de RH criado com sucesso!\n";
    return $hr;
  } else {
    echo "Usuário de RH já existe.\n";
    return $existingHR;
  }
}

/**
 * Cria usuários regulares a partir de um array de dados
 *
 * @param array $usuarios Array com dados dos usuários
 * @param int $roleId ID da role de usuário regular
 * @param array $enderecos Array com endereços para escolha aleatória
 * @param array $salarios Array com salários para escolha aleatória
 */
function createRegularUsers($usuarios, $roleId, $enderecos, $salarios)
{
  echo "Criando usuários regulares...\n";

  foreach ($usuarios as $index => $usuario) {
    $email = strtolower(str_replace(' ', '.', $usuario['name'])) . '@user.com';
    $email = str_replace(
      ['ç', 'ã', 'á', 'à', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú'],
      ['c', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u'],
      $email
    );

    $existingUser = \App\Models\Employee::findByEmail($email);
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

      // Criar primeiro o funcionário sem avatar
      $employee = new \App\Models\Employee([
        'name' => $usuario['name'],
        'cpf' => gerarCpfValido(),
        'email' => $email,
        'birth_date' => $usuario['birth_date'],
        'role_id' => $roleId,
        'salary' => $salario,
        'hire_date' => $hireDate,
        'status' => 'Active',
        'address' => $endereco . ', ' . $numeroRua,
        'city' => $usuario['city'],
        'state' => $usuario['state'],
        'zipcode' => $zipcode,
        'notes' => 'Usuário criado automaticamente para testes',
        'created_at' => (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format(DATETIME_FORMAT),
      ]);
      $employee->save();

      // Para alguns usuários, adicione a foto de perfil (aproximadamente 1 em cada 3)
      if ($index % 3 === 0) {
        // Usar nossa função para criar avatar de usuário com o ID do funcionário
        $avatar_name = createUserAvatar('user', $employee->id);
        // Atualizar funcionário com o nome do avatar
        $employee->avatar_name = $avatar_name;
        $employee->save();
      }

      $credential = new \App\Models\UserCredential([
        'employee_id' => $employee->id,
        'password' => DEFAULT_PASSWORD,
        'password_confirmation' => DEFAULT_PASSWORD
      ]);
      $credential->save();

      $avatarMsg = $avatar_name ? " (com foto de perfil)" : "";
      echo "Usuário criado: {$usuario['name']} - {$email}{$avatarMsg}\n";
    } else {
      echo "Usuário {$usuario['name']} já existe.\n";
    }
  }
}
