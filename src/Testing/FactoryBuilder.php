<?php

namespace LaravelDoctrine\ORM\Testing;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadata;
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
     */
    public function __construct(ManagerRegistry $registry, $class, $name, array $definitions, Faker $faker)
    {
        $this->name        = $name;
        $this->class       = $class;
        $this->faker       = $faker;
        $this->registry    = $registry;
        $this->definitions = $definitions;
    }

    /**
     * @param ManagerRegistry $registry
     * @param string          $class
     * @param string          $name
     * @param array           $definitions
     * @param Faker           $faker
     * @param array           $states
     *
     * @return FactoryBuilder
     */
    public static function construct(ManagerRegistry $registry, $class, $name, array $definitions, Faker $faker, array $states)
    {
        $instance         = new static($registry, $class, $name, $definitions, $faker);
        $instance->states = $states;

        return $instance;
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
        } else {
            foreach ($results as $result) {
                $manager->persist($result);
            }
        }

        $manager->flush();

        return $results;
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
            return $this->makeInstance($attributes);
        } else {
            $results = [];

            for ($i = 0; $i < $this->amount; $i++) {
                $results[] = $this->makeInstance($attributes);
            }

            return new Collection($results);
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
        $definition = $this->applyStates($definition, $attributes);

        if ($definition instanceof $this->class) {
            return $definition;
        }

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
            return $attribute instanceof \Closure ?
                $attribute($attributes) :
                $attribute;
        }, $attributes);
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
            if (! isset($this->states[$state])) {
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
        $stateAttributes = $this->states[$state];

        if (! is_callable($stateAttributes)) {
            return $stateAttributes;
        }

        return call_user_func(
            $stateAttributes,
            $this->faker, $attributes
        );
    }
}
