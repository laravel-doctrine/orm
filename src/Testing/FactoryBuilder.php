<?php

namespace LaravelDoctrine\ORM\Testing;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Faker\Generator as Faker;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class FactoryBuilder
{
    /**
     * The model definitions in the container.
     *
     * @var array
     */
    protected $definitions;

    /**
     * The model being built.
     *
     * @var string
     */
    protected $class;

    /**
     * The name of the model being built.
     *
     * @var string
     */
    protected $name = 'default';

    /**
     * The number of models to build.
     *
     * @var int
     */
    protected $amount = 1;

    /**
     * The Faker instance for the builder.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * The model states.
     *
     * @var array
     */
    protected $states = [];

    /**
     * The states to apply.
     *
     * @var array
     */
    protected $activeStates = [];

    /**
     * Create an new builder instance.
     *
     * @param ManagerRegistry  $registry
     * @param string           $class
     * @param string           $name
     * @param array            $definitions
     * @param \Faker\Generator $faker
     * @param array            $afterMaking
     * @param array            $afterCreating
     */
    public function __construct(ManagerRegistry $registry, $class, $name, array $definitions, Faker $faker, array $afterMaking, array $afterCreating)
    {
        $this->name          = $name;
        $this->class         = $class;
        $this->faker         = $faker;
        $this->registry      = $registry;
        $this->definitions   = $definitions;
        $this->afterMaking   = $afterMaking;
        $this->afterCreating = $afterCreating;
    }

    /**
     * Set the amount of models you wish to create / make.
     *
     * @param int $amount
     *
     * @return $this
     */
    public function times($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Create a collection of models and persist them to the database.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes = [])
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
     * @param ManagerRegistry $registry
     * @param string          $class
     * @param string          $name
     * @param array           $definitions
     * @param Faker           $faker
     * @param array           $states
     * @param array           $afterMaking
     * @param array           $afterCreating
     *
     * @return FactoryBuilder
     */
    public static function construct(
        ManagerRegistry $registry,
        $class,
        $name,
        array $definitions,
        Faker $faker,
        array $states,
        array $afterMaking = [],
        array $afterCreating = []
    ) {
        $instance         = new static($registry, $class, $name, $definitions, $faker, $afterMaking, $afterCreating);
        $instance->states = $states;

        return $instance;
    }

    /**
     * Create a collection of models.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function make(array $attributes = [])
    {
        if ($this->amount === 1) {
            return tap($this->makeInstance($attributes), function ($instance) {
                $this->callAfterMaking(collect([$instance]));
            });
        } else {
            $results = [];

            for ($i = 0; $i < $this->amount; $i++) {
                $results[] = $this->makeInstance($attributes);
            }

            $resultsCollection = new Collection($results);

            $this->callAfterMaking($resultsCollection);

            return $resultsCollection;
        }
    }

    /**
     * Make an instance of the model with the given attributes.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    protected function makeInstance(array $attributes = [])
    {
        if (!isset($this->definitions[$this->class][$this->name])) {
            throw new InvalidArgumentException("Unable to locate factory with name [{$this->name}] [{$this->class}].");
        }

        $definition = call_user_func($this->definitions[$this->class][$this->name], $this->faker, $attributes);

        if ($definition instanceof $this->class) {
            return $definition;
        }

        $definition = $this->applyStates($definition, $attributes);

        /** @var ClassMetadata $metadata */
        $metadata = $this->registry
            ->getManagerForClass($this->class)
            ->getClassMetadata($this->class);

        $toManyRelations = (new Collection($metadata->getAssociationMappings()))
            ->keys()
            ->filter(function ($association) use ($metadata) {
                return $metadata->isCollectionValuedAssociation($association);
            })
            ->mapWithKeys(function ($association) {
                return [$association => new ArrayCollection];
            });

        return SimpleHydrator::hydrate(
            $this->class,
            $this->callClosureAttributes(array_merge($toManyRelations->all(), $definition, $attributes))
        );
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function callClosureAttributes(array $attributes)
    {
        return array_map(function ($attribute) use ($attributes) {
            if ($attribute instanceof \Closure) {
                $entity = $attribute($attributes);
                if (is_array($entity) || $entity instanceof \Traversable) {
                    foreach ($entity as $e) {
                        if (is_object($e)) {
                            $this->registry
                                ->getManagerForClass(get_class($e))
                                ->persist($e);
                        }
                    }
                } elseif (is_object($entity)) {
                    $this->registry
                        ->getManagerForClass(get_class($entity))
                        ->persist($entity);
                }

                return $entity;
            }

            return $attribute;
        }, $attributes);
    }

    /**
     * @return array
     */
    public function getStates(): array
    {
        return $this->states;
    }

    /**
     * Set the states to be applied to the model.
     *
     * @param  array|mixed $states
     * @return $this
     */
    public function states($states)
    {
        $this->activeStates = is_array($states) ? $states : func_get_args();

        return $this;
    }

    /**
     * Apply the active states to the model definition array.
     *
     * @param  array $definition
     * @param  array $attributes
     * @return array
     */
    protected function applyStates(array $definition, array $attributes = [])
    {
        foreach ($this->activeStates as $state) {
            if (! isset($this->states[$this->class][$state])) {
                if ($this->stateHasAfterCallback($state)) {
                    continue;
                }

                throw new InvalidArgumentException("Unable to locate [{$state}] state for [{$this->class}].");
            }

            $definition = array_merge(
                $definition,
                $this->stateAttributes($state, $attributes)
            );
        }

        return $definition;
    }

    /**
     * Get the state attributes.
     *
     * @param  string $state
     * @param  array  $attributes
     * @return array
     */
    protected function stateAttributes($state, array $attributes)
    {
        $stateAttributes = $this->states[$this->class][$state];

        if (! is_callable($stateAttributes)) {
            return $stateAttributes;
        }

        return call_user_func(
            $stateAttributes,
            $this->faker,
            $attributes
        );
    }

    /**
     * Run after making callbacks on a collection of models.
     *
     * @param  \Illuminate\Support\Collection $models
     * @return void
     */
    public function callAfterMaking($models)
    {
        $this->callAfter($this->afterMaking, $models);
    }

    /**
     * Run after creating callbacks on a collection of models.
     *
     * @param  \Illuminate\Support\Collection $models
     * @return void
     */
    public function callAfterCreating($models)
    {
        $this->callAfter($this->afterCreating, $models);
    }

    /**
     * Call after callbacks for each model and state.
     *
     * @param  array                          $afterCallbacks
     * @param  \Illuminate\Support\Collection $models
     * @return void
     */
    protected function callAfter(array $afterCallbacks, $models)
    {
        $states = array_merge([$this->name], $this->activeStates);

        $models->each(function ($model) use ($states, $afterCallbacks) {
            foreach ($states as $state) {
                $this->callAfterCallbacks($afterCallbacks, $model, $state);
            }
        });
    }

    /**
     * Call after callbacks for each model and state.
     *
     * @param  array                               $afterCallbacks
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  string                              $state
     * @return void
     */
    protected function callAfterCallbacks(array $afterCallbacks, $model, $state)
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
     *
     * @param  string $state
     * @return bool
     */
    protected function stateHasAfterCallback($state)
    {
        return isset($this->afterMaking[$this->class][$state]) ||
            isset($this->afterCreating[$this->class][$state]);
    }
}
