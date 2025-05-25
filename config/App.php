<?php

namespace Config;

class App
{
    public static array $middlewareAliases = [
        'auth' => \App\Middleware\Authenticate::class,
        'admin-hr' => \App\Middleware\AdminOrHRMiddleware::class // Middleware para rotas admin e RH
    ];
}
