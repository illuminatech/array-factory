<?php

namespace Illuminatech\ArrayFactory\Test;

use Illuminate\Container\Container;
use Illuminatech\ArrayFactory\FactoryContract;
use Illuminatech\ArrayFactory\FactoryServiceProvider;

class FactoryServiceProviderTest extends TestCase
{
    public function testRegister()
    {
        $app = new Container();

        $serviceProvider = new FactoryServiceProvider($app);

        $serviceProvider->register();

        $factory = $app->make(FactoryContract::class);

        $this->assertTrue($factory instanceof FactoryContract);
        $this->assertSame($factory, $app->make(FactoryContract::class));
    }
}
