<?php

namespace App\Controllers;

use App\Models\Employee;
use App\Models\ProfileImage;
use Lib\Authentication\Auth;
use Lib\FlashMessage;
use Core\Http\Controllers\Controller;
use Core\Request;
use Core\Response;
use Core\Session;

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
            $profileImage = new ProfileImage();
            $fileInfo = $profileImage->validateImage($_FILES['avatar']);
            $debug['validation'] = empty($fileInfo['errors']) ? 'Validation passed' : 'Validation failed';
            $debug['validation_errors'] = $fileInfo['errors'];

            if (!empty($fileInfo['errors'])) {
                foreach ($fileInfo['errors'] as $error) {
                    FlashMessage::danger($error);
                }
            } else {
              // Process the upload usando o modelo ProfileImage
                $profileImage = new ProfileImage();
                $result = $profileImage->processImageUpload($user, $_FILES['avatar']);
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
          // Handle upload errors usando o modelo ProfileImage
            $profileImage = new ProfileImage();
            FlashMessage::danger($profileImage->getUploadErrorMessage($_FILES['avatar']['error']));
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
