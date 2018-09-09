<?php

namespace Mahdrentys\AutoDI;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private static $container = null;

    public function set($key, $value):void
    {

    }

    public function get($key)
    {

    }

    public function has($key):bool
    {

    }

    public static function getContainer():ContainerInterface
    {
        if (is_null(self::$container))
        {
            self::$container = new Container();
        }

        return self::$container;
    }
}