<?php

// Carregar autoloader
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Importar classes necessárias
use App\Models\Employee;
use App\Models\UserCredential;
use App\Models\Role;
use Core\Database\Database;

// Configurar variáveis de ambiente para conexão com o banco de dados
// Isso é necessário porque o container PHP não carrega automaticamente o arquivo .env
if (!isset($_ENV['DB_HOST']) && !getenv('DB_HOST')) {
  $_ENV['DB_HOST'] = 'db';
  putenv('DB_HOST=db');
}

if (!isset($_ENV['DB_PORT']) && !getenv('DB_PORT')) {
  $_ENV['DB_PORT'] = '3306';
  putenv('DB_PORT=3306');
}

if (!isset($_ENV['DB_DATABASE']) && !getenv('DB_DATABASE')) {
  $_ENV['DB_DATABASE'] = 'talent_soft_development';
  putenv('DB_DATABASE=talent_soft_development');
}

if (!isset($_ENV['DB_USERNAME']) && !getenv('DB_USERNAME')) {
  $_ENV['DB_USERNAME'] = 'talent-soft';
  putenv('DB_USERNAME=talent-soft');
}

if (!isset($_ENV['DB_PASSWORD']) && !getenv('DB_PASSWORD')) {
  $_ENV['DB_PASSWORD'] = 'talent-soft';
  putenv('DB_PASSWORD=talent-soft');
}

// Constantes
define('DEFAULT_PASSWORD', '123456');
define('ROOT_DIR', dirname(__DIR__, 2));
define('UPLOADS_DIR', ROOT_DIR . '/public/uploads/');
define('AVATARS_DIR', UPLOADS_DIR . 'avatars/');
define('ASSETS_DIR', ROOT_DIR . '/public/assets/');
define('IMAGES_DIR', ASSETS_DIR . 'images/defaults/');
define('DEFAULT_AVATAR', 'default-avatar.jpg');

/**
 * Função para criar diretórios necessários
 */
function createRequiredDirectories()
{
  echo "\n=== Preparando diretórios ===\n";

  // Criar diretório de uploads/avatars
  if (!file_exists(AVATARS_DIR)) {
    echo "Criando diretório de avatares: " . AVATARS_DIR . "\n";
    if (mkdir(AVATARS_DIR, 0777, true)) {
      echo "Diretório de avatares criado com sucesso!\n";
    } else {
      echo "ERRO: Falha ao criar diretório de avatares.\n";
    }
  } else {
    echo "Diretório de avatares já existe.\n";
    // Garantir que as permissões estão corretas mesmo se o diretório já existir
    chmod(AVATARS_DIR, 0777);
  }
  
  // Verificar diretório de imagens
  if (!file_exists(IMAGES_DIR)) {
    echo "AVISO: Diretório de imagens não existe: " . IMAGES_DIR . "\n";
  } else {
    echo "Diretório de imagens existe.\n";
  }
}

/**
 * Função para preparar a imagem padrão
 *
 * @return bool true se a imagem padrão está disponível
 */
function prepareDefaultAvatar()
{
  echo "\n=== Preparando imagem padrão ===\n";

  $defaultAvatarSource = IMAGES_DIR . DEFAULT_AVATAR;
  $avatarDestName = 'default_' . uniqid() . '.jpg';
  $defaultAvatarDest = AVATARS_DIR . $avatarDestName;
  $success = false;

  // Verificar se a imagem padrão existe na pasta de origem
  if (file_exists($defaultAvatarSource)) {
    echo "Imagem padrão encontrada em: $defaultAvatarSource\n";

    // Copiar para o diretório de avatares
    echo "Copiando imagem padrão para: $defaultAvatarDest\n";
    if (copy($defaultAvatarSource, $defaultAvatarDest)) {
      echo "Imagem padrão copiada com sucesso para o diretório de avatares!\n";
      $success = true;
    } else {
      echo "ERRO: Falha ao copiar imagem padrão.\n";
    }
  } else {
    echo "AVISO: Imagem padrão não encontrada em: $defaultAvatarSource\n";
  }

  return $success;
}

/**
 * Function to generate a valid CPF
 * Implements the Brazilian CPF validation algorithm
 *
 * @return string CPF formatted (###.###.###-##)
 */
