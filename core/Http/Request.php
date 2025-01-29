<?php

namespace Core\Http;

class Request
{
  private string $method;
  private string $uri;

  /** @var mixed[] */
  public array $params;
  private array $data;

  /** @var array<string, string> */
  private array $headers;

  public function __construct()
  {
    $this->method = $_REQUEST['_method'] ?? $_SERVER['REQUEST_METHOD'];
    $this->uri = $_SERVER['REQUEST_URI'];
    $this->params = $_REQUEST;
    $this->headers = function_exists('getallheaders') ? getallheaders() : [];
    $this->data = array_merge($_GET, $_POST);
    $this->params = [];
  }

  public function getMethod(): string
  {
    return $this->method;
  }

  public function getUri(): string
  {
    return $this->uri;
  }

  /** @return mixed[] */
  public function getParams(): array
  {
    return $this->params;
  }

  /** @return array<string, string> */
  public function getHeaders(): array
  {
    return $this->headers;
  }

  /** @param mixed[] $params */
  public function addParams(array $params): void
  {
    $this->params = array_merge($this->params, $params);
  }

  public function acceptJson(): bool
  {
    return isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] === 'application/json';
  }

  public function getParam(string $key, mixed $default = null): mixed
  {
    return $this->params[$key] ?? $default;
  }

  public function setParams(array $params): void
  {
    $this->params = $params;
  }

  public function only(array $keys): array
  {
    return array_filter(
      $this->all(),
      function ($key) use ($keys) {
        return in_array($key, $keys);
      },
      ARRAY_FILTER_USE_KEY
    );
  }

  public function all(): array
  {
    return $this->params;
  }

  public function get(string $key, $default = null)
  {
    return $this->data[$key] ?? $default;
  }
}
