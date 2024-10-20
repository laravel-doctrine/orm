<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Resolvers;

use Doctrine\ORM\Mapping\EntityListenerResolver as ResolverContract;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

use function gettype;
use function is_object;
use function sprintf;
use function trim;

class EntityListenerResolver implements ResolverContract
{
    /** @var object[] Map of class name to entity listener instances. */
    private array $instances = [];

    public function __construct(private Container $container)
    {
    }

    public function clear(string|null $className = null): void
    {
        if ($className) {
            unset($this->instances[$className = trim($className, '\\')]);

            return;
        }

        $this->instances = [];
    }

    public function resolve(string $className): object
    {
        $hasInstance = isset($this->instances[$className = trim($className, '\\')]);
        if ($hasInstance) {
            return $this->instances[$className];
        }

        return $this->instances[$className] = $this->container->make($className);
    }

    public function register(object $object): void
    {
        $this->instances[$object::class] = $object;
    }
}
