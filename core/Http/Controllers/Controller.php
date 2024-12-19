<?php

namespace Core\Http\Controllers;

use Core\Constants\Constants;

class Controller
{
  protected string $layout = 'application';


  /**
   * @param array<string, mixed> $data
   */
  protected function render(string $view, array $data = []): void
  {
    extract($data);

    $view = Constants::rootPath()->join('app/views/' . $view . '.phtml');
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

  protected function redirectTo(string $location): void
  {
    header('Location: ' . $location);
    exit;
  }

  protected function redirectBack(): void
  {
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    $this->redirectTo($referer);
  }
}
