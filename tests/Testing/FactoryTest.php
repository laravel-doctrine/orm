<?php

use Doctrine\Common\Persistence\ManagerRegistry;
use LaravelDoctrine\ORM\Testing\Factory;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class FactoryTest extends MockeryTestCase
{
    public function test_it_passes_along_the_class_configured_states()
    {
        /** @var Faker\Generator $faker */
        $faker = Mockery::mock(Faker\Generator::class);
        /** @var ManagerRegistry $registry */
        $registry = Mockery::mock(ManagerRegistry::class);

        $factory = new Factory($faker, $registry);
        $factory->state('SomeClass', 'withState', function () {
        });

        $builder = $factory->of('SomeClass');
        $this->assertAttributeEquals(['withState' => function () {
        }], 'states', $builder);
    }
}
