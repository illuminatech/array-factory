<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2015 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ArrayFactory\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminatech\ArrayFactory\FactoryContract;

/**
 * Factory is a facade for array factory access.
 *
 * This facade requires {@see \Illuminatech\ArrayFactory\FactoryContract} implementation to be bound as singleton
 * to the application service container.
 *
 * @see \Illuminatech\ArrayFactory\FactoryContract
 *
 * @method static object make(array|string $definition, \Illuminate\Contracts\Container\Container $container = null)
 * @method static object configure(object $object, iterable $config, \Illuminate\Contracts\Container\Container $container = null)
 * @method static object ensure(array|\Closure|string $reference, string $type = null, \Illuminate\Contracts\Container\Container $container = null)
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Factory extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return FactoryContract::class;
    }
}
