<?php

namespace App\Middleware;

use Core\Http\Middleware\Middleware;
use Core\Http\Request;
use Lib\Authentication\Auth;
use Lib\FlashMessage;

class AdminMiddleware implements Middleware
{
    public function handle(Request $request): void
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            FlashMessage::danger('Você não tem permissão para acessar essa página');
            $this->redirectTo(route('home'));
        }
    }


    private function redirectTo(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }
}
