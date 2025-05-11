<?php

namespace Core\Http;

class Request
{
  private string $method;
  private string $uri;
  private array $params = [];
  private array $data = [];
  private array $headers = [];

  public function __construct()
  {
    $this->method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];
    $this->uri = $_SERVER['REQUEST_URI'];
    $this->headers = function_exists('getallheaders') ? getallheaders() : [];
    $this->data = $this->sanitizeInput(array_merge($_GET, $_POST));
  }

  private function sanitizeInput(array $input): array
  {
    return array_map(function ($value) {
      return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }, $input);
  }

  public function getMethod(): string
  {
    return $this->method;
  }

  public function getUri(): string
  {
    return $this->uri;
  }

  public function getData(string $key = null, $default = null)
  {
    if ($key === null) {
      return $this->data;
    }
    return $this->data[$key] ?? $default;
  }

  public function addParams(array $params): void
  {
    $this->params = $this->sanitizeInput($params);
  }

  public function getParam(string $key, $default = null)
  {
    return $this->params[$key] ?? $default;
  }

  public function only(array $keys): array
  {
    return array_intersect_key($this->data, array_flip($keys));
  }

  public function getHeaders(): array
  {
    return $this->headers;
  }
}
