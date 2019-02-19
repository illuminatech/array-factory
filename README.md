<p align="center">
    <a href="https://github.com/illuminatech" target="_blank">
        <img src="https://avatars1.githubusercontent.com/u/47185924" height="100px">
    </a>
    <h1 align="center">Laravel Array Factory</h1>
    <br>
</p>

This extension allows DI aware object creation from array definition.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/illuminatech/array-factory/v/stable.png)](https://packagist.org/packages/illuminatech/array-factory)
[![Total Downloads](https://poser.pugx.org/illuminatech/array-factory/downloads.png)](https://packagist.org/packages/illuminatech/array-factory)
[![Build Status](https://travis-ci.org/illuminatech/array-factory.svg?branch=master)](https://travis-ci.org/illuminatech/array-factory)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist illuminatech/array-factory
```

or add

```json
"illuminatech/array-factory": "*"
```

to the require section of your composer.json.


Usage
-----

This extension allows DI aware object creation from array definition.
Creation is performed by factory defined via [[\Illuminatech\ArrayFactory\FactoryContract]] contract.
[[\Illuminatech\ArrayFactory\Factory]] can be used for particular implementation.
Such factory allows creation of any object from its array definition.
Keys in definition array are processed by following rules:

- '__class': string, full qualified name of the class to be instantiated.
- '__construct()': array, arguments to be bound during constructor invocation.
- 'methodName()': array, list of arguments to be passed to the object method, which name defined via key.
- 'fieldOrProperty': mixed, value to be assigned to the public field or passed to the setter method.
- '()': callable, PHP callback to be invoked once object has been instantiated and all other configuration applied to it.

Imagine we have the following class defined at our project:

```php
<?php

class Car
{
    public $condition;
    
    public $registrationNumber;
    
    private $type = 'unknown';
    
    private $color = 'unknown';
    
    private $engineRunning = false;
    
    public function __construct(string $condition)
    {
        $this->condition = $condition;
    }
    
    public function setType(string $type)
    {
        $this->type = $type;
    }
    
    public function getType(): string
    {
        return $this->type;
    }
    
    public function color(string $color): self
    {
        $this->color = $color;
    
        return $this;
    }
    
    public function startEngine(): self
    {
        $this->engineRunning = true;

        return $this;
    }    
}
```

Instance of such class can be instantiated using array factory in the following way:

```php
<?php

/* @var $factory \Illuminatech\ArrayFactory\FactoryContract */

$car = $factory->make([
    '__class' => Car::class, // class name
    '__construct()' => ['condition' => 'good'], // constructor arguments
    'registrationNumber' => 'AB1234', // set public field `Car::$registrationNumber`
    'type' => 'sedan', // pass value to the setter `Car::setType()`
    'color()' => ['red'], // pass arguments to the method `Car::color()`
    '()' => function (Car $car) {
         // final adjustments to be made after object creation and other config application:
         $car->startEngine();
     },
]);
```

The main benefit of array object definition is lazy loading: you can define entire object configuration as a mere array
without even loading the class source file, and then instantiate actual object only in case it becomes necessary.

Defined array configuration can be adjusted, applying default values for it. For example:

```php
<?php

/* @var $factory \Illuminatech\ArrayFactory\FactoryContract */

$config = [
    'registrationNumber' => 'AB1234',
    'type' => 'sedan',
    'color()' => ['red'],
];

// ...

$defaultCarConfig = [
    '__class' => Car::class,
    'type' => 'sedan',
    'condition' => 'good',
];

$car = $factory->make(array_merge($defaultCarConfig, $config));
```

You may use [[\Illuminatech\ArrayFactory\Facades\Factory]] facade for quick access to the factory functionality.
For example:

```php
<?php

use Illuminatech\ArrayFactory\Facades\Factory;

$car = Factory::make([
    '__class' => Car::class,
    'registrationNumber' => 'AB1234',
    'type' => 'sedan',
]);
```


## Service configuration <span id="service-configuration"></span>

The most common use case for array factory is creation of the universal configuration for particular application service.
Imagine we create a library providing geo-location by IP address detection. Since there are plenty of external services
and means to solve this task, we have created some high level contract, like following:

```php
<?php

namespace MyVendor\GeoLocation;

use Illuminate\Http\Request;

interface DetectorContract
{
    public function detect(Request $request): LocationInfo;
}
```

This contact may have multiple different implementations: each per each different approach and service. Each particular
implementation provides its own set of configuration parameters, which can not be unified.
Using array factory we can define a service provider for such library in following way:

```php
<?php

namespace MyVendor\GeoLocation;

use Illuminatech\ArrayFactory\Factory;
use Illuminate\Support\ServiceProvider;

class DetectorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DetectorContract::class, function ($app) {
            $factory = new Factory($app);
            
            $factory->make(array_merge(
                ['__class' => DefaultDetector::class], // default config
                $app->config->get('geoip', []) // developer defined config
            ));
        });
    }
}
```

This allows developer to specify any particular detector class to be used along with its configuration. The actual
configuration file 'config/geoip.php' may look like following:

```php
<?php
/* file 'config/geoip.php' */

return [
    '__class' => \MyVendor\GeoLocation\SomeExternalApiDetector::class,
    'apiEndpoint' => 'https://some.external.service/api',
    'apiKey' => env('SOME_EXTERNAL_API_KEY'),
];
```

It can also look like following:

```php
<?php
/* file 'config/geoip.php' */

return [
    '__class' => \MyVendor\GeoLocation\LocalFileDetector::class,
    'geoipDatabaseFile' => __DIR__.'/geoip/local.db',
];
```

Both configuration will work fine with the service provider we created, and same will be for countless other possible
configurations for different geo-location detectors, which may not even exist yet.


## Interaction with DI container <span id="interaction-with-di-container"></span>

[[\Illuminatech\ArrayFactory\Factory]] is DI aware: it performs object instantiation via [[\Illuminate\Contracts\Container\Container::make()]].
Thus bindings set within container will affect object creation. For example:

```php
<?php

use Illuminate\Container\Container;
use Illuminatech\ArrayFactory\Factory;

$container = Container::getInstance();

$factory = new Factory($container);

$container->bind(Car::class, function() {
    $car = new Car();
    $car->setType('by-di-container');

    return $car;
});

/* @var $car Car */
$car = $factory->make([
    '__class' => Car::class,
    'registrationNumber' => 'AB1234',
]);

var_dump($car->getType()); // outputs: 'by-di-container'
```

> Note: obviously, in case there is a DI container binding for the instantiated class, the key '__construct()' inside
  array configuration will be ignored.

DI container is also used during configuration method invocations, allowing automatic arguments injection. For example:

```php
<?php

use Illuminate\Container\Container;
use Illuminatech\ArrayFactory\Factory;

class Person
{
    public $carRents = [];
    
    public function rentCar(Car $car, $price)
    {
        $this->carRents[] = ['car' => $car, 'price' => $price];
    }
}

$container = Container::getInstance();

$factory = new Factory($container);

$container->bind(Car::class, function() {
    $car = new Car();
    $car->setType('by-di-container');

    return $car;
});

/* @var $person Person */
$person = $factory->make([
    '__class' => Person::class,
    'rentCar()' => ['price' => 12],
]);

var_dump($person->carRents[0]['car']->getType()); // outputs: 'by-di-container'
var_dump($person->carRents[0]['price']); // outputs: '12'
```

Note that final handler callback ('()' configuration key) is not DI aware and does not provide binding for its arguments.
However, the factory instance is always passed as its second argument allowing you to access to its DI container if needed.
Following code will produce the same result as the one from previous example:

```php
<?php

use Illuminate\Container\Container;
use Illuminatech\ArrayFactory\Factory;

$container = Container::getInstance();

$factory = new Factory($container);

/* @var $person Person */
$person = $factory->make([
    '__class' => Person::class,
    '()' => function (Person $person, Factory $factory) {
        $factory->getContainer()->call([$person, 'rentCar'], ['price' => 12]);
    },
]);
```


## Standalone configuration <span id="standalone-configuration"></span>

You may use array factory to configure or re-configure already existing objects. For example:

```php
<?php

use Illuminatech\ArrayFactory\Factory;

$factory = new Factory();

$car = new Car();
$car->setType('sedan');
$car->color('red');

/* @var $car Car */
$car = $factory->configure($car, [
    'type' => 'hatchback',
    'color()' => ['green'],
]);

var_dump($car->getType()); // outputs: 'hatchback'
var_dump($car->getColor()); // outputs: 'green'
```


## Type ensurance <span id="type-ensurance"></span>

You may add extra check whether created object matches particular base class or interface, using `ensure()` method.
For example:

```php
<?php

use Illuminate\Support\Carbon;
use Illuminate\Cache\RedisStore;
use Illuminate\Contracts\Cache\Store;
use Illuminatech\ArrayFactory\Factory;

$factory = new Factory();

// successful creation:
$cache = $factory->ensure(
    [
        '__class' => RedisStore::class,
    ],
    Store::class
);

// throws an exception:
$cache = $factory->ensure(
    [
        '__class' => Carbon::class,
    ],
    Store::class
);
```

## Immutable methods handling <span id="immutable-methods-handling"></span>

[[\Illuminatech\ArrayFactory\Factory]] handles immutable methods during object configuration, returning new object
from their invocations. For example: in case we have following class:

```php
<?php

class CarImmutable extends Car
{
    public function setType(string $type)
    {
        $new = clone $this; // immutability
        $new->type = $type;
    
        return $new;
    }
    
    public function color(string $color): self
    {
        $new = clone $this; // immutability
        $new->color = $color;
    
        return $new;
    }
}
```

The following configuration will be applied correctly:

```php
<?php

use Illuminatech\ArrayFactory\Factory;

$factory = new Factory();

/* @var $car Car */
$car = $factory->make([
    '__class' => CarImmutable::class,
    'type' => 'sedan',
    'color()' => ['green'],
]);

var_dump($car->getType()); // outputs: 'sedan'
var_dump($car->getColor()); // outputs: 'green'
```

> Note: since there could be immutable method invocations during configuration, you should always use result
  of [[\Illuminatech\ArrayFactory\FactoryContract::configure()]] method instead of its argument.


## Recursive make <span id="recursive-make"></span>

For complex object, which stores other object as its inner property, there may be need to configure both host and resident
objects using array definition and resolve them both via array factory. For this case definition like following may
be created: 

```php
<?php

$config = [
    '__class' => Car::class,
    // ...
    'engine' => [
        '__class' => InternalCombustionEngine::class,
        // ...
    ],
];
```

However, nested definitions are not resolved by array factory automatically. Following example will not instantiate
engine instance:

```php
<?php

use Illuminatech\ArrayFactory\Factory;

$factory = new Factory();

$config = [
    '__class' => Car::class,
    // ...
    'engine' => [
        '__class' => InternalCombustionEngine::class,
        // ...
    ],
];

$car = $factory->make($config);
var_dump($car->engine); // outputs array
```

This is done in order to allow setup of the slave internal configuration into created object, so it be can resolved
in lazy way according to its own internal logic.

However, you may enforce resolving of the nested definition wrapping it into [[\Illuminatech\ArrayFactory\Definition]] instance.
For example:

```php
<?php

use Illuminatech\ArrayFactory\Factory;
use Illuminatech\ArrayFactory\Definition;

$factory = new Factory();

$config = [
    '__class' => Car::class,
    // ...
    'engine' => new Definition([
        '__class' => InternalCombustionEngine::class,
        // ...
    ]),
];

$car = $factory->make($config);
var_dump($car->engine); // outputs object
```
