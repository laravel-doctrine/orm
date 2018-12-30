<?php

namespace LaravelDoctrine\Tests\Testing;

use Doctrine\Common\Persistence\ManagerRegistry;
use LaravelDoctrine\ORM\Testing\Factory;
use LaravelDoctrine\Tests\Stubs\Faker\Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class FactoryTest extends MockeryTestCase
{
    public function test_it_passes_along_the_class_configured_states()
    {
        /** @var Generator $faker */
        $faker = Mockery::mock(Generator::class);
        /** @var ManagerRegistry $registry */
        $registry = Mockery::mock(ManagerRegistry::class);

        $factory = new Factory($faker, $registry);
        $factory->state('SomeClass', 'withState', function () {
        });

        $builder = $factory->of('SomeClass');
        //todo this https://github.com/symfony/symfony/pull/29686 will help to move when phpunit 8 is done
        $this->assertAttributeEquals(['withState' => function () {
        }], 'states', $builder);
    }
}
