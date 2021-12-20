<?php

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Illuminate\Contracts\Container\Container;
use LaravelDoctrine\ORM\Testing\Concerns\InteractsWithEntities;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\ExpectationFailedException;

class InteractsWithEntitiesTest extends MockeryTestCase
{
    use InteractsWithEntities;

    protected $em;
    protected $app;

    public function setUp(): void
    {
        $this->em = Mockery::mock(EntityManagerInterface::class);

        $this->app = Mockery::mock(Container::class);
        $this->app
            ->allows('make')
            ->with('em')
            ->andReturn($this->em);
    }

    public function testEntitiesMatchWithMatch()
    {
        $repository = Mockery::mock(EntityRepository::class);
        $repository->expects('findBy')
            ->with(['someField' => 'someValue'])
            ->once()
            ->andReturn(['entity']);

        $this->em->expects('getRepository')
            ->with('SomeClass')
            ->once()
            ->andReturn($repository);

        $this->entitiesMatch('SomeClass', ['someField' => 'someValue']);
    }

    public function testEntitiesMatchWithoutMatch()
    {
        $repository = Mockery::mock(EntityRepository::class);
        $repository->expects('findBy')
            ->with(['someField' => 'someValue'])
            ->once()
            ->andReturn([]);

        $this->em->expects('getRepository')
            ->with('SomeClass')
            ->once()
            ->andReturn($repository);

        $this->expectException(ExpectationFailedException::class);
        $this->entitiesMatch('SomeClass', ['someField' => 'someValue']);
    }

    public function testNoEntitiesMatchWithMatch()
    {
        $repository = Mockery::mock(EntityRepository::class);
        $repository->expects('findBy')
            ->with(['someField' => 'someValue'])
            ->once()
            ->andReturn(['entity']);

        $this->em->expects('getRepository')
            ->with('SomeClass')
            ->once()
            ->andReturn($repository);

        $this->expectException(ExpectationFailedException::class);
        $this->noEntitiesMatch('SomeClass', ['someField' => 'someValue']);
    }

    public function testNoEntitiesMatchWithoutMatch()
    {
        $repository = Mockery::mock(EntityRepository::class);
        $repository->expects('findBy')
            ->with(['someField' => 'someValue'])
            ->once()
            ->andReturn([]);

        $this->em->expects('getRepository')
            ->with('SomeClass')
            ->once()
            ->andReturn($repository);

        $this->noEntitiesMatch('SomeClass', ['someField' => 'someValue']);
    }
}
