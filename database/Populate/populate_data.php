<?php

// Carregar autoloader
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Importar classes necessárias
use App\Models\Role;
use Core\Database\Database;

// Carregar arquivos de configuração e funções
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/generators.php';
require_once __DIR__ . '/includes/sample_data.php';

// Inicialização do ambiente
echo "\n=== Inicializando ambiente ===\n";

// Executar migração do banco de dados
Database::migrate();

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

// Buscar roles
$adminRole = Role::findByName('admin');
$hrRole = Role::findByName('hr');
$userRole = Role::findByName('user');

// Criar usuário administrador
createAdminUser($adminRole->id);

// Criar usuário de RH
createHRUser($hrRole->id);

// Criar usuários regulares
echo "\nCriando 28 usuários adicionais...\n";
createRegularUsers($usuarios, $userRole->id, $enderecos, $salarios);

// Ajustar permissões de todos os arquivos no diretório de avatares
adjustAllAvatarPermissions();

// Exibir resumo
echo "\n=== RESUMO ===\n";
echo "Total de funcionários criados: 30\n";
echo "Admin: klesio@admin.com (senha: " . DEFAULT_PASSWORD . ")\n";
echo "RH: caio@rh.com (senha: " . DEFAULT_PASSWORD . ")\n";
echo "28 usuários com emails @user.com (senha: " . DEFAULT_PASSWORD . ")\n";
echo "\nDados inseridos com sucesso!\n";

// Carregar adaptador e script para popular projetos e notificações
require_once __DIR__ . '/includes/projects_adapter.php';
echo "\nPopulando projetos e notificações...\n";
populateProjectsAndNotifications();
