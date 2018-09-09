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
    public $a;

    public function __construct(A $a)
    {
        $this->uniqid = uniqid();
        $this->a = $a;
    }
}

$container = Container::getContainer();
$container->set('a', new A());
$container->set('b', function() use ($container)
{
    return new B($container->get('a'));
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

        $b = $container->get('b');
        expect($b)->toBeAnInstanceOf(B::class);
    });

    it('should build the items only once time', function()
    {
        $container = Container::getContainer();

        $a1 = $container->get('a');
        $a2 = $container->get('a');
        expect($a1->uniqid == $a2->uniqid)->toBe(true);

        $b1 = $container->get('b');
        $b2 = $container->get('b');
        expect($b1->uniqid == $b2->uniqid)->toBe(true);
    });

    it('should say if a key is set', function()
    {
        $container = Container::getContainer();

        expect($container->has('a'))->toBe(true);
        expect($container->has('b'))->toBe(true);
        expect($container->has('c'))->toBe(false);
    });

    it('should rebuild the item', function()
    {
        $container = Container::getContainer();

        $b1 = $container->build('b');
        $b2 = $container->build('b');
        expect($b1->uniqid != $b2->uniqid)->toBe(true);
    });

    it('should resolve empty constructor', function()
    {
        $container = Container::getContainer();

        $a = $container->get(A::class);
        expect($a)->toBeAnInstanceOf(A::class);
    });

    it('should resolve constructor with a dependency', function()
    {
        $container = Container::getContainer();

        $b = $container->get(B::class);
        expect($b)->toBeAnInstanceOf(B::class);
        expect($b->a)->toBeAnInstanceOf(A::class);
    });

});