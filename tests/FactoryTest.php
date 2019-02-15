<?php

namespace Illuminatech\ArrayFactory\Test;

use InvalidArgumentException;
use Illuminate\Container\Container;
use Illuminatech\ArrayFactory\Factory;
use Illuminatech\ArrayFactory\Test\Support\Car;
use Illuminatech\ArrayFactory\Test\Support\Person;
use Illuminatech\ArrayFactory\Test\Support\CarRent;

class FactoryTest extends TestCase
{
    public function testConfigure()
    {
        $factory = new Factory();

        $object = new Car();

        $config = [
            'registrationNumber' => 'AB1234',
            'type' => 'sedan',
            'color()' => ['red'],
            '()' => function (Car $car) {
                $car->startEngine();
            },
        ];

        /* @var $configuredObject Car */
        $configuredObject = $factory->configure($object, $config);

        $this->assertSame($object, $configuredObject);
        $this->assertSame($config['registrationNumber'], $configuredObject->registrationNumber);
        $this->assertSame($config['type'], $configuredObject->getType());
        $this->assertSame($config['color()'][0], $configuredObject->getColor());
        $this->assertSame(true, $configuredObject->isEngineRunning());
    }

    /**
     * @depends testConfigure
     */
    public function testConfigureImmutable()
    {
        /* @var $configuredObject Car */
        $factory = new Factory();

        $object = new Car();
        $configuredObject = $factory->configure($object, [
            'typeImmutable' => 'sedan',
        ]);
        $this->assertNotSame($object, $configuredObject);
        $this->assertSame('sedan', $configuredObject->getType());

        $object = new Car();
        $configuredObject = $factory->configure($object, [
            'colorImmutable()' => ['red'],
        ]);
        $this->assertNotSame($object, $configuredObject);
        $this->assertSame('red', $configuredObject->getColor());

        $object = new Car();
        $configuredObject = $factory->configure($object, [
            '()' => function (Car $car) {
                $new = clone $car;
                $new->startEngine();

                return $new;
            },
        ]);
        $this->assertNotSame($object, $configuredObject);
        $this->assertSame(true, $configuredObject->isEngineRunning());
    }

    /**
     * @depends testConfigure
     */
    public function testMake()
    {
        $container = new Container();

        $factory = new Factory($container);

        $object = $factory->make(Car::class);
        $this->assertTrue($object instanceof Car);

        $object = $factory->make(['__class' => Car::class]);
        $this->assertTrue($object instanceof Car);

        $object = $factory->make([
            '__class' => Person::class,
            '__construct()' => [
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
            ],
        ]);
        $this->assertTrue($object instanceof Person);
        $this->assertSame('John Doe', $object->name);
        $this->assertSame('johndoe@example.com', $object->email);
    }

    /**
     * @depends testMake
     */
    public function testMakeWithBindings()
    {
        $container = new Container();

        $factory = new Factory($container);

        $container->bind(Car::class, function() {
            $car = new Car();
            $car->registrationNumber = 'di-container';

            return $car;
        });

        /* @var $car Car */
        $car = $factory->make(Car::class);
        $this->assertSame('di-container', $car->registrationNumber);

        $container->bind(Person::class, function () {
            return new Person('John Doe', 'container@example.com');
        });

        /* @var $carRent CarRent */
        $carRent = $factory->make(CarRent::class);
        $this->assertSame('container@example.com', $carRent->person->email);
        $this->assertSame('di-container', $carRent->car->registrationNumber);
    }

    /**
     * @depends testMake
     */
    public function testEnsure()
    {
        $container = new Container();

        $factory = new Factory($container);

        $object = $factory->ensure(['__class' => Car::class], Car::class);
        $this->assertTrue($object instanceof Car);

        $this->expectException(InvalidArgumentException::class);
        $object = $factory->ensure(['__class' => Car::class], Person::class);
    }
}
