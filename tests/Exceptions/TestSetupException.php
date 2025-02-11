<?php

namespace Tests\Exceptions;

class TestSetupException extends \Exception
{
  private string $context;

  public function __construct(
    string $message,
    string $context,
    int $code = 0,
    ?\Throwable $previous = null
  ) {
    parent::__construct($message, $code, $previous);
    $this->context = $context;
  }

  public function getContext(): string
  {
    return $this->context;
  }
}
