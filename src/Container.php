<?php

namespace Mahdrentys\AutoDI;

use Psr\Container\ContainerInterface;
use Closure;
use Exception;
use ReflectionClass;

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

    public function get($key, ...$args)
    {
        if (!isset($this->instances[$key]))
        {
            if (isset($this->factories[$key]))
            {
                $this->instances[$key] = call_user_func_array($this->factories[$key], $args);
            }
            else
            {
                return $this->resolve($key, $args);
            }
        }

        return $this->instances[$key];
    }

    public function resolve($key, $args = [])
    {
        $reflectedClass = new ReflectionClass($key);

        if ($reflectedClass->isInstantiable())
        {
            $constructor = $reflectedClass->getConstructor();
            $params = $constructor->getParameters();
            $paramsToPass = [];

            $scalar = false;

            foreach ($params as $param)
            {
                $class = $param->getClass();

                if ($class)
                {
                    $paramsToPass[] = $this->get($class->getName());
                }
                else
                {
                    if (empty($paramsToPass))
                    {
                        $scalar = 'before';
                    }
                    else
                    {
                        $scalar = 'after';
                    }
                }
            }

            if ($scalar)
            {
                if ($scalar == 'before')
                {
                    $objectParams = $paramsToPass;
                    $paramsToPass = $args;

                    foreach ($objectParams as $objectParam)
                    {
                        $paramsToPass[] = $objectParam;
                    }
                }
                else if ($scalar == 'after')
                {
                    foreach ($args as $arg)
                    {
                        $paramsToPass[] = $arg;
                    }
                }
            }

            $instance = $reflectedClass->newInstanceArgs($paramsToPass);
            $this->set($key, $instance);
            return $instance;
        }
        else
        {
            throw new Exception('AutoDI : Class "' . $key . '" not found.');
        }
    }

    public function build($key)
    {
        if (isset($this->factories[$key]))
        {
            return $this->factories[$key]();
        }
        else if (isset($this->instance[$key]))
        {
            throw new Exception('AutoDI : Key "' . $key . '" is not defined by a Closure, so you can\'t build it.');
        }
        else
        {
            throw new Exception('AutoDI : Key "' . $key . '" was not found.');
        }
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