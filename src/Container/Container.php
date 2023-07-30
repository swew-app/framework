<?php

declare(strict_types=1);

namespace Swew\Framework\Container;

use Error;
use Exception;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use Swew\Framework\Base\AbstractCacheState;
use Swew\Framework\Container\Exceptions\ContainerException;
use Swew\Framework\Support\Arr;

use function array_key_exists;
use function class_exists;
use function is_callable;
use function is_null;
use function method_exists;
use function sprintf;

class Container extends AbstractCacheState implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $definitions = [];

    /**
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * @param array<string, mixed> $definitions
     */
    public function __construct(array $definitions = [])
    {
        $this->setMultiple($definitions);
    }

    /**
     * Sets multiple definitions at once.
     *
     * @param array<string, mixed> $definitions
     * @psalm-suppress MixedAssignment
     */
    public function setMultiple(array $definitions): void
    {
        foreach ($definitions as $id => $definition) {
            $this->set($id, $definition);
        }
    }

    /**
     * Sets definition to the container.
     *
     * @param string $id
     * @param mixed $definition
     */
    public function set(string $id, mixed $definition): void
    {
        if ($this->hasInstance($id)) {
            unset($this->instances[$id]);
        }

        $this->definitions[$id] = $definition;
    }

    /**
     * Returns `true` if the container can return an instance for this ID, otherwise `false`.
     *
     * @param string $id
     * @return bool
     */
    private function hasInstance(string $id): bool
    {
        return array_key_exists($id, $this->instances);
    }

    /**
     * Gets instance by definition from the container by ID.
     *
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function get(string $id): mixed
    {
        if ($this->hasInstance($id)) {
            return $this->instances[$id];
        }

        if ($this->has($id)) {
            $isString = is_string($this->definitions[$id]);

            if ($isString && is_callable($this->definitions[$id])) {

                return $this->definitions[$id]($this);

            } elseif ($isString && class_exists($this->definitions[$id])) {

                $definedInstance = $this->getNew($this->definitions[$id]);

                if (is_callable($definedInstance)) {

                    return $this->instances[$id] = $definedInstance();

                }

                return $this->instances[$id] = $definedInstance;
            } else {

                return $this->definitions[$id];

            }
        }

        $subDef = Arr::get($this->definitions, $id);

        if (isset($subDef)) {
            return $subDef;
        }

        $this->instances[$id] = $this->getNew($id);

        return $this->instances[$id];
    }

    /**
     * Returns 'true' if the dependency with this ID was sets, otherwise 'false'.
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions);
    }

    /**
     * Always gets a new instance by definition from the container by ID.
     *
     * @param string $id
     * @param string|null $method
     * @return mixed
     * @psalm-suppress MixedAssignment
     * @throws Exception
     */
    public function getNew(string $id, ?string $method = null): mixed
    {
        $instance = $this->createInstance($id);

        if (!is_null($method) && method_exists($instance, $method)) {
            return $instance->$method($this);
        }

        return $instance;
    }

    /**
     * Create instance by definition from the container by ID.
     *
     * @param string $id
     * @return mixed
     *
     * @throws Exception
     * @psalm-suppress MixedArgument
     */
    private function createInstance(string $id): mixed
    {
        if (!$this->has($id)) {
            if (class_exists($id)) {
                return $this->createObject($id);
            }

            throw new Exception(sprintf('`%s` is not set in container and is not a class name.', $id));
        }

        if (is_string($this->definitions[$id])) {
            if (class_exists($this->definitions[$id])) {
                $item = $this->createObject($this->definitions[$id]);

                if (is_callable($item)) {
                    return $item($this);
                }

                return $item;
            }
        }

        if (is_callable($this->definitions[$id])) {
            return $this->definitions[$id]($this);
        }

        return $this->definitions[$id];
    }

    /**
     * Create object by class name.
     *
     * If the object has dependencies in the constructor, it tries to create them too.
     *
     * @param string $className
     * @return object
     *
     * @throws ContainerException|ReflectionException If unable to create object.
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress MixedAssignment
     */
    private function createObject(string $className): object
    {
        if ($this->hasCacheItem($className)) {
            return $this->getCacheItem($className);
        }

        try {
            $reflection = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new ContainerException(sprintf('Unable to create object `%s`.', $className), 0, $e);
        }

        if (($constructor = $reflection->getConstructor()) === null) {
            $this->addCacheItem($className);

            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            if ($type = $parameter->getType()) {
                /** @var ReflectionNamedType $type */
                $typeName = $type->getName();

                if (!$type->isBuiltin() && ($this->has($typeName) || class_exists($typeName))) {
                    $arguments[] = $this->get($typeName);
                    continue;
                }

                if ($type->isBuiltin() && $typeName === 'array' && !$parameter->isDefaultValueAvailable()) {
                    $arguments[] = [];
                    continue;
                }
            }

            if ($parameter->isDefaultValueAvailable()) {
                try {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                } catch (ReflectionException $e) {
                    throw new ContainerException(sprintf(
                        'Unable to create object `%s`. Unable to get default value of constructor parameter: `%s`.',
                        $reflection->getName(),
                        $parameter->getName()
                    ));
                }
            }

            throw new ContainerException(sprintf(
                'Unable to create object `%s`. Unable to process a constructor parameter: `%s`.',
                $reflection->getName(),
                $parameter->getName()
            ));
        }

        $this->addCacheItem($className, $arguments);

        return $reflection->newInstanceArgs($arguments);
    }

    // region [cache]

    private function hasCacheItem(string $className): bool
    {
        if ($this->isUseCache) {
            $this->loadCache();
        }

        return array_key_exists($className, $this->cachedData);
    }

    private function getCacheItem(string $className): object
    {
        $args = array_map(function ($key) {
            if (class_exists($key)) {
                return $this->get($key);
            }

            return $key;
        }, $this->cachedData[$className]);

        try {
            return new $className(...$args);
        } catch (Error $e) {
            $this->clearCache();

            throw $e;
        }
    }

    private function addCacheItem(string $className, array $args = []): void
    {
        $this->cachedData[$className] = array_map(function ($item) {
            return is_object($item) ? get_class($item) : $item;
        }, $args);

        $this->isNeedWriteCache = true;
    }

    // endregion
}
