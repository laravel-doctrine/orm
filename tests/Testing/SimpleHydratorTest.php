<?php

namespace LaravelDoctrine\Tests\Testing;

use LaravelDoctrine\ORM\Testing\SimpleHydrator;
use PHPUnit\Framework\TestCase;

class SimpleHydratorTest extends TestCase
{
    public function test_can_hydrate_class()
    {
        $entity = SimpleHydrator::hydrate(\LaravelDoctrine\Tests\Mocks\BaseHydrateableClass::class, [
            'name' => 'Patrick',
        ]);

        $this->assertInstanceOf(\LaravelDoctrine\Tests\Mocks\BaseHydrateableClass::class, $entity);
        $this->assertEquals('Patrick', $entity->getName());
    }

    public function test_can_hydrate_with_extension_of_private_properties()
    {
        $entity = SimpleHydrator::hydrate(\LaravelDoctrine\Tests\Mocks\ChildHydrateableClass::class, [
            'name'        => 'Patrick',
            'description' => 'Hello World',
        ]);

        $this->assertInstanceOf(\LaravelDoctrine\Tests\Mocks\ChildHydrateableClass::class, $entity);
        $this->assertEquals('Patrick', $entity->getName());
        $this->assertEquals('Hello World', $entity->getDescription());
    }
}