function gerarCpfValido()
{
  $cpf = [];
  for ($i = 0; $i < 9; $i++) {
    $cpf[$i] = mt_rand(0, 9);
  }

  $soma = 0;
  for ($i = 0; $i < 9; $i++) {
    $soma += $cpf[$i] * (10 - $i);
  }
  $resto = $soma % 11;
  $cpf[9] = ($resto < 2) ? 0 : 11 - $resto;

  $soma = 0;
  for ($i = 0; $i < 10; $i++) {
    $soma += $cpf[$i] * (11 - $i);
  }
  $resto = $soma % 11;
  $cpf[10] = ($resto < 2) ? 0 : 11 - $resto;

  return sprintf(
    "%d%d%d.%d%d%d.%d%d%d-%d%d",
    $cpf[0],
    $cpf[1],
    $cpf[2],
    $cpf[3],
    $cpf[4],
    $cpf[5],
    $cpf[6],
    $cpf[7],
    $cpf[8],
    $cpf[9],
    $cpf[10]
  );
}

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
  // Criar avatar para o administrador usando nossa função
  $admin_avatar_name = createUserAvatar('admin');
  $avatar_created = ($admin_avatar_name !== null);

  if ($avatar_created) {
    echo "Avatar criado para o administrador com sucesso.\n";
  } else {
    echo "AVISO: Não foi possível criar avatar para o administrador.\n";
  }

  $admin = new Employee([
    'name' => 'Klesio Nascimento',
    'cpf' => gerarCpfValido(),
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
    'notes' => 'Administrador principal do sistema',
    'avatar_name' => $avatar_created ? $admin_avatar_name : null
  ]);
  $admin->save();

  $adminCredential = new UserCredential([
    'employee_id' => $admin->id,
    'password' => DEFAULT_PASSWORD,
    'password_confirmation' => DEFAULT_PASSWORD
  ]);
  $adminCredential->save();
} else {
  // Verificar se o administrador já tem um avatar, caso não tenha, adicionar um
  if (!$admin->hasValidAvatar()) {
    $admin_avatar_name = 'avatar_admin_' . uniqid() . '.jpg';
    $avatar_added = false;

    // Tentar usar a imagem do administrador primeiro
    if ($adminProfileExists) {
      $result = copyAvatarToUploads(IMAGES_DIR . 'profile_admin.jpg', $admin_avatar_name);
      if ($result) {
        $admin->avatar_name = $admin_avatar_name;
        $admin->save();
        $avatar_added = true;
        echo "Avatar adicionado ao administrador existente usando profile_admin.jpg.\n";
      }
    }
    // Se não conseguiu usar a imagem do administrador, tenta usar a imagem padrão
    elseif ($defaultAvatarExists && !$avatar_added) {
      $result = copyAvatarToUploads(ASSETS_DIR . 'default-avatar.jpg', $admin_avatar_name);
      if ($result) {
        $admin->avatar_name = $admin_avatar_name;
        $admin->save();
        echo "Avatar adicionado ao administrador existente usando a imagem padrão.\n";
      }
    }
  }
}

echo "Criando usuário RH Caio...\n";
$hr = Employee::findByEmail('caio@rh.com');
if (!$hr) {
  $hr = new Employee([
    'name' => 'Caio Silva Santos',
    'cpf' => gerarCpfValido(),
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
    'password' => DEFAULT_PASSWORD,
    'password_confirmation' => DEFAULT_PASSWORD
  ]);
  $hrCredential->save();
}

