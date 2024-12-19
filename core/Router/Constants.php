<?php

// Core/Router/Constants.php
namespace Core\Router;

class Constants
{
    public static function rootPath()
    {
        return new Path(dirname(__DIR__, 2));
    }
}
