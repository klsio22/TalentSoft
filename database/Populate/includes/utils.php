<?php

/**
 * Função para criar diretórios necessários
 */
function createRequiredDirectories()
{
  echo "\n=== Preparando diretórios ===\n";

  // Criar diretório de uploads
  if (!file_exists(UPLOADS_DIR)) {
    echo "Criando diretório de uploads: " . UPLOADS_DIR . "\n";
    if (mkdir(UPLOADS_DIR, 0777, true)) {
      echo "Diretório de uploads criado com sucesso!\n";
    } else {
      echo "ERRO: Falha ao criar diretório de uploads.\n";
    }
  } else {
    echo "Diretório de uploads já existe.\n";
    // Garantir que as permissões estão corretas mesmo se o diretório já existir
    chmod(UPLOADS_DIR, 0777);
  }
  
  // Verificar diretório de imagens
  if (!file_exists(IMAGES_DIR)) {
    echo "AVISO: Diretório de imagens não existe: " . IMAGES_DIR . "\n";
  } else {
    echo "Diretório de imagens existe.\n";
  }
}

/**
 * Função para preparar a imagem padrão de avatar
 *
 * @return bool true se a imagem padrão está disponível
 */
function prepareDefaultAvatar()
{
  echo "\n=== Preparando imagem padrão de avatar ===\n";
  
  $defaultAvatarSource = IMAGES_DIR . DEFAULT_AVATAR;
  $defaultAvatarDest = UPLOADS_DIR . 'default_' . uniqid() . '.png';
  
  // Verificar se a imagem padrão existe
  if (file_exists($defaultAvatarSource)) {
    echo "Imagem padrão encontrada: $defaultAvatarSource\n";
    
    // Copiar para o diretório de uploads
    if (copy($defaultAvatarSource, $defaultAvatarDest)) {
      echo "Imagem padrão copiada para: $defaultAvatarDest\n";
      // Ajustar permissões
      chmod($defaultAvatarDest, 0777);
      return true;
    } else {
      echo "ERRO: Falha ao copiar imagem padrão para o diretório de uploads.\n";
    }
  } else {
    echo "ERRO: Imagem padrão não encontrada em: $defaultAvatarSource\n";
  }
  
  return false;
}

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
  $destPath = UPLOADS_DIR . $destName;
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
  $avatarName = $prefix . '_' . uniqid() . '.png';
  $defaultAvatarSource = IMAGES_DIR . DEFAULT_AVATAR;
  $destPath = UPLOADS_DIR . $avatarName;

  // Tentar usar a imagem padrão da pasta images e copiar para o diretório de uploads
  if (file_exists($defaultAvatarSource) && copy($defaultAvatarSource, $destPath)) {
    chmod($destPath, 0777);
    echo "Avatar criado com sucesso: $destPath\n";
    return $avatarName;
  }

  echo "ERRO: Falha ao criar avatar para $prefix\n";
  return null;
}

/**
 * Função para ajustar permissões de todos os arquivos no diretório de uploads
 */
function adjustAllAvatarPermissions()
{
  echo "\n=== Ajustando permissões finais ===\n";
  setPermissions(UPLOADS_DIR, 0777);

  // Ajustar permissões de todos os arquivos no diretório de uploads
  if (is_dir(UPLOADS_DIR) && $handle = opendir(UPLOADS_DIR)) {
    while (false !== ($file = readdir($handle))) {
      if ($file != "." && $file != ".." && is_file(UPLOADS_DIR . $file)) {
        setPermissions(UPLOADS_DIR . $file, 0777);
      }
    }
    closedir($handle);
  }
}
