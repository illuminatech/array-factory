<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2015 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ArrayFactory;

use Illuminate\Contracts\Container\Container;

/**
 * FactoryContract
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface FactoryContract
{
    /**
     * Creates new object from the definition.
     *
     * @param  array|string  $definition
     * @param  Container|null  $container DI container instance.
     * @return object created object.
     */
    public function make($definition, Container $container = null);

    /**
     * Configures existing object applying given configuration to it.
     *
     * @param  object  $object object to be configured.
     * @param  iterable  $config configuration to be applied.
     * @param  Container|null  $container DI container instance.
     * @return object configured object.
     */
    public function configure($object, iterable $config, Container $container = null);

    /**
     * Resolves the specified reference into the actual object and makes sure it is of the specified type.
     *
     * @param  array|object|string  $reference an object or its factory-compatible definition.
     * @param  string|null  $type the class/interface name to be checked. If null, type check will not be performed.
     * @param  Container|null  $container DI container instance.
     * @return object created object.
     */
    public function ensure($reference, string $type = null, Container $container = null);
}
