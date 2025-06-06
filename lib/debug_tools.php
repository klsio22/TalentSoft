<?php

/**
 * Arquivo de inicialização para as configurações de debug
 *
 * Este arquivo contém apenas configurações e não define funções,
 * atendendo ao PSR-1 que exige separação entre definições de símbolos e efeitos colaterais.
 */

// Incluir as definições de funções de debug
require_once __DIR__ . '/debug_functions.php';

// Registrar manipulador personalizado de erros
set_error_handler('debug_error_handler');

// Garantir que erros nunca serão exibidos na tela, apenas registrados nos logs
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Definir o diretório de logs e criar se não existir
$logDir = __DIR__ . '/../log';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Configurar o arquivo de log de erros
ini_set('error_log', $logDir . '/php_errors.log');
