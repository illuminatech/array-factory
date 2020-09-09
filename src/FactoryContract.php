<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ArrayFactory;

use Illuminate\Contracts\Container\Container;

/**
 * FactoryContract defines the contract for array factory.
 *
 * Such factory allows creation of any object from its array definition.
 * Keys in definition array are processed by following rules:
 *
 * - '__class': string, full qualified name of the class to be instantiated.
 * - '__construct()': array, arguments to be bound during constructor invocation.
 * - 'methodName()': array, list of arguments to be passed to the object method, which name defined via key.
 * - 'fieldOrProperty': mixed, value to be assigned to the public field or passed to the setter method.
 * - '()': callable, PHP callback to be invoked once object has been instantiated and all other configuration applied to it.
 *
 * For example:
 *
 * ```php
 * $factory->make([
 *     '__class' => Item::class,
 *     '__construct()' => ['constructorArgument' => 'initial'],
 *     'publicField' => 'value assigned to public field',
 *     'virtualProperty' => 'value passed to setter method',
 *     'someMethod()' => ['argument1' => 'value1', 'argument2' => 'value2'],
 *     '()' => function (Item $item) {
 *          // final adjustments
 *      },
 * ]);
 * ```
 *
 * @see Definition
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
