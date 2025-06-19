<?php

namespace Core\Http\Controllers;

use App\Models\Employee;
use Core\Constants\Constants;
use Lib\Authentication\Auth;

class Controller
{
    protected string $layout = 'application';

    protected ?Employee $current_user = null;

    public function __construct()
    {
        $this->current_user = Auth::user();
    }

    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    public function currentUser(): ?Employee
    {
        if ($this->current_user === null) {
            $this->current_user = Auth::user();
        }

        return $this->current_user;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);

        // Verificar se o arquivo .php existe primeiro, caso contrário tentar .phtml
        $phpView = Constants::rootPath()->join('app/views/' . $view . '.php');
        $phtmlView = Constants::rootPath()->join('app/views/' . $view . '.phtml');

        if (file_exists($phpView)) {
            $view = $phpView;
        } else {
            $view = $phtmlView;
        }

        require Constants::rootPath()->join('app/views/layouts/' . $this->layout . '.phtml');
    }


    /**
     * @param array<string, mixed> $data
     */
    protected function renderJson(string $view, array $data = []): void
    {
        extract($data);

        $view = Constants::rootPath()->join('app/views/' . $view . '.json.php');
        $json = [];

        header('Content-Type: application/json; chartset=utf-8');
        require $view;
        echo json_encode($json);
        return;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $errors
     */
    protected function renderWithErrors(string $view, array $data = [], array $errors = []): void
    {
        $data['errors'] = $errors;
        $this->render($view, $data);
    }

    protected function redirectTo(string $location): void
    {
        header('Location: ' . $location);

        // Não sair durante os testes
        if (!defined('PHPUNIT_TEST_RUNNING') || PHPUNIT_TEST_RUNNING !== true) {
            exit;
        }

        // Em ambiente de teste, lança uma exceção para evitar a continuação do código
        throw new \RuntimeException("Redirect to: $location");
    }

    protected function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirectTo($referer);
    }
}
