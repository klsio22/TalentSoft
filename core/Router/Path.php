<?php

// Core/Router/Path.php
namespace Core\Router;

class Path
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function join(string $segment): string
    {
        return $this->path . DIRECTORY_SEPARATOR . $segment;
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
