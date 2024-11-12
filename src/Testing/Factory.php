<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Testing;

use ArrayAccess;
use Doctrine\Persistence\ManagerRegistry;
use Faker\Generator as Faker;
use Symfony\Component\Finder\Finder;

use function array_merge;
use function call_user_func;
use function database_path;
use function is_dir;

class Factory implements ArrayAccess
{
    /**
     * The model definitions in the container.
     *
     * @var mixed[]
     */
    protected array $definitions = [];

    /**
     * The registered model states.
     *
     * @var mixed[]
     */
    protected array $states;

    /**
     * The registered after making callbacks.
     *
     * @var mixed[]
     */
    protected array $afterMaking = [];

    /**
     * The registered after creating callbacks.
     *
     * @var mixed[]
     */
    protected array $afterCreating = [];

    /**
     * The Faker instance for the builder.
     */
    protected Faker $faker;

    /**
     * Create a new factory instance.
     */
    public function __construct(Faker $faker, protected ManagerRegistry $registry)
    {
        $this->faker = $faker;
    }

    /**
     * Create a new factory container.
     *
     * @return static
     */
    public static function construct(Faker $faker, ManagerRegistry $registry, string|null $pathToFactories = null): static
    {
        $pathToFactories = $pathToFactories ?: database_path('factories');

        return (new self($faker, $registry))->load($pathToFactories);
    }

    /**
     * Define a class with a given short-name.
     */
    public function defineAs(string $class, string $name, callable $attributes): void
    {
        $this->define($class, $attributes, $name);
    }

    /**
     * Define a class with a given set of attributes.
     *
     * @return $this
     */
    public function define(string $class, callable $attributes, string $name = 'default')
    {
        $this->definitions[$class][$name] = $attributes;

        return $this;
    }

    /**
     * Define a state with a given set of attributes.
     *
     * @return $this
     */
    public function state(string $class, string $state, callable|array $attributes)
    {
        $this->states[$class][$state] = $attributes;

        return $this;
    }

    /**
     * Define a callback to run after making a model.
     *
     * @return $this
     */
    public function afterMaking(string $class, callable $callback, string $name = 'default')
    {
        $this->afterMaking[$class][$name][] = $callback;

        return $this;
    }

    /**
     * Define a callback to run after making a model with given state.
     *
     * @return $this
     */
    public function afterMakingState(string $class, string $state, callable $callback)
    {
        return $this->afterMaking($class, $callback, $state);
    }

    /**
     * Define a callback to run after creating a model.
     *
     * @return $this
     */
    public function afterCreating(string $class, callable $callback, string $name = 'default')
    {
        $this->afterCreating[$class][$name][] = $callback;

        return $this;
    }

    /**
     * Define a callback to run after creating a model with given state.
     *
     * @return $this
     */
    public function afterCreatingState(string $class, string $state, callable $callback)
    {
        return $this->afterCreating($class, $callback, $state);
    }

    /**
     * Create an instance of the given model and persist it to the database.
     *
     * @param mixed[] $attributes
     */
    public function create(string $class, array $attributes = []): mixed
    {
        return $this->of($class)->create($attributes);
    }

    /**
     * Create an instance of the given model and type and persist it to the database.
     *
     * @param mixed[] $attributes
     */
    public function createAs(string $class, string $name, array $attributes = []): mixed
    {
        return $this->of($class, $name)->create($attributes);
    }

    /**
     * Create an instance of the given model.
     *
     * @param mixed[] $attributes
     */
    public function make(string $class, array $attributes = []): mixed
    {
        return $this->of($class)->make($attributes);
    }

    /**
     * Create an instance of the given model and type.
     *
     * @param mixed[] $attributes
     */
    public function makeAs(string $class, string $name, array $attributes = []): mixed
    {
        return $this->of($class, $name)->make($attributes);
    }

    /**
     * Get the raw attribute array for a given named model.
     *
     * @param mixed[] $attributes
     *
     * @return mixed[]
     */
    public function rawOf(string $class, string $name, array $attributes = []): array
    {
        return $this->raw($class, $attributes, $name);
    }

    /**
     * Get the raw attribute array for a given model.
     *
     * @param mixed[] $attributes
     *
     * @return mixed[]
     */
    public function raw(string $class, array $attributes = [], string $name = 'default'): array
    {
        $raw = call_user_func($this->definitions[$class][$name], $this->faker);

        return array_merge($raw, $attributes);
    }

    /**
     * Create a builder for the given model.
     */
    public function of(string $class, string $name = 'default'): FactoryBuilder
    {
        return FactoryBuilder::construct(
            $this->registry,
            $class,
            $name,
            $this->definitions,
            $this->faker,
            $this->states ?? [],
            $this->afterMaking,
            $this->afterCreating,
        );
    }

    /**
     * Load factories from path.
     *
     * @return $this
     */
    public function load(string $path)
    {
        $factory = $this;

        if (is_dir($path)) {
            foreach (Finder::create()->files()->in($path) as $file) {
                require $file->getRealPath();
            }
        }

        return $factory;
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->definitions[$offset]);
    }

    /**
     * Get the value of the given offset.
     *
     * @param string $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->make($offset);
    }

    /**
     * Set the given offset to the given value.
     *
     * @param string   $offset
     * @param callable $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->define($offset, $value);
    }

    /**
     * Unset the value at the given offset.
     *
     * @param string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->definitions[$offset]);
    }
}
