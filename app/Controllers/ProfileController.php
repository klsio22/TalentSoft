<?php

namespace App\Controllers;

use App\Models\ProfileImage;
use Lib\Authentication\Auth;
use Lib\FlashMessage;
use Core\Http\Controllers\Controller;

class ProfileController extends Controller
{
    protected string $layout = 'application';
    private const VIEW_PROFILE = 'profile/show';
    private const TITLE_PROFILE = 'Meu Perfil';

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

  /**
   * Handle avatar upload process
   */
    public function uploadAvatar(): void
    {
        $user = Auth::user();

        if (!$this->validateUserAccess($user)) {
            return;
        }

        $debug = $this->collectDebugInfo();
        $this->processAvatarUpload($user, $debug);

        $this->redirectTo(route('profile.show'));
    }

  /**
   * Validate if user is authenticated and has permission
   *
   * @param mixed $user The user object
   * @return bool True if user has access, false otherwise
   */
    private function validateUserAccess($user): bool
    {
        if (!$user) {
            $this->redirectTo(route('auth.login'));
            return false;
        }

        if (!$this->canUploadAvatar()) {
            FlashMessage::danger('Você não tem permissão para fazer upload de imagens.');
            $this->redirectTo(route('profile.show'));
            return false;
        }

        return true;
    }

  /**
   * Collect debug information about the upload request
   *
   * @return array Debug information
   */
    private function collectDebugInfo(): array
    {
        $debug = [];
        $debug['request_method'] = $_SERVER['REQUEST_METHOD'];
        $debug['files'] = isset($_FILES) ? 'Files array exists' : 'No files array';
        $debug['avatar'] = isset($_FILES['avatar']) ? 'Avatar key exists' : 'No avatar key';

        if (isset($_FILES['avatar'])) {
            $debug['avatar_error'] = $_FILES['avatar']['error'];
            $debug['avatar_name'] = $_FILES['avatar']['name'];
            $debug['avatar_size'] = $_FILES['avatar']['size'];
            $debug['avatar_tmp_name'] = $_FILES['avatar']['tmp_name'];
        }

        return $debug;
    }

  /**
   * Process the avatar upload
   *
   * @param mixed $user The user object
   * @param array $debug Debug information
   */
    private function processAvatarUpload($user, array &$debug): void
    {
      // No file uploaded
        if (!isset($_FILES['avatar'])) {
            $this->handleNoFileUploaded();
            return;
        }

        $uploadError = $_FILES['avatar']['error'];

      // Successful upload
        if ($uploadError === UPLOAD_ERR_OK) {
            $this->handleSuccessfulUpload($user, $debug);
            return;
        }

      // Upload error but file was selected
        if ($uploadError !== UPLOAD_ERR_NO_FILE) {
            $this->handleUploadError($uploadError);
            return;
        }

      // POST request but no file selected
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            FlashMessage::danger('Nenhum arquivo foi enviado.');
        }
    }

  /**
   * Handle when no file was uploaded
   */
    private function handleNoFileUploaded(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            FlashMessage::danger('Nenhum arquivo foi enviado.');
        }
    }

  /**
   * Handle successful file upload
   *
   * @param mixed $user The user object
   * @param array $debug Debug information
   */
    private function handleSuccessfulUpload($user, array &$debug): void
    {
        $profileImage = new ProfileImage();
        $fileInfo = $profileImage->validateImage($_FILES['avatar']);

        $debug['validation'] = empty($fileInfo['errors']) ? 'Validation passed' : 'Validation failed';
        $debug['validation_errors'] = $fileInfo['errors'] ?? [];

        if (!empty($fileInfo['errors'])) {
            $this->displayErrors($fileInfo['errors']);
            return;
        }

        $result = $profileImage->processImageUpload($user, $_FILES['avatar']);
        $debug['upload_errors'] = $result['errors'] ?? [];

        if (!empty($result['errors'])) {
            $this->displayErrors($result['errors']);
        } else {
            FlashMessage::success('Sua foto de perfil foi atualizada com sucesso.');
        }
    }

  /**
   * Handle upload errors
   *
   * @param int $errorCode PHP upload error code
   */
    private function handleUploadError(int $errorCode): void
    {
        $profileImage = new ProfileImage();
        FlashMessage::danger($profileImage->getUploadErrorMessage($errorCode));
    }

  /**
   * Display multiple error messages
   *
   * @param array $errors Array of error messages
   */
    private function displayErrors(array $errors): void
    {
        foreach ($errors as $error) {
            FlashMessage::danger($error);
        }
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirectTo(route('auth.login'));
            return;
        }

        $profileImage = new ProfileImage();
        $result = $profileImage->processAvatarRemoval($user);

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
}
