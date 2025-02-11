<?php

namespace Core\Database;

class DatabaseException extends \Exception
{
  private ?string $query;

  public function __construct(
    string $message = "",
    int $code = 0,
    ?\Throwable $previous = null,
    ?string $query = null
  ) {
    parent::__construct($message, $code, $previous);
    $this->query = $query;
  }

  public function getQuery(): ?string
  {
    return $this->query;
  }
}
