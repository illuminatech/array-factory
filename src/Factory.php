<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2015 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\ArrayFactory;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Contracts\Container\Container;

/**
 * Factory
 *
 * @see FactoryContract
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

        if (is_array($definition)) {
            $class = Arr::pull($definition, '__class');
            $parameters = Arr::pull($definition, '__construct()');
            $config = $definition;
        } else {
            $class = $definition;
            $parameters = [];
            $config = [];
        }

        $object = $container->make($class, $parameters);

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
                $result = call_user_func_array([$object, substr($action, 0, -2)], $arguments);

                // handle immutable methods
                $object = $this->chooseNewObject($object, $result);

                continue;
            }

            if (method_exists($object, $setter = 'set'.$action)) {
                // setter
                $result = call_user_func([$object, $setter], $arguments);

                // handle immutable methods
                $object = $this->chooseNewObject($object, $result);

                continue;
            }

            // property
            if (property_exists($object, $action) || method_exists($object, '__set')) {
                $object->$action = $arguments;

                continue;
            }

            throw new InvalidArgumentException('Class "'.get_class($object).'" does not have property "'.$action.'"');
        }

        if ($finalHandler !== null) {
            $result = call_user_func($finalHandler, $object);

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
     * @param  object|mixed $candidate candidate value.
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
     * Returns default DI container to be used for {@link $container}.
     *
     * @return Container DI container instance.
     */
    protected function defaultContainer(): Container
    {
        return \Illuminate\Container\Container::getInstance();
    }
}
