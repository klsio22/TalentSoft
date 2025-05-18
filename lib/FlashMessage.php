<?php

namespace Lib;

class FlashMessage
{
    /**
     * Adiciona uma mensagem de sucesso
     */
    public static function success(string $value): void
    {
        self::message('success', $value);
    }

    /**
     * Adiciona uma mensagem de erro
     */
    public static function danger(string $value): void
    {
        self::message('danger', $value);
    }

    /**
     * Adiciona uma mensagem de alerta
     */
    public static function warning(string $value): void
    {
        self::message('warning', $value);
    }

    /**
     * Adiciona uma mensagem informativa
     */
    public static function info(string $value): void
    {
        self::message('info', $value);
    }

    /**
     * Retorna todas as mensagens flash e limpa a sessão
     */
    public static function get(): array
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        return $flash;
    }

    /**
     * Verifica se existem mensagens flash
     */
    public static function hasMessages(): bool
    {
        return !empty($_SESSION['flash']);
    }

    /**
     * Método privado para adicionar uma mensagem na sessão
     */
    private static function message(string $type, string $value): void
    {
        $_SESSION['flash'][$type] = $value;
    }
}
