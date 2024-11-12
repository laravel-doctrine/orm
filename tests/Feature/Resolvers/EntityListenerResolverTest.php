<?php

declare(strict_types=1);

namespace LaravelDoctrineTest\ORM\Feature\Resolvers;

use Doctrine\ORM\Mapping\EntityListenerResolver as ResolverContract;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Resolvers\EntityListenerResolver;
use LaravelDoctrineTest\ORM\TestCase;
use Mockery as m;
use stdClass;
use TypeError;

class EntityListenerResolverTest extends TestCase
{
    private Container $container;

    private EntityListenerResolver $resolver;

    protected function setUp(): void
    {
        $this->container = m::mock(Container::class);
        $this->resolver  = new EntityListenerResolver($this->container);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        m::close();

        parent::tearDown();
    }

    public function testImplementsDoctrineInterface(): void
    {
        $this->assertInstanceOf(ResolverContract::class, $this->resolver);
    }

    public function testResolvesFromContainer(): void
    {
        $object = new stdClass();
        $this->container->shouldReceive('make')->with('class')->andReturn($object);

        $resolvedObject = $this->resolver->resolve('class');

        $this->assertSame($object, $resolvedObject, 'Resolver should retrieve the object from the container');
    }

    public function testHoldsReferenceAfterResolve(): void
    {
        $object        = new stdClass();
        $anotherObject = new stdClass();
        $this->container->shouldReceive('make')->with('class')->once()->andReturn($object, $anotherObject);

        $resolvedObject      = $this->resolver->resolve('class');
        $resolvedObjectAgain = $this->resolver->resolve('class');

        $this->assertSame($object, $resolvedObject, 'Resolver should retrieve the object from the container');
        $this->assertSame($object, $resolvedObjectAgain, 'Resolver should retrieve the object from its own reference');
    }

    public function testClearsHeldReference(): void
    {
        $object        = new stdClass();
        $anotherObject = new stdClass();
        $this->container->shouldReceive('make')->with('class')->times(2)->andReturn($object, $anotherObject);

        $this->resolver->resolve('class');
        $this->resolver->clear('class');

        $resolvedObjectAgain = $this->resolver->resolve('class');

        $this->assertSame($anotherObject, $resolvedObjectAgain, 'Resolver should got back to container after clear');
    }

    public function testClearsAllHeldReferences(): void
    {
        $object           = new stdClass();
        $anotherObject    = new stdClass();
        $oneMoreObject    = new stdClass();
        $yetAnotherObject = new stdClass();
        $this->container->shouldReceive('make')->with('class')->times(2)->andReturn($object, $anotherObject);
        $this->container->shouldReceive('make')->with('class2')->times(2)->andReturn($oneMoreObject, $yetAnotherObject);

        $this->resolver->resolve('class');
        $this->resolver->resolve('class2');

        $this->resolver->clear();

        $resolvedAnotherObject    = $this->resolver->resolve('class');
        $resolvedYetAnotherObject = $this->resolver->resolve('class2');

        $this->assertSame($anotherObject, $resolvedAnotherObject, 'Resolver should retrieve the object from the container');
        $this->assertSame($yetAnotherObject, $resolvedYetAnotherObject, 'Resolver should retrieve the object from the container');
    }

    public function testAllowsDirectlyRegisteringListeners(): void
    {
        $object = new stdClass();

        $this->resolver->register($object);

        $resolvedObject = $this->resolver->resolve($object::class);

        $this->assertSame($object, $resolvedObject, 'Resolver should not use container when directly registering');
    }

    public function testDoesNotAllowRegisteringNonObjects(): void
    {
        $this->expectException(TypeError::class);
        $this->resolver->register('foo');
    }
}
