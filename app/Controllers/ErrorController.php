<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;

class ErrorController extends Controller
{
    public function notFound(Request $request): void
    {
        $title = 'Página Não Encontrada';
        $this->render('errors/404', compact('title'));
    }
}
