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
        if (!isset($this->instances[$key]))
        {
            if (isset($this->factories[$key]))
            {
                $this->instances[$key] = $this->factories[$key]();
            }
            else
            {
                throw new Exception('AutoDI : Key "' . $key . '" was not found.');
            }
        }

        return $this->instances[$key];
    }

    public function has($key):bool
    {
        return isset($this->instances[$key]) OR isset($this->factories[$key]);
    }

    public static function getContainer():ContainerInterface
    {
        if (is_null(self::$container))
        {
            $container = new Container();
            self::$container = &$container;
        }

        return self::$container;
    }
}