<?php

namespace App\Middleware;

use Core\Http\Middleware\Middleware;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class Authenticate implements Middleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check()) {
            // Configurar uma mensagem Flash antes do redirecionamento
            FlashMessage::danger('Você deve estar logado para acessar essa página');
            $this->redirectTo(route('auth.login'));
        }
    }

    private function redirectTo(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }
}
