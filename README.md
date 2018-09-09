# PHP Auto DI

PSR-11 Dependency injection container implementation.

## Installation

Install composer if you don't have it, and run :

```
composer install mahdrentys/auto-di
```

## Usage

```php
<?php

require 'vendor/autoload.php';

use Mahdrentys\AutoDI\Container;

class A
{
    
}

class B
{
    public $a;

    public function __construct(A $a)
    {
        $this->a = $a;
    }
}

$container = Container::getContainer(); // You can this method throughout all your application, the same container is returned everytime.

$a = $container->get(A::class); // Return an instance of A
$a = $container->get(A::class); // Return the same instance of A, as a singleton

$b = $container->get(B::class); // Return an instance of B (the A dependency is automaticly resolved)
var_dump($b->a); // The same instance of A than $a

class C
{
    public $b;
    public $name;

    public function __construct(string $name, B $b)
    {
        $this->name = $name;
        $this->b = $b;
    }
}

$c = $container->get(C::class, 'RandomName'); // You can pass the arguments of the constructor
/**
 * The dependencies can be placed before or after the "normal" arguments :
 * public function __construct(string $name, B $b)
 * OR
 * public function __construct(B $b, string $name)
 */

// You can also call functions and methods with auto-wiring
function test(B $b, $someArgument)
{
    return $someArgument;
}
$result = $container->call('test', 'someArgument');

class D
{
    public function test(B $b, $someArgument)
    {
        return $someArgument;
    }

    public static function testStatic($someArgument, B $b)
    {
        return $someArgument;
    }
}
$d = new D();
// Or :
$d = $container->get(D::class);
$result = $container->call([$d, 'test'], 'someArgument');
$result = $container->call('D::testStatic', 'someArgument');


// You can define keys manually
// For example, you have a Database class that require PDO, so you have to define how to instantiate PDO.

class Database
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function query(string $query)
    {
        // Code....................
    }
}

$pdo = new PDO('mysql:host=localhost;dbname=yourdatabase;charset=utf8', 'root', 'root');
$container->set(PDO::class, $pdo);

// You can also do :
$container->set(PDO::class, function()
{
    return new PDO('mysql:host=localhost;dbname=yourdatabase;charset=utf8', 'root', 'root');
});
// In this case, PDO is instantiated only if it is used.

$database = $container->get(Database::class); // $database is an instance of Database, with the right PDO in it


// You can also use interfaces

interface CacheDriverInterface
{
    public function set(string $key, $value):void;
    public function get(string $key);
}

class RedisCacheDriver implements CacheDriverInterface
{
    public function set(string $key, $value):void
    {
        // Code..................
    }

    public function get(string $key)
    {
        // Code..................
    }
}

class Model
{
    private $cacheDriver;

    public function __construct(CacheDriverInterface $cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;
    }
}

$container->set(CacheDriverInterface::class, function()
{
    return $container->get(RedisCacheDriver::class);
});

$model = $container->get(Model::class); // The RedisCacheDriver is used.
```


## License

This project is licensed under the MIT license - see the [LICENSE](LICENSE) file for more details.