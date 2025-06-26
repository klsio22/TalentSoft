<?php

namespace App\Controllers;

use App\Services\ProfileAvatar;
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

    public function show(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirectTo(route('auth.login'));
            return;
        }

        $this->render(self::VIEW_PROFILE, [
        'title' => self::TITLE_PROFILE,
        'user' => $user
        ]);
    }

  /**
   * Upload and update user avatar
   */
    public function uploadAvatar(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirectTo(route('auth.login'));
            return;
        }

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            FlashMessage::danger('Erro ao enviar arquivo. Por favor, tente novamente.');
            $this->redirectTo(route('profile.show'));
            return;
        }

        $profileAvatar = new ProfileAvatar($user);
        try {
            $result = $profileAvatar->update($_FILES['avatar']);

            // Verificar se a atualização realmente ocorreu
            if ($result === true) {
                FlashMessage::success('Sua foto de perfil foi atualizada com sucesso.');
            } else {
                FlashMessage::danger('Erro ao atualizar foto de perfil.');
            }
        } catch (\Exception $e) {
            FlashMessage::danger('Erro ao processar imagem: ' . $e->getMessage());
        }

        $this->redirectTo(route('profile.show'));
    }

  /**
   * Remove user avatar
   */
    public function removeAvatar(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirectTo(route('auth.login'));
            return;
        }

        $profileAvatar = new ProfileAvatar($user);
        $result = $profileAvatar->remove();

        if ($result) {
            FlashMessage::success('Sua foto de perfil foi removida com sucesso.');
        } else {
            FlashMessage::danger('Erro ao remover foto de perfil.');
        }

        $this->redirectTo(route('profile.show'));
    }
}
