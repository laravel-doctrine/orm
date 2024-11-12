<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Testing;

use LaravelDoctrine\ORM\Testing\SimpleHydrator;
use LaravelDoctrineTest\ORM\Assets\Testing\AncestorHydrateableClass;
use LaravelDoctrineTest\ORM\Assets\Testing\ChildHydrateableClass;
use LaravelDoctrineTest\ORM\TestCase;

class SimpleHydratorTest extends TestCase
{
    public function testCanHydrateClass(): void
    {
        $entity = SimpleHydrator::hydrate(AncestorHydrateableClass::class, ['name' => 'Patrick']);

        $this->assertInstanceOf(AncestorHydrateableClass::class, $entity);
        $this->assertEquals('Patrick', $entity->getName());
    }

    public function testCanHydrateWithExtensionOfPrivateProperties(): void
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
