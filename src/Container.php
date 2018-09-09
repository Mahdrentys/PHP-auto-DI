<?php

namespace Mahdrentys\AutoDI;

use Psr\Container\ContainerInterface;
use Closure;

class Container implements ContainerInterface
{
    private static $container = null;
    private $factories = [];
    private $instances = [];

    public function set($key, $value):void
    {
        if (is_object($value) AND $value instanceof Closure)
        {
            $this->factories[$key] = $value;
        }
        else
        {
            $this->instances[$key] = $value;
        }
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