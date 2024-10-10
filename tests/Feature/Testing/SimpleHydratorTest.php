<?php

use LaravelDoctrine\ORM\Testing\SimpleHydrator;
use PHPUnit\Framework\TestCase;

class SimpleHydratorTest extends TestCase
{
    public function test_can_hydrate_class()
    {
        $entity = SimpleHydrator::hydrate(BaseHydrateableClass::class, [
            'name' => 'Patrick',
        ]);

        $this->assertInstanceOf(BaseHydrateableClass::class, $entity);
        $this->assertEquals('Patrick', $entity->getName());
    }

    public function test_can_hydrate_with_extension_of_private_properties()
    {
        $entity = SimpleHydrator::hydrate(ChildHydrateableClass::class, [
            'name'        => 'Patrick',
            'description' => 'Hello World',
        ]);

        $this->assertInstanceOf(ChildHydrateableClass::class, $entity);
        $this->assertEquals('Patrick', $entity->getName());
        $this->assertEquals('Hello World', $entity->getDescription());
    }
}

class ChildHydrateableClass extends BaseHydrateableClass
{
    private $description;

    public function getDescription()
    {
        return $this->description;
    }
}

class BaseHydrateableClass
{
    private $name;

    public function getName()
    {
        return $this->name;
    }
}
