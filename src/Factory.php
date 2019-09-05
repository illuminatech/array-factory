<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ArrayFactory;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Contracts\Container\Container;

/**
 * Factory is a particular DI aware implementation of {@see FactoryContract}.
 *
 * @see FactoryContract
 * @see Definition
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Factory implements FactoryContract
{
    /**
     * @var \Illuminate\Contracts\Container\Container DI container to be used.
     */
    private $container;

    /**
     * Constructor.
     *
     * @param  Container|null  $container DI container to be used.
     */
    public function __construct(Container $container = null)
    {
        if ($container !== null) {
            $this->setContainer($container);
        }
    }

    /**
     * @return Container used DI container.
     */
    public function getContainer(): Container
    {
        if ($this->container === null) {
            $this->container = $this->defaultContainer();
        }

        return $this->container;
    }

    /**
     * @param  Container  $container DI container to be used.
     * @return static self reference.
     */
    public function setContainer(Container $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function make($definition, Container $container = null)
    {
        $container = $container ?? $this->getContainer();

        if ($definition instanceof Definition) {
            $definition = $definition->definition;
        }

        if (is_array($definition)) {
            $class = Arr::pull($definition, '__class');
            if ($class === null) {
                throw new InvalidArgumentException('Array definition must contain "__class" key.');
            }
            $constructArgs = Arr::pull($definition, '__construct()', []);
            $config = $definition;
        } else {
            $class = $definition;
            $constructArgs = [];
            $config = [];
        }

        $constructArgs = $this->makeIfDefinitionArray($constructArgs, $container);

        $object = $container->make($class, $constructArgs);

        return $this->configure($object, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function configure($object, iterable $config, Container $container = null)
    {
        $finalHandler = null;

        foreach ($config as $action => $arguments) {
            if ($action === '()') {
                $finalHandler = $arguments;

                continue;
            }

            if (substr($action, -2) === '()') {
                // method call
                $container = $container ?? $this->getContainer();

                $result = $container->call([$object, substr($action, 0, -2)], $this->makeIfDefinitionArray($arguments, $container));

                // handle immutable methods
                $object = $this->chooseNewObject($object, $result);

                continue;
            }

            if (method_exists($object, $setter = 'set'.$action)) {
                // setter
                $result = call_user_func([$object, $setter], $this->makeIfDefinition($arguments, $container));

                // handle immutable methods
                $object = $this->chooseNewObject($object, $result);

                continue;
            }

            // property
            if (property_exists($object, $action) || method_exists($object, '__set')) {
                $object->$action = $this->makeIfDefinition($arguments, $container);

                continue;
            }

            throw new InvalidArgumentException('Class "'.get_class($object).'" does not have property "'.$action.'"');
        }

        if ($finalHandler !== null) {
            $result = call_user_func($finalHandler, $object, $this);

            // handle possible immutability
            $object = $this->chooseNewObject($object, $result);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function ensure($reference, string $type = null, Container $container = null)
    {
        if (! is_object($reference)) {
            $reference = $this->make($reference, $container);
        }

        if ($type !== null) {
            if (! $reference instanceof $type) {
                throw new InvalidArgumentException('Reference "'.get_class($reference).'" does not match type "'.$type.'"');
            }
        }

        return $reference;
    }

    /**
     * Picks the new object to be used from original trusted one and new possible candidate.
     * This method is used to handle possible immutable creating methods, when method invocation
     * does not alters object state, but creates new object instead.
     *
     * @param  object  $original original object.
     * @param  object|mixed  $candidate candidate value.
     * @return object new object to be used.
     */
    private function chooseNewObject($original, $candidate)
    {
        if (is_object($candidate) && $candidate !== $original && get_class($candidate) === get_class($original)) {
            return $candidate;
        }

        return $original;
    }

    /**
     * Checks if given value is a array factory compatible definition, performs make if it is, skips - if not.
     *
     * @param  mixed  $candidate candidate value to be checked.
     * @param  Container  $container DI container to be used for making object.
     * @return object|mixed resolved object or intact candidate.
     */
    private function makeIfDefinition($candidate, Container $container = null)
    {
        $container = $container ?? $this->getContainer();

        if ($candidate instanceof Definition) {
            return $this->make($candidate->definition, $container);
        }

        return $candidate;
    }

    /**
     * Iterates over definition candidates, performing build of the values, which are valid definitions.
     *
     * @param  iterable  $candidates candidate values to be checked.
     * @param  Container  $container DI container to be used for making objects.
     * @return array
     */
    private function makeIfDefinitionArray(iterable $candidates, Container $container = null): array
    {
        $container = $container ?? $this->getContainer();

        $result = [];
        foreach ($candidates as $key => $value) {
            $result[$key] = $this->makeIfDefinition($value, $container);
        }

        return $result;
    }

    /**
     * Returns default DI container to be used for {@see $container}.
     *
     * @return Container DI container instance.
     */
    protected function defaultContainer(): Container
    {
        return \Illuminate\Container\Container::getInstance();
    }
}
