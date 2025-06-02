<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Lib\Authentication\Auth;

class ProfileController extends Controller
{
    protected string $layout = 'application';

    public function __construct()
    {
        parent::__construct();

        if (!Auth::check()) {
            $this->redirectTo(route('auth.login'));
        }
    }

    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            $this->redirectTo(route('auth.login'));
            return;
        }

        $this->render('profile/show', [
            'title' => 'Meu Perfil',
            'user' => $user
        ]);
    }
}
