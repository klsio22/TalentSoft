<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;

class ErrorController extends Controller
{
    protected string $layout = 'application';

    /**
     * Exibe página para erro 404
     */
    public function notFound(): void
    {
        http_response_code(404);
        $title = 'Página não encontrada';
        $this->render('errors/not_found', compact('title'));
    }

    /**
     * Exibe página para erro 500
     */
    public function serverError(): void
    {
        http_response_code(500);
        $title = 'Erro interno do servidor';
        $this->render('errors/server_error', compact('title'));
    }
}
