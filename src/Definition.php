<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2015 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ArrayFactory;

use InvalidArgumentException;

/**
 * Definition represents array factory compatible definition.
 *
 * Presence of such object among constructor or method arguments at other definition will trigger its conversion
 * into defined object right away.
 *
 * For example:
 *
 * ```php
 * $factory = new Factory();
 *
 * $car = $factory->make([
 *     '__class' => Car::class,
 *     '__construct()' => [
 *         'engine' => new Definition(Engine::class),
 *     ],
 *     'driver' => new Definition([
 *         '__class' => Person::class,
 *         'name' => 'John Doe',
 *     ]),
 * ]);
 * ```
 *
 * @see FactoryContract::make()
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Definition
{
    /**
     * @var array|string raw definition for {@link FactoryContract::make()}
     */
    public $definition;

    /**
     * Definition constructor.
     *
     * @param  array|string  $definition array factory compatible definition.
     */
    public function __construct($definition)
    {
        $this->definition = $definition;
    }

    /**
     * Restores class state after using `var_export()`.
     * @see var_export()
     *
     * @param  array  $state state to be restored from.
     * @return static restored instance.
     * @throws InvalidArgumentException when $state property does not contain `id` parameter.
     */
    public static function __set_state($state)
    {
        if (! isset($state['definition'])) {
            throw new InvalidArgumentException(
                'Failed to instantiate class "'.get_called_class().'". Required parameter "definition" is missing.'
            );
        }

        return new self($state['definition']);
    }
}
