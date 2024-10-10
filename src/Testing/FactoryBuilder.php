<?php

declare(strict_types=1);

namespace LaravelDoctrine\ORM\Testing;

use Closure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Faker\Generator as Faker;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Traversable;

use function array_map;
use function array_merge;
use function assert;
use function call_user_func;
use function collect;
use function func_get_args;
use function is_array;
use function is_callable;
use function is_object;
use function tap;

class FactoryBuilder
{
    /**
     * The model definitions in the container.
     *
     * @var mixed[]
     */
    protected array $definitions;

    /**
     * The model being built.
     *
     * @var class-string
     */
    protected string $class;

    /**
     * The name of the model being built.
     */
    protected string $name = 'default';

    /**
     * The number of models to build.
     */
    protected int $amount = 1;

    /**
     * The Faker instance for the builder.
     */
    protected Faker $faker;

    /**
     * The model states.
     *
     * @var mixed[]
     */
    protected array $states = [];

    /**
     * The states to apply.
     *
     * @var mixed[]
     */
    protected array $activeStates = [];

    /**
     * The registered after making callbacks.
     *
     * @var mixed[]
     */
    public array $afterMaking = [];

    /**
     * The registered after creating callbacks.
     *
     * @var mixed[]
     */
    public array $afterCreating = [];

    /**
     * Create an new builder instance.
     *
     * @param class-string $class
     * @param mixed[]      $definitions
     * @param mixed[]      $afterMaking
     * @param mixed[]      $afterCreating
     */
    public function __construct(
        protected ManagerRegistry $registry,
        string $class,
        string $name,
        array $definitions,
        Faker $faker,
        array $afterMaking,
        array $afterCreating,
    ) {
        $this->name          = $name;
        $this->class         = $class;
        $this->faker         = $faker;
        $this->definitions   = $definitions;
        $this->afterMaking   = $afterMaking;
        $this->afterCreating = $afterCreating;
    }

