<?php

namespace Core\Errors;

use Core\Exceptions\HTTPException;

class ErrorsHandler
{
    public static function init(): void
    {
        // Configurar a exibição de erros - ocultar do navegador
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        ini_set('log_errors', 1);
        error_reporting(E_ALL);

        // Definir o diretório de logs e criar se não existir
        $logDir = __DIR__ . '/../../log';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        // Configurar o arquivo de log de erros
        ini_set('error_log', $logDir . '/php_errors.log');

        // Inicializar manipuladores de erros e exceções
        $handler = new self();
    }

    private function __construct()
    {
        ob_start(); // Iniciar o buffer de saída
        set_exception_handler($this->exceptionHandler());
        set_error_handler($this->errorHandler());
    }

    private static function exceptionHandler(): callable
    {
        return function ($e) {
            ob_end_clean(); // Discard the buffered output

            // Registrar o erro no log
            $logMessage = sprintf(
                "[%s] Uncaught exception '%s': %s in %s on line %d\nStack trace:\n%s\n",
                date('Y-m-d H:i:s'),
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );

            // Definir o diretório de logs e criar se não existir
            $logDir = __DIR__ . '/../../log';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            // Salvar no arquivo de log
            $logFile = $logDir . '/errors.log';
            error_log($logMessage, 3, $logFile);

            // Configurar o cabeçalho HTTP apropriado
            if ($e instanceof HTTPException) {
                header('HTTP/1.1 ' . $e->getStatusCode() . ' ' . $e->getMessage());
            } else {
                header('HTTP/1.1 500 Internal Server Error');
            }

            // Em ambiente de produção, mostrar mensagem simplificada
            $isProduction = false;
            if (getenv('APP_ENV') === 'production') {
                $isProduction = true;
            }

            if ($isProduction) {
                // Usar o template de erro 500
                include __DIR__ . '/../../app/views/errors/500.php';
                exit;
            } else {
                // Em ambiente de desenvolvimento, mostrar detalhes do erro
                echo <<<HTML
                <h1>{$e->getMessage()}</h1>
                <pre>
                Uncaught exception class: {get_class($e)}
                </pre>
                Message: <strong>{$e->getMessage()}</strong><br>
                File: {$e->getFile()}<br>
                Line: {$e->getLine()}<br>
                <br>
                Stack Trace: <br>
                <pre>
                    {$e->getTraceAsString()}
                </pre>
                HTML;
            }
        };
    }

    private static function errorHandler(): callable
    {
        return function ($errorNumber, $errorStr, $file, $line) {
            ob_end_clean(); // Discard the buffered output

            // Registrar o erro no log
            $logMessage = sprintf(
                "[%s] PHP Error [$errorNumber]: $errorStr in $file on line $line\n",
                date('Y-m-d H:i:s')
            );

            // Definir o diretório de logs e criar se não existir
            $logDir = __DIR__ . '/../../log';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            // Salvar no arquivo de log
            $logFile = $logDir . '/errors.log';
            error_log($logMessage, 3, $logFile);

            header('HTTP/1.1 500 Internal Server Error');

            // Em ambiente de produção, mostrar mensagem simplificada
            $isProduction = false;
            if (getenv('APP_ENV') === 'production') {
                $isProduction = true;
            }

            if ($isProduction) {
                switch ($errorNumber) {
                    case E_USER_ERROR:
                        echo '<h1>Erro crítico</h1>';
                        echo '<p>Desculpe, ocorreu um erro ao processar sua solicitação.</p>';
                        exit(1);
                    case E_USER_WARNING:
                    case E_USER_NOTICE:
                    default:
                        echo '<h1>Erro no sistema</h1>';
                        echo '<p>Desculpe, ocorreu um erro ao processar sua solicitação.</p>';
                        echo '<p>Por favor, tente novamente mais tarde ou entre em contato com o suporte.</p>';
                }
            } else {
                // Em ambiente de desenvolvimento, mostrar detalhes do erro
                switch ($errorNumber) {
                    case E_USER_ERROR:
                        echo <<<HTML
                            <b>ERROR</b> [$errorNumber] $errorStr<br>
                            Fatal error on line $line in file $file<br>
                            PHP {PHP_VERSION} ({PHP_OS})<br>
                            Aborting...<br>
                            HTML;
                        break;
                    case E_USER_WARNING:
                        echo "<b>WARNING</b> [$errorNumber] $errorStr<br>";
                        break;
                    case E_USER_NOTICE:
                        echo "<b>NOTICE</b> [$errorNumber] $errorStr<br>";
                        break;
                    default:
                        echo "<b>UNKNOWN ERROR TYPE</b> [$errorNumber] $errorStr<br>";
                }

                echo <<<HTML
                    <h1>$errorStr</h1>
                    File: $file <br>
                    Line: $line <br>
                    <br>
                    Stack Trace: <br>
                    HTML;

                echo '<pre>';
                debug_print_backtrace();
                echo '</pre>';
            }

            // Para erros fatais, encerrar a execução
            if ($errorNumber == E_USER_ERROR) {
                exit(1);
            }

            // Retornar true para que o erro não seja processado pelo manipulador de erros padrão do PHP
            return true;
        };
    }
}
