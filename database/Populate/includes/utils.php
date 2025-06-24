<?php

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
 * Função para preparar a imagem padrão de avatar
 *
 * @return bool true se a imagem padrão está disponível
 */
function prepareDefaultAvatar()
{
  echo "\n=== Preparando imagem padrão de avatar ===\n";
  
  $defaultAvatarSource = IMAGES_DIR . DEFAULT_AVATAR;
  $defaultAvatarDest = AVATARS_DIR . 'default_' . uniqid() . '.jpg';
  
  // Verificar se a imagem padrão existe
  if (file_exists($defaultAvatarSource)) {
    echo "Imagem padrão encontrada: $defaultAvatarSource\n";
    
    // Copiar para o diretório de avatares
    if (copy($defaultAvatarSource, $defaultAvatarDest)) {
      echo "Imagem padrão copiada para: $defaultAvatarDest\n";
      // Ajustar permissões
      chmod($defaultAvatarDest, 0777);
      return true;
    } else {
      echo "ERRO: Falha ao copiar imagem padrão para o diretório de avatares.\n";
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

/**
 * Função para ajustar permissões de todos os arquivos no diretório de avatares
 */
function adjustAllAvatarPermissions()
{
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
}