// Array de usuários para criar
$usuarios = [
  ['name' => 'Ana Beatriz Silva', 'birth_date' => '1992-01-15', 'city' => 'São Paulo', 'state' => 'SP'],
  ['name' => 'Bruno Costa Santos', 'birth_date' => '1988-03-22', 'city' => 'Rio de Janeiro', 'state' => 'RJ'],
  ['name' => 'Carlos Eduardo Lima', 'birth_date' => '1995-07-10', 'city' => 'Belo Horizonte', 'state' => 'MG'],
  ['name' => 'Daniela Oliveira', 'birth_date' => '1990-11-05', 'city' => 'Porto Alegre', 'state' => 'RS'],
  ['name' => 'Eduardo Ferreira', 'birth_date' => '1987-09-18', 'city' => 'Salvador', 'state' => 'BA'],
  ['name' => 'Fernanda Ribeiro', 'birth_date' => '1993-12-28', 'city' => 'Fortaleza', 'state' => 'CE'],
  ['name' => 'Gabriel Souza', 'birth_date' => '1991-04-14', 'city' => 'Recife', 'state' => 'PE'],
  ['name' => 'Helena Martins', 'birth_date' => '1989-06-30', 'city' => 'Manaus', 'state' => 'AM'],
  ['name' => 'Igor Pereira', 'birth_date' => '1994-02-08', 'city' => 'Belém', 'state' => 'PA'],
  ['name' => 'Julia Almeida', 'birth_date' => '1986-10-25', 'city' => 'Goiânia', 'state' => 'GO'],
  ['name' => 'Kevin Rodrigues', 'birth_date' => '1996-05-12', 'city' => 'Vitória', 'state' => 'ES'],
  ['name' => 'Larissa Castro', 'birth_date' => '1988-08-07', 'city' => 'João Pessoa', 'state' => 'PB'],
  ['name' => 'Marcos Vieira', 'birth_date' => '1992-12-19', 'city' => 'Aracaju', 'state' => 'SE'],
  ['name' => 'Natália Gomes', 'birth_date' => '1990-03-04', 'city' => 'Teresina', 'state' => 'PI'],
  ['name' => 'Otávio Barbosa', 'birth_date' => '1985-07-21', 'city' => 'Natal', 'state' => 'RN'],
  ['name' => 'Patrícia Cunha', 'birth_date' => '1993-11-16', 'city' => 'Maceió', 'state' => 'AL'],
  ['name' => 'Rafael Torres', 'birth_date' => '1987-01-29', 'city' => 'Campo Grande', 'state' => 'MS'],
  ['name' => 'Sabrina Dias', 'birth_date' => '1994-09-03', 'city' => 'Cuiabá', 'state' => 'MT'],
  ['name' => 'Thiago Moreira', 'birth_date' => '1991-05-26', 'city' => 'Florianópolis', 'state' => 'SC'],
  ['name' => 'Vanessa Cardoso', 'birth_date' => '1989-04-11', 'city' => 'Brasília', 'state' => 'DF'],
  ['name' => 'William Araújo', 'birth_date' => '1995-08-17', 'city' => 'Palmas', 'state' => 'TO'],
  ['name' => 'Ximena Nogueira', 'birth_date' => '1986-12-02', 'city' => 'Boa Vista', 'state' => 'RR'],
  ['name' => 'Yago Freitas', 'birth_date' => '1992-06-13', 'city' => 'Macapá', 'state' => 'AP'],
  ['name' => 'Zara Mendes', 'birth_date' => '1988-10-08', 'city' => 'Rio Branco', 'state' => 'AC'],
  ['name' => 'André Lopes', 'birth_date' => '1993-02-23', 'city' => 'São Luís', 'state' => 'MA'],
  ['name' => 'Bianca Ramos', 'birth_date' => '1990-07-01', 'city' => 'Curitiba', 'state' => 'PR'],
  ['name' => 'Cláudio Xavier', 'birth_date' => '1987-11-14', 'city' => 'Londrina', 'state' => 'PR'],
  ['name' => 'Débora Fonseca', 'birth_date' => '1994-03-09', 'city' => 'Maringá', 'state' => 'PR']
];

$salarios = [4500.00, 5000.00, 5500.00, 6000.00, 6500.00, 7000.00];
$enderecos = [
  'Rua das Palmeiras',
  'Avenida Brasil',
  'Rua do Comércio',
  'Avenida Paulista',
  'Rua XV de Novembro',
  'Avenida Beira Mar',
  'Rua da Liberdade',
  'Avenida Atlântica'
];

/**
 * Função para ajustar permissões de arquivos e diretórios
 *
 * @param string $path Caminho do arquivo ou diretório
 * @param int $permissions Permissões a serem aplicadas (octal)
 * @return bool true se as permissões foram aplicadas com sucesso
 */
function setPermissions($path, $permissions = 0777)
{
  if (file_exists($path)) {
    echo "Ajustando permissões para $path\n";
    if (chmod($path, $permissions)) {
      echo "Permissões ajustadas com sucesso para $path\n";
      return true;
    } else {
      echo "ERRO: Falha ao ajustar permissões para $path\n";
    }
  }
  return false;
}

