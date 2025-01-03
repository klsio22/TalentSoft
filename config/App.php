<?php

namespace Config;

class App
{
  public static array $middlewareAliases = [
    'user' => \App\Middleware\Authenticate::class,
    'admin' => \App\Middleware\AdminMiddleware::class,
  ];
}