    /**
     * Set the amount of models you wish to create / make.
     *
     * @return $this
     */
    public function times(int $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Create a collection of models and persist them to the database.
     *
     * @param mixed[] $attributes
     */
    public function create(array $attributes = []): mixed
    {
        $results = $this->make($attributes);
        $manager = $this->registry->getManagerForClass($this->class);

        if ($this->amount === 1) {
            $manager->persist($results);
            $this->callAfterCreating(collect([$results]));
        } else {
            foreach ($results as $result) {
                $manager->persist($result);
            }

            $this->callAfterCreating($results);
        }

        $manager->flush();

        return $results;
    }

    /**
     * @param mixed[] $definitions
     * @param mixed[] $states
     * @param mixed[] $afterMaking
     * @param mixed[] $afterCreating
     */
    public static function construct(
        ManagerRegistry $registry,
        string $class,
        string $name,
        array $definitions,
        Faker $faker,
        array $states,
        array $afterMaking = [],
        array $afterCreating = [],
    ): FactoryBuilder {
        $instance         = new self($registry, $class, $name, $definitions, $faker, $afterMaking, $afterCreating);
        $instance->states = $states;

        return $instance;
    }

    /**
     * Create a collection of models.
     *
     * @param mixed[] $attributes
     */
    public function make(array $attributes = []): mixed
    {
        if ($this->amount === 1) {
            return tap($this->makeInstance($attributes), function ($instance): void {
                $this->callAfterMaking(collect([$instance]));
            });
        }

        $results = [];

        for ($i = 0; $i < $this->amount; $i++) {
            $results[] = $this->makeInstance($attributes);
        }

        $resultsCollection = new Collection($results);

        $this->callAfterMaking($resultsCollection);

        return $resultsCollection;
    }

    /**
     * Make an instance of the model with the given attributes.
     *
     * @param mixed[] $attributes
     */
    protected function makeInstance(array $attributes = []): mixed
    {
        if (! isset($this->definitions[$this->class][$this->name])) {
            throw new InvalidArgumentException('Unable to locate factory with name [' . $this->name . '] [' . $this->class . ']');
        }

        $definition = call_user_func($this->definitions[$this->class][$this->name], $this->faker, $attributes);

        if ($definition instanceof $this->class) {
            return $definition;
        }

        $definition = $this->applyStates($definition, $attributes);

        $metadata = $this->registry
            ->getManagerForClass($this->class)
            ->getClassMetadata($this->class);
        assert($metadata instanceof ClassMetadata);

        $toManyRelations = (new Collection($metadata->getAssociationMappings()))
            ->keys()
            ->filter(static function ($association) use ($metadata) {
                return $metadata->isCollectionValuedAssociation($association);
            })
            ->mapWithKeys(static function ($association) {
                return [$association => new ArrayCollection()];
            });

        return SimpleHydrator::hydrate(
            $this->class,
            $this->callClosureAttributes(array_merge($toManyRelations->all(), $definition, $attributes)),
        );
    }

    /**
     * @param mixed[] $attributes
     *
     * @return mixed[]
     */
    protected function callClosureAttributes(array $attributes): array
    {
        return array_map(function ($attribute) use ($attributes) {
            if ($attribute instanceof Closure) {
                $entity = $attribute($attributes);
                if (is_array($entity) || $entity instanceof Traversable) {
                    foreach ($entity as $e) {
                        if (! is_object($e)) {
                            continue;
                        }

                        $this->registry
                            ->getManagerForClass($e::class)
                            ->persist($e);
                    }
                } elseif (is_object($entity)) {
                    $this->registry
                        ->getManagerForClass($entity::class)
                        ->persist($entity);
                }

                return $entity;
            }

            return $attribute;
        }, $attributes);
    }

    /** @return mixed[] */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * Set the states to be applied to the model.
     *
     * @return $this
     */
    public function states(mixed $states)
    {
        $this->activeStates = is_array($states) ? $states : func_get_args();

        return $this;
    }

    /**
     * Apply the active states to the model definition array.
     *
     * @param mixed[] $definition
     * @param mixed[] $attributes
     *
     * @return mixed[]
     */
    protected function applyStates(array $definition, array $attributes = []): array
    {
        foreach ($this->activeStates as $state) {
            if (! isset($this->states[$this->class][$state])) {
                if ($this->stateHasAfterCallback($state)) {
                    continue;
                }

                throw new InvalidArgumentException('Unable to locate [' . $state . '] state for [' . $this->class);
            }

            $definition = array_merge(
                $definition,
                $this->stateAttributes($state, $attributes),
            );
        }

        return $definition;
    }

    /**
     * Get the state attributes.
     *
     * @param mixed[] $attributes
     *
     * @return mixed[]
     */
    protected function stateAttributes(string $state, array $attributes): array
    {
        $stateAttributes = $this->states[$this->class][$state];

        if (! is_callable($stateAttributes)) {
            return $stateAttributes;
        }

        return call_user_func(
            $stateAttributes,
            $this->faker,
            $attributes,
        );
    }

    /**
     * Run after making callbacks on a collection of models.
     */
    public function callAfterMaking(Collection $models): void
    {
        $this->callAfter($this->afterMaking, $models);
    }

    /**
     * Run after creating callbacks on a collection of models.
     */
    public function callAfterCreating(Collection $models): void
    {
        $this->callAfter($this->afterCreating, $models);
    }

    /**
     * Call after callbacks for each model and state.
     *
     * @param mixed[] $afterCallbacks
     */
    protected function callAfter(array $afterCallbacks, Collection $models): void
    {
        $states = array_merge([$this->name], $this->activeStates);

        $models->each(function ($model) use ($states, $afterCallbacks): void {
            foreach ($states as $state) {
                $this->callAfterCallbacks($afterCallbacks, $model, $state);
            }
        });
    }

    /**
     * Call after callbacks for each model and state.
     *
     * @param mixed[] $afterCallbacks
     */
    protected function callAfterCallbacks(array $afterCallbacks, mixed $model, string $state): void
    {
        if (! isset($afterCallbacks[$this->class][$state])) {
            return;
        }

        foreach ($afterCallbacks[$this->class][$state] as $callback) {
            $callback($model, $this->faker);
        }
    }

    /**
     * Determine if the given state has an "after" callback.
     */
    protected function stateHasAfterCallback(string $state): bool
    {
        return isset($this->afterMaking[$this->class][$state]) ||
            isset($this->afterCreating[$this->class][$state]);
    }
}
