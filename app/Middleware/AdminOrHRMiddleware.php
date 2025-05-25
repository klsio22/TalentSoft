<?php

namespace App\Middleware;

use Core\Http\Middleware\Middleware;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class AdminOrHRMiddleware implements Middleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            // Configurar uma mensagem Flash antes do redirecionamento
            FlashMessage::danger('Você deve estar logado para acessar essa página');
            $this->redirectTo(route('auth.login'));
        }

        // Verificar se o usuário é um administrador ou RH
        $user = Auth::user();
        if ($user && ($user->isAdmin() || $user->isHR())) {
            return; // Acesso permitido
        }

        // Acesso negado
        FlashMessage::danger('Você não tem permissão para acessar essa página');
        $this->redirectTo(route('root'));
    }

    private function redirectTo(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }
}
