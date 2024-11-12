<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Testing;

use Doctrine\Persistence\ManagerRegistry;
use Faker;
use LaravelDoctrine\ORM\Testing\Factory;
use LaravelDoctrineTest\ORM\MockeryTestCase;
use Mockery;

use function assert;
use function property_exists;

class FactoryTest extends MockeryTestCase
{
    public function testItPassesAlongTheClassConfiguredStates(): void
    {
        $faker = Mockery::mock(Faker\Generator::class);
        assert($faker instanceof Faker\Generator);
        $registry = Mockery::mock(ManagerRegistry::class);
        assert($registry instanceof ManagerRegistry);

        $factory = new Factory($faker, $registry);
        $factory->state('SomeClass', 'withState', static function (): void {
        });

        $builder = $factory->of('SomeClass');

        $this->assertTrue(property_exists($builder, 'states'));
        $this->assertArrayHasKey('SomeClass', $builder->getStates());
        $this->assertArrayHasKey('withState', $builder->getStates()['SomeClass']);
    }

    public function testItPassesAlongAfterCreateingCallback(): void
    {
        $faker = Mockery::mock(Faker\Generator::class);
        assert($faker instanceof Faker\Generator);
        $registry = Mockery::mock(ManagerRegistry::class);
        assert($registry instanceof ManagerRegistry);

        $factory = new Factory($faker, $registry);
        $factory->afterCreating('SomeClass', static function (): void {
        });

        $builder = $factory->of('SomeClass');

        $this->assertTrue(property_exists($builder, 'afterCreating'));
        $this->assertArrayHasKey('SomeClass', $builder->afterCreating);
        $this->assertArrayHasKey('default', $builder->afterCreating['SomeClass']);
    }

    public function testItPassesAlongAfterMakingCallback(): void
    {
        $faker = Mockery::mock(Faker\Generator::class);
        assert($faker instanceof Faker\Generator);
        $registry = Mockery::mock(ManagerRegistry::class);
        assert($registry instanceof ManagerRegistry);

        $factory = new Factory($faker, $registry);
        $factory->afterMaking('SomeClass', static function (): void {
        });

        $builder = $factory->of('SomeClass');
        $this->assertTrue(property_exists($builder, 'afterMaking'));
        $this->assertArrayHasKey('SomeClass', $builder->afterMaking);
        $this->assertArrayHasKey('default', $builder->afterMaking['SomeClass']);
    }

    public function testItPassesAlongAfterCreatingStateCallback(): void
    {
        $faker = Mockery::mock(Faker\Generator::class);
        assert($faker instanceof Faker\Generator);
        $registry = Mockery::mock(ManagerRegistry::class);
        assert($registry instanceof ManagerRegistry);

        $factory = new Factory($faker, $registry);
        $factory->afterCreatingState('SomeClass', 'withState', static function (): void {
        });

        $builder = $factory->of('SomeClass');
        $this->assertTrue(property_exists($builder, 'afterCreating'));
        $this->assertArrayHasKey('SomeClass', $builder->afterCreating);
        $this->assertArrayHasKey('withState', $builder->afterCreating['SomeClass']);
    }

    public function testItPassesAlongAfterMakingStateCallback(): void
    {
        $faker = Mockery::mock(Faker\Generator::class);
        assert($faker instanceof Faker\Generator);
        $registry = Mockery::mock(ManagerRegistry::class);
        assert($registry instanceof ManagerRegistry);

        $factory = new Factory($faker, $registry);
        $factory->afterMakingState('SomeClass', 'withState', static function (): void {
        });

        $builder = $factory->of('SomeClass');

        $this->assertTrue(property_exists($builder, 'afterMaking'));
        $this->assertArrayHasKey('SomeClass', $builder->afterMaking);
        $this->assertArrayHasKey('withState', $builder->afterMaking['SomeClass']);
    }
}
