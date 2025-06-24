<?php

namespace App\Models;

/**
 * Classe responsável por gerenciar imagens de perfil
 */
class ProfileImage
{
  // Define allowed image types and max size (2MB)
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    private const MAX_SIZE = 2097152; // 2MB in bytes
    private const UPLOAD_DIR = 'public/uploads/avatars/';
    private const DEFAULT_AVATAR = '/assets/images/defaults/default-avatar.jpg';

  /**
   * Valida uma imagem enviada
   *
   * @param array $file Array do arquivo enviado ($_FILES['avatar'])
   * @return array Resultado da validação com possíveis erros
   */
    public function validateImage(array $file): array
    {
        $result = [
        'errors' => []
        ];

      // Check file size
        if ($file['size'] > self::MAX_SIZE) {
            $result['errors'][] = 'O arquivo é muito grande. O tamanho máximo permitido é 2MB.';
        }

      // Check file type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::ALLOWED_TYPES)) {
            $result['errors'][] = 'Tipo de arquivo inválido. Apenas JPEG, PNG e GIF são permitidos.';
        }

        return $result;
    }

  /**
   * Processa o upload de uma imagem de perfil
   *
   * @param object $user Objeto do usuário/funcionário
   * @param array $file Array do arquivo enviado ($_FILES['avatar'])
   * @return array Resultado do processamento com possíveis erros
   */
    public function processImageUpload($user, array $file): array
    {
        $result = [
        'errors' => [],
        'debug' => []
        ];

      // Preparar diretório de upload
        $uploadDir = $this->prepareUploadDirectory($result);
        if (!empty($result['errors'])) {
            return $result;
        }

      // Gerar nome de arquivo único
        $newFilename = $this->generateUniqueFilename($user, $file);
        $uploadPath = $uploadDir . $newFilename;
        $result['debug']['upload_path'] = $uploadPath;

      // Remover avatar antigo se existir
        $this->removeExistingAvatar($user, $uploadDir, $result);

      // Fazer upload do novo arquivo
        if (!$this->moveUploadedFile($file, $uploadPath, $result)) {
            return $result;
        }

      // Atualizar registro do usuário
        $this->updateUserAvatar($user, $newFilename, $uploadPath, $result);

        return $result;
    }

  /**
   * Prepara o diretório de upload, criando-o se necessário
   *
   * @param array &$result Array de resultado para debug e erros
   * @return string Caminho do diretório de upload
   */
    private function prepareUploadDirectory(array &$result): string
    {
        $uploadDir = dirname(__DIR__, 2) . '/' . self::UPLOAD_DIR;
        $result['debug']['upload_dir'] = $uploadDir;
        $result['debug']['dir_exists'] = file_exists($uploadDir) ? 'Yes' : 'No';

        if (file_exists($uploadDir)) {
            return $uploadDir;
        }

      // Tenta criar o diretório com permissões mais amplas
        $mkdirResult = @mkdir($uploadDir, 0777, true);
        $result['debug']['mkdir_result'] = $mkdirResult ? 'Success' : 'Failed';

        if (!$mkdirResult) {
            $error = error_get_last();
            $result['errors'][] = 'Não foi possível criar o diretório de upload. Verifique as permissões.';
            $result['debug']['mkdir_error'] = $error;
            return $uploadDir;
        }

      // Garante que as permissões estão corretas após a criação
        @chmod($uploadDir, 0777);
        return $uploadDir;
    }

  /**
   * Gera um nome de arquivo único para o avatar
   *
   * @param object $user Objeto do usuário
   * @param array $file Array do arquivo enviado
   * @return string Nome de arquivo único
   */
    private function generateUniqueFilename($user, array $file): string
    {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Get the user's role prefix
        $rolePrefix = 'user';
        if ($user->isAdmin()) {
            $rolePrefix = 'admin';
        } elseif ($user->isHR()) {
            $rolePrefix = 'hr';
        }

        // Generate filename in the format: role_uniqueid.extension
        return $rolePrefix . '_' . uniqid() . '.' . $extension;
    }

  /**
   * Remove o avatar existente do usuário se houver
   *
   * @param object $user Objeto do usuário
   * @param string $uploadDir Diretório de upload
   * @param array &$result Array de resultado para debug
   */
    private function removeExistingAvatar($user, string $uploadDir, array &$result): void
    {
        if ($user->avatar_name && file_exists($uploadDir . $user->avatar_name)) {
            $unlinkResult = unlink($uploadDir . $user->avatar_name);
            $result['debug']['unlink_old_avatar'] = $unlinkResult ? 'Success' : 'Failed';
        }
    }

  /**
   * Move o arquivo enviado para o destino final
   *
   * @param array $file Array do arquivo enviado
   * @param string $uploadPath Caminho completo de destino
   * @param array &$result Array de resultado para debug e erros
   * @return bool Sucesso ou falha
   */
    private function moveUploadedFile(array $file, string $uploadPath, array &$result): bool
    {
        $moveResult = move_uploaded_file($file['tmp_name'], $uploadPath);
        $result['debug']['move_uploaded_file'] = $moveResult ? 'Success' : 'Failed';

        if (!$moveResult) {
            $result['errors'][] = 'Não foi possível fazer o upload do arquivo.';
            $result['debug']['move_error'] = error_get_last();
        }

        return $moveResult;
    }

  /**
   * Atualiza o registro do usuário com o novo nome do avatar
   *
   * @param object $user Objeto do usuário
   * @param string $newFilename Novo nome de arquivo
   * @param string $uploadPath Caminho completo do arquivo
   * @param array &$result Array de resultado para debug e erros
   */
    private function updateUserAvatar($user, string $newFilename, string $uploadPath, array &$result): void
    {
        $user->avatar_name = $newFilename;
        $saveResult = $user->save();
        $result['debug']['user_save'] = $saveResult ? 'Success' : 'Failed';

        if (!$saveResult) {
            $result['errors'][] = 'Não foi possível atualizar o registro do usuário.';
            $this->cleanupFailedUpload($uploadPath, $result);
        }
    }

  /**
   * Remove o arquivo enviado em caso de falha ao salvar no banco de dados
   *
   * @param string $uploadPath Caminho completo do arquivo
   * @param array &$result Array de resultado para debug
   */
    private function cleanupFailedUpload(string $uploadPath, array &$result): void
    {
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
            $result['debug']['cleanup_unlink'] = 'File removed after failed save';
        }
    }

  /**
   * Processa a remoção do avatar de um usuário
   *
   * @param object $user Objeto do usuário
   * @return array Resultado com status de sucesso e possíveis erros
   */
    public function processAvatarRemoval($user): array
    {
        $result = [
        'success' => false,
        'errors' => []
        ];

      // Check if user has an avatar to remove
        if (!$user->avatar_name) {
            $result['errors'][] = 'Você não possui uma imagem de perfil para remover.';
            return $result;
        }

        $uploadDir = dirname(__DIR__, 2) . '/' . self::UPLOAD_DIR;
        $avatarPath = $uploadDir . $user->avatar_name;
        $fileExists = file_exists($avatarPath);

      // Handle file deletion and database update
        if ($this->handleAvatarDeletion($fileExists, $avatarPath, $user, $result)) {
            $result['success'] = true;
        }

        return $result;
    }

  /**
   * Gerencia a exclusão do arquivo de avatar e atualização do banco de dados
   *
   * @param bool $fileExists Se o arquivo existe
   * @param string $avatarPath Caminho completo do arquivo
   * @param object $user Objeto do usuário
   * @param array &$result Array de resultado para atualizar com erros
   * @return bool Status de sucesso
   */
    private function handleAvatarDeletion(bool $fileExists, string $avatarPath, $user, array &$result): bool
    {
      // If file exists, try to delete it
        if ($fileExists && !unlink($avatarPath)) {
            $result['errors'][] = 'Não foi possível remover o arquivo de imagem.';
            return false;
        }

      // Update user record regardless of whether file existed
        $user->avatar_name = null;

        if (!$user->save()) {
            $result['errors'][] = 'Não foi possível atualizar o registro do usuário.';
            return false;
        }

        return true;
    }

  /**
   * Retorna a mensagem de erro para códigos de erro de upload
   *
   * @param int $errorCode Código de erro do upload
   * @return string Mensagem de erro correspondente
   */
    public function getUploadErrorMessage(int $errorCode): string
    {
        $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'O arquivo é muito grande.',
        UPLOAD_ERR_FORM_SIZE => 'O arquivo é muito grande.',
        UPLOAD_ERR_PARTIAL => 'O arquivo foi apenas parcialmente carregado.',
        UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
        UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente.',
        UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo em disco.',
        UPLOAD_ERR_EXTENSION => 'Uma extensão PHP interrompeu o upload do arquivo.'
        ];

        return $errorMessages[$errorCode] ?? 'Erro desconhecido no upload.';
    }

  /**
   * Verifica se um usuário tem um avatar válido
   *
   * @param object $user Objeto do usuário
   * @return bool True se o avatar existe, false caso contrário
   */
    public function hasValidAvatar($user): bool
    {
        return $user->avatar_name !== null;
    }

  /**
   * Retorna a URL do avatar do usuário
   *
   * @param object $user Objeto do usuário
   * @return string URL do avatar ou imagem padrão
   */
    public function getAvatarUrl($user): string
    {
        if ($this->hasValidAvatar($user)) {
            return '/uploads/avatars/' . htmlspecialchars($user->avatar_name);
        }

      // Retorna uma imagem padrão caso nenhum avatar esteja definido
        return self::DEFAULT_AVATAR;
    }
}
