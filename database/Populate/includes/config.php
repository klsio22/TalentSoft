<?php

/**
 * Configurações de ambiente e constantes para o script de população de dados
 *
 * Este arquivo carrega as variáveis de ambiente do Docker que são passadas
 * através do arquivo .env e do script run
 */

// Função para obter variáveis de ambiente com valor padrão
function getEnvironmentVariable($name, $default = null) {
    // Tenta obter do ambiente primeiro
    $value = getenv($name);
    
    // Se não encontrou ou está vazio, usa o valor padrão
    if ($value === false || $value === '') {
        $value = $default;
        
        // Define a variável de ambiente para uso posterior
        if ($value !== null) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
    
    return $value;
}

// Configura variáveis de ambiente para conexão com o banco de dados
// Usa os valores do Docker que são passados através do arquivo .env
$dbHost = getEnvironmentVariable('DB_HOST', 'db');
$dbPort = getEnvironmentVariable('DB_PORT', '3306');
$dbName = getEnvironmentVariable('DB_DATABASE', 'talent_soft_development');
$dbUser = getEnvironmentVariable('DB_USERNAME', 'talent-soft');
$dbPass = getEnvironmentVariable('DB_PASSWORD', 'talent-soft');

// Constantes para configuração do banco de dados
define('DB_HOST', $dbHost);
define('DB_PORT', $dbPort);
define('DB_DATABASE', $dbName);
define('DB_USERNAME', $dbUser);
define('DB_PASSWORD', $dbPass);

// Constantes para o sistema
define('DEFAULT_PASSWORD', '123456');
define('ROOT_DIR', dirname(__DIR__, 3));
define('UPLOADS_DIR', ROOT_DIR . '/public/uploads/');
define('AVATARS_DIR', UPLOADS_DIR . 'avatars/');
define('ASSETS_DIR', ROOT_DIR . '/public/assets/');
define('IMAGES_DIR', ASSETS_DIR . 'images/defaults/');
define('DEFAULT_AVATAR', 'default-avatar.jpg');
