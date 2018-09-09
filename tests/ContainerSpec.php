<?php

use Mahdrentys\AutoDI\Container;

class A
{
    public $uniqid;

    public function __construct()
    {
        $this->uniqid = uniqid();
    }
}

class B
{
    public $uniqid;

    public function __construct()
    {
        $this->uniqid = uniqid();
    }
}

$container = Container::getContainer();
$container->set('a', new A());
$container->set('b', function()
{
    return new B();
});

describe('Container', function()
{

    it('should return container', function()
    {
        $container = Container::getContainer();
        expect($container)->toBeAnInstanceOf(Container::class);
    });

    it('should return the items', function()
    {
        $container = Container::getContainer();

        $a = $container->get('a');
        expect($a)->toBeAnInstanceOf(A::class);

        $a = $container->get('b');
        expect($a)->toBeAnInstanceOf(B::class);
    });

    it('should say if a key is set', function()
    {
        $container = Container::getContainer();

        expect($container->has('a'))->toBe(true);
        expect($container->has('b'))->toBe(true);
        expect($container->has('c'))->toBe(false);
    });

});