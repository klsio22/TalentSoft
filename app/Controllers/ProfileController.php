<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class ProfileController extends Controller
{
    protected string $layout = 'application';
  // Define allowed image types and max size (2MB)
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    private const MAX_SIZE = 2097152; // 2MB in bytes
    private const UPLOAD_DIR = 'public/uploads/avatars/';
    private const VIEW_PROFILE = 'profile/show';
    private const TITLE_PROFILE = 'Meu Perfil';
    private const ERROR_USER_UPDATE = 'Não foi possível atualizar o registro do usuário.';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        }
    }

  /**
   * Verifica se o usuário tem permissão para fazer upload de avatar
   *
   * @return bool True se o usuário tem permissão, False caso contrário
   */
    private function canUploadAvatar(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

      // Implemente aqui sua lógica de permissão
      // Por exemplo, apenas administradores ou usuários com papel específico podem fazer upload
      // return $user->hasRole('admin') || $user->hasPermission('upload_avatar');

      // Por enquanto, qualquer usuário autenticado pode fazer upload
        return true;
    }

    public function show(): void
    {
        $user = Auth::user();

        $this->render(self::VIEW_PROFILE, [
            'title' => self::TITLE_PROFILE,
            'user' => $user
        ]);
    }

    public function uploadAvatar(): void
    {
        $user = Auth::user();
        $debug = [];

        if (!$user) {
            $this->redirectTo(route('auth.login'));
            return;
        }

      // Verifica se o usuário tem permissão para fazer upload
        if (!$this->canUploadAvatar()) {
            FlashMessage::danger('Você não tem permissão para fazer upload de imagens.');
            $this->redirectTo(route('profile.show'));
            return;
        }

        $fileInfo = null;

      // Add debug information
        $debug['request_method'] = $_SERVER['REQUEST_METHOD'];
        $debug['files'] = isset($_FILES) ? 'Files array exists' : 'No files array';
        $debug['avatar'] = isset($_FILES['avatar']) ? 'Avatar key exists' : 'No avatar key';

        if (isset($_FILES['avatar'])) {
            $debug['avatar_error'] = $_FILES['avatar']['error'];
            $debug['avatar_name'] = $_FILES['avatar']['name'];
            $debug['avatar_size'] = $_FILES['avatar']['size'];
            $debug['avatar_tmp_name'] = $_FILES['avatar']['tmp_name'];
        }

      // Check if file was uploaded
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileInfo = $this->validateImage($_FILES['avatar']);
            $debug['validation'] = empty($fileInfo['errors']) ? 'Validation passed' : 'Validation failed';
            $debug['validation_errors'] = $fileInfo['errors'];

            if (!empty($fileInfo['errors'])) {
                foreach ($fileInfo['errors'] as $error) {
                    FlashMessage::danger($error);
                }
            } else {
              // Process the upload
                $result = $this->processImageUpload($user, $_FILES['avatar']);
                $debug['upload_errors'] = $result['errors'] ?? [];

                if (!empty($result['errors'])) {
                    foreach ($result['errors'] as $error) {
                        FlashMessage::danger($error);
                    }
                } else {
                    FlashMessage::success('Sua foto de perfil foi atualizada com sucesso.');
                }
            }
        } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
          // Handle upload errors
            FlashMessage::danger($this->getUploadErrorMessage($_FILES['avatar']['error']));
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            FlashMessage::danger('Nenhum arquivo foi enviado.');
        }

        $this->redirectTo(route('profile.show'));
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirectTo(route('auth.login'));
            return;
        }

        $result = $this->processAvatarRemoval($user);

        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $error) {
                FlashMessage::danger($error);
            }
        }

        if ($result['success']) {
            FlashMessage::success('Sua foto de perfil foi removida com sucesso.');
        }

        $this->redirectTo(route('profile.show'));
    }

  /**
   * Process the avatar removal logic
   *
   * @param object $user The user object
   * @return array Result with success status and any errors
   */
    private function processAvatarRemoval($user): array
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
   * Handle the avatar file deletion and database update
   *
   * @param bool $fileExists Whether the file exists
   * @param string $avatarPath Full path to the avatar file
   * @param object $user User object
   * @param array &$result Result array to update with errors
   * @return bool Success status
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
            $result['errors'][] = self::ERROR_USER_UPDATE;
            return false;
        }

        return true;
    }

    private function validateImage(array $file): array
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

    private function processImageUpload($user, array $file): array
    {
        $result = [
        'errors' => [],
        'debug' => []
        ];

      // Create upload directory if it doesn't exist
        $uploadDir = dirname(__DIR__, 2) . '/' . self::UPLOAD_DIR;
        $result['debug']['upload_dir'] = $uploadDir;
        $result['debug']['dir_exists'] = file_exists($uploadDir) ? 'Yes' : 'No';

      // Garantir que o diretório de upload existe
        if (!file_exists($uploadDir)) {
          // Tenta criar o diretório com permissões mais amplas
            $mkdirResult = @mkdir($uploadDir, 0777, true);
            $result['debug']['mkdir_result'] = $mkdirResult ? 'Success' : 'Failed';

            if (!$mkdirResult) {
                $error = error_get_last();
                $result['errors'][] = 'Não foi possível criar o diretório de upload. Verifique as permissões.';
                $result['debug']['mkdir_error'] = $error;
                return $result;
            }

          // Garante que as permissões estão corretas após a criação
            @chmod($uploadDir, 0777);
        }

      // Generate a unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = 'avatar_' . $user->id . '_' . uniqid() . '.' . $extension;
        $uploadPath = $uploadDir . $newFilename;
        $result['debug']['upload_path'] = $uploadPath;

      // If user already has an avatar, delete the old one
        if ($user->avatar_name && file_exists($uploadDir . $user->avatar_name)) {
            $unlinkResult = unlink($uploadDir . $user->avatar_name);
            $result['debug']['unlink_old_avatar'] = $unlinkResult ? 'Success' : 'Failed';
        }

      // Move the uploaded file
        $moveResult = move_uploaded_file($file['tmp_name'], $uploadPath);
        $result['debug']['move_uploaded_file'] = $moveResult ? 'Success' : 'Failed';

        if ($moveResult) {
          // Update user record
            $user->avatar_name = $newFilename;
            $saveResult = $user->save();
            $result['debug']['user_save'] = $saveResult ? 'Success' : 'Failed';

            if (!$saveResult) {
                $result['errors'][] = self::ERROR_USER_UPDATE;
              // Delete the uploaded file if we couldn't update the user record
                if (file_exists($uploadPath)) {
                    unlink($uploadPath);
                    $result['debug']['cleanup_unlink'] = 'File removed after failed save';
                }
            }
        } else {
            $result['errors'][] = 'Não foi possível fazer o upload do arquivo.';
            $result['debug']['move_error'] = error_get_last();
        }

        return $result;
    }

    private function getUploadErrorMessage(int $errorCode): string
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
}
