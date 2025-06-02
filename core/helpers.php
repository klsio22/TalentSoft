<?php

use Core\Debug\Debugger;
use Core\Router\Router;

if (!function_exists('dd')) {
    function dd(): void
    {
        Debugger::dd(...func_get_args());
    }
}

if (!function_exists('route')) {
    /**
     * @param string $name
     * @param mixed[] $params
     * @return string
     */
    function route(string $name, $params = []): string
    {
        // Se estamos em um ambiente de teste e existe um mock para esta função, usar o mock
        if (isset($GLOBALS['__function_mock_route'])) {
            return call_user_func($GLOBALS['__function_mock_route'], $name, $params);
        }

        return Router::getInstance()->getRoutePathByName($name, $params);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Gera ou retorna um token CSRF para proteção de formulários
     * @return string
     */
    function csrf_token(): string
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_check')) {
    /**
     * Verifica se o token CSRF enviado é válido
     * @param string $token Token enviado pelo formulário
     * @return bool
     */
    function csrf_check(string $token): bool
    {
        // Desabilitar CSRF em ambiente de teste
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'testing') {
            return true;
        }

        if (!isset($_SESSION)) {
            session_start();
        }

        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
