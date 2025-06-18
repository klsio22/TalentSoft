<?php

namespace Tests\Unit\Controllers;

use App\Controllers\EmployeesController;


/**
 * Controlador de teste que herda de EmployeesController mas substitui o comportamento de autenticação
 */
class MockEmployeesController extends EmployeesController
{
  private bool $isAdmin;
  private bool $isHR;

  public function __construct(bool $isAdmin = true, bool $isHR = false)
  {
    $this->isAdmin = $isAdmin;
    $this->isHR = $isHR;
    // Não chamar o construtor pai para evitar a verificação de autenticação
  }

  // Sobrescrever o método redirectTo para evitar redirecionamento nos testes
  protected function redirectTo(string $location): void
  {
    // Não fazer nada nos testes
  }

  // Getters para verificar para onde o redirecionamento seria feito
  public function getIsAdmin(): bool
  {
    return $this->isAdmin;
  }

  public function getIsHR(): bool
  {
    return $this->isHR;
  }
}
