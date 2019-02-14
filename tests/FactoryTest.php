<?php

namespace Illuminatech\ArrayFactory\Test;

use Illuminatech\ArrayFactory\Factory;
use Illuminatech\ArrayFactory\Test\Support\Car;

class FactoryTest extends TestCase
{
    public function testConfigure()
    {
        $factory = new Factory();

        $object = new Car();

        $config = [
            'registrationNumber' => 'AB1234',
            'type' => 'sedan',
        ];

        /* @var $configuredObject Car */
        $configuredObject = $factory->configure($object, $config);

        $this->assertSame($object, $configuredObject);
        $this->assertSame($config['registrationNumber'], $configuredObject->registrationNumber);
        $this->assertSame($config['type'], $configuredObject->getType());
    }
}
