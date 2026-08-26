<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit;

use AmarMayor\Support\Container;
use AmarMayor\Tests\TestCase;

class ContainerTest extends TestCase
{
    public function testBindingAndResolution(): void
    {
        $container = new Container();
        $container->bind('service.dummy', function () {
            return new \stdClass();
        });

        $this->assertTrue($container->has('service.dummy'));
        $obj1 = $container->get('service.dummy');
        $this->assertTrue($obj1 instanceof \stdClass);
    }

    public function testSingleton(): void
    {
        $container = new Container();
        $container->singleton('service.singleton', function () {
            $obj = new \stdClass();
            $obj->random = rand(1, 100000);
            return $obj;
        });

        $obj1 = $container->get('service.singleton');
        $obj2 = $container->get('service.singleton');

        $this->assertEquals($obj1->random, $obj2->random);
    }
}
