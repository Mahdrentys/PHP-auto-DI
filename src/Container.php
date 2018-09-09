<?php

namespace Mahdrentys\AutoDI;

use Psr\Container\ContainerInterface;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class Container implements ContainerInterface
{
    private static $container = null;
    private $factories = [];
    private $instances = [];

    /**
     * Add a key to the container.
     * 
     * $value can be directly an instance, or can be a closure that returns the instance.
     *
     * @param string $key
     * @param mixed|Closure $value
     * @return void
     */
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

    /**
     * Returns an instance of $key.
     *
     * @param string $key
     * @param mixed ...$args The constructor arguments
     */
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

    private function resolveParams(array $params, array $args = []):array
    {
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

        return $paramsToPass;
    }

    /**
     * Call a function or a method with auto-wiring.
     *
     * @param string|array $function Can be 'functionName', 'Class::staticMethod' or [$instance, 'method'].
     * @param mixed ...$args The function's non-auto arguments.
     * @return void
     */
    public function call($function, ...$args)
    {
        if (gettype($function) == 'string')
        {
            if (preg_match('/^.+::.+$/', $function))
            {
                $reflectedFunction = new ReflectionMethod($function);
            }
            else
            {
                $reflectedFunction = new ReflectionFunction($function);
            }
        }
        else if (gettype($function) == 'array')
        {
            $reflectedFunction = new ReflectionMethod($function[0], $function[1]);
        }

        $params = $reflectedFunction->getParameters();
        $paramsToPass = $this->resolveParams($params, $args);
        return call_user_func_array($function, $paramsToPass);
    }

    private function resolve($key, $args = [])
    {
        $reflectedClass = new ReflectionClass($key);

        if ($reflectedClass->isInstantiable())
        {
            $constructor = $reflectedClass->getConstructor();
            
            if (is_null($constructor))
            {
                $instance = $reflectedClass->newInstance();
            }
            else
            {
                $params = $constructor->getParameters();
                $paramsToPass = $this->resolveParams($params, $args);
                $instance = $reflectedClass->newInstanceArgs($paramsToPass);
            }

            $this->set($key, $instance);
            return $instance;
        }
        else
        {
            throw new Exception('AutoDI : Class "' . $key . '" not found.');
        }
    }

    /**
     * Build an instance instead of returning a singleton.
     * 
     * Working only with keys previously defined by $container->set().
     *
     * @param string $key
     * @return void
     */
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

    /**
     * @param string $key
     * @return boolean
     */
    public function has($key):bool
    {
        return isset($this->instances[$key]) OR isset($this->factories[$key]);
    }

    /**
     * Return the container.
     *
     * @return ContainerInterface
     */
    public static function getContainer():Container
    {
        if (is_null(self::$container))
        {
            $container = new Container();
            self::$container = &$container;
        }

        return self::$container;
    }
}