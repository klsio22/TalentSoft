<?php

/**
 * Modifique este arquivo para registrar informações detalhadas sobre erros ao criar funcionários
 * Coloque no início do método store() do EmployeesController.php
 */

function debug_error_handler($errno, $errstr, $errfile, $errline)
{
    $error_log = '/var/www/log/debug-errors.log';
    $message = date('[Y-m-d H:i:s]') . " Error $errno: $errstr in $errfile on line $errline\n";

    // Registrar também a pilha de chamadas
    $trace = debug_backtrace();
    $message .= "Backtrace:\n";
    foreach ($trace as $i => $step) {
        $file = $step['file'] ?? 'unknown file';
        $line = $step['line'] ?? 'unknown line';
        $function = $step['function'] ?? 'unknown function';
        $class = $step['class'] ?? '';
        $type = $step['type'] ?? '';
        $message .= "#$i $file($line): ";
        if ($class) {
            $message .= "$class$type";
        }
        $message .= "$function()\n";
    }

    file_put_contents($error_log, $message, FILE_APPEND);
    return true;
}

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

// Função para depurar variáveis (apenas em log, nunca na tela)
function debug_var($var, $label = null)
{
    ob_start();
    echo "\n\n----- DEBUG ";
    if ($label) {
        echo "[$label] ";
    }
    echo "-----\n";
    var_dump($var);
    echo "\n----- END DEBUG -----\n\n";
    $output = ob_get_clean();

    // Diretório de logs local
    $logDir = __DIR__ . '/../log';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    // Usar um arquivo local que pode ser facilmente acessado
    $logFile = $logDir . '/debug-vars.log';
    file_put_contents($logFile, $output, FILE_APPEND);

    // Também registrar no log de erros do PHP para facilidade de acesso
    error_log($output);

    // Retornar uma string vazia para não afetar o output
    return '';
}