/**
 * Função para copiar um arquivo de avatar para o diretório de uploads
 *
 * @param string $sourceFile Caminho completo para o arquivo de origem
 * @param string $destName Nome do arquivo de destino (sem caminho)
 * @return bool true se a cópia foi bem-sucedida
 */
function copyAvatarToUploads($sourceFile, $destName)
{
  $destPath = AVATARS_DIR . $destName;
  $success = false;

  if (file_exists($sourceFile)) {
    echo "Copiando avatar para $destPath\n";
    if (copy($sourceFile, $destPath)) {
      echo "Avatar copiado com sucesso para $destPath\n";
      // Ajustar permissões do arquivo copiado
      setPermissions($destPath);
      $success = true;
    } else {
      echo "ERRO: Falha ao copiar avatar para $destPath\n";
    }
  }

  return $success;
}

/**
 * Função para criar um avatar para um usuário
 *
 * @param string $prefix Prefixo para o nome do arquivo (ex: 'admin', 'user')
 * @return string|null Nome do arquivo criado ou null se falhou
 */
function createUserAvatar($prefix = 'user')
{
  $avatarName = $prefix . '_' . uniqid() . '.jpg';
  $defaultAvatarSource = IMAGES_DIR . DEFAULT_AVATAR;

  // Tentar usar a imagem padrão da pasta images e copiar para o diretório de avatares
  if (file_exists($defaultAvatarSource) && copyAvatarToUploads($defaultAvatarSource, $avatarName)) {
    return $avatarName;
  }

  return null;
}

// Inicialização do ambiente
echo "\n=== Inicializando ambiente ===\n";

// Criar diretórios necessários
createRequiredDirectories();

// Preparar imagem padrão
$defaultAvatarAvailable = prepareDefaultAvatar();

// Verificar se a imagem padrão está disponível
if ($defaultAvatarAvailable) {
  echo "Imagem padrão está pronta para uso.\n";
} else {
  echo "AVISO: Imagem padrão não está disponível. Avatares podem não ser criados corretamente.\n";
}

echo "Criando 28 usuários adicionais...\n";
foreach ($usuarios as $index => $usuario) {
  $email = strtolower(str_replace(' ', '.', $usuario['name'])) . '@user.com';
  $email = str_replace(
    ['ç', 'ã', 'á', 'à', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú'],
    ['c', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u'],
    $email
  );

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

    // Para alguns usuários, adicione a foto de perfil (aproximadamente 1 em cada 3)
    $avatar_name = null;
    if ($index % 3 === 0) {
      // Usar nossa função para criar avatar de usuário
      $avatar_name = createUserAvatar('user');
    }

    $employee = new Employee([
      'name' => $usuario['name'],
      'cpf' => gerarCpfValido(),
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
      'notes' => 'Usuário criado automaticamente para testes',
      'avatar_name' => $avatar_name  // Adicionar nome da imagem de avatar
    ]);
    $employee->save();

    $credential = new UserCredential([
      'employee_id' => $employee->id,
      'password' => DEFAULT_PASSWORD,
      'password_confirmation' => DEFAULT_PASSWORD
    ]);
    $credential->save();

    $avatarMsg = $avatar_name ? " (com foto de perfil)" : "";
    echo "Usuário criado: {$usuario['name']} - {$email}{$avatarMsg}\n";
  }
}

echo "\n=== Ajustando permissões finais ===\n";
setPermissions(UPLOADS_DIR, 0777);
setPermissions(AVATARS_DIR, 0777);

// Ajustar permissões de todos os arquivos no diretório de avatares
if (is_dir(AVATARS_DIR) && $handle = opendir(AVATARS_DIR)) {
  while (false !== ($file = readdir($handle))) {
    if ($file != "." && $file != ".." && is_file(AVATARS_DIR . $file)) {
      setPermissions(AVATARS_DIR . $file, 0777);
    }
  }
  closedir($handle);
}

echo "\n=== RESUMO ===\n";
echo "Total de funcionários criados: 30\n";
echo "Admin: klesio@admin.com (senha: " . DEFAULT_PASSWORD . ")\n";
echo "RH: caio@rh.com (senha: " . DEFAULT_PASSWORD . ")\n";
echo "28 usuários com emails @user.com (senha: " . DEFAULT_PASSWORD . ")\n";
echo "\nDados inseridos com sucesso!\n";

echo "\nNow populating projects and notifications...\n";
require_once __DIR__ . '/populate_projects.php';
