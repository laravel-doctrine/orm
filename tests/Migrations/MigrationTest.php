<?php

use Brouwers\LaravelDoctrine\Migrations\DoctrineMigrationRepository;
use Brouwers\LaravelDoctrine\Migrations\Migration;
use Mockery as m;

class MigrationsTest extends \PHPUnit_Framework_TestCase
{
    public function getMockRepository($em = null, $schema = null, $metadata = null)
    {
        $em     = $em ?: m::mock('Doctrine\ORM\EntityManager');
        $schema = $schema ?: m::mock('Doctrine\ORM\Tools\SchemaTool');
        ;
        $metadata = $metadata ?: m::mock('Doctrine\ORM\Mapping\ClassMetadataFactory');

        return new DoctrineMigrationRepository($em, $schema, $metadata);
    }

    public function getMockQuery()
    {
        return m::mock('\Doctrine\ORM\AbstractQuery');
    }

    public function testGetRan()
    {
        $q = $this->getMockQuery();
        $q->shouldReceive('getResult')->andReturn([
            [
                'migration' => '2014_04_19_060008_create_pages_table',
                'batch'     => '1'
            ],
            [
                'migration' => '2014_04_19_062803_create_login_log_table',
                'batch'     => '2'
            ]
        ]);
        $qb = m::mock('Doctrine\ORM\QueryBuilder');
        $qb->shouldReceive('select')->andReturnSelf();
        $qb->shouldReceive('from')->with('Brouwers\LaravelDoctrine\Migrations\Migration', m::any())->andReturnSelf();
        $qb->shouldReceive('getQuery')->andReturn($q);
        $em = m::mock('Doctrine\ORM\EntityManager');
        $em->shouldReceive('createQueryBuilder')->andReturn($qb);
        $repository = $this->getMockRepository($em);
        $this->assertEquals($repository->getRan(), [
            '2014_04_19_060008_create_pages_table',
            '2014_04_19_062803_create_login_log_table'
        ]);
    }

    public function testLog()
    {
        $em = m::mock('Doctrine\ORM\EntityManager');
        $em->shouldReceive('persist');
        $em->shouldReceive('flush');
        $repository = $this->getMockRepository($em);
        $repository->log('2013_04_19_064008_create_pages_table', 169);
    }

    public function testDelete()
    {
        $q = $this->getMockQuery();
        $q->shouldReceive('execute');
        $qb = m::mock('Doctrine\ORM\QueryBuilder');
        $qb->shouldReceive('delete')->with('Brouwers\LaravelDoctrine\Migrations\Migration', m::any())->andReturnSelf();
        $qb->shouldReceive('andWhere')->andReturnSelf();
        $qb->shouldReceive('setParameter')->andReturnSelf();
        $qb->shouldReceive('getQuery')->andReturn($q);
        $em = m::mock('Doctrine\ORM\EntityManager');
        $em->shouldReceive('createQueryBuilder')->andReturn($qb);
        $repository = $this->getMockRepository($em);
        $repository->delete(new Migration('2014_04_19_060008_create_pages_table', 169));
    }

    public function testGetBatchNumber()
    {
        $q = $this->getMockQuery();
        $q->shouldReceive('getResult')->andReturn([
            [
                'max_batch' => 4
            ]
        ]);
        $qb = m::mock('Doctrine\ORM\QueryBuilder');
        $qb->shouldReceive('select')->andReturnSelf();
        $qb->shouldReceive('from')->with('Brouwers\LaravelDoctrine\Migrations\Migration', m::any())->andReturnSelf();
        $qb->shouldReceive('getQuery')->andReturn($q);
        $em = m::mock('Doctrine\ORM\EntityManager');
        $em->shouldReceive('createQueryBuilder')->andReturn($qb);
        $repository = $this->getMockRepository($em);
        $this->assertEquals($repository->getLastBatchNumber(), 4);
        $this->assertEquals($repository->getNextBatchNumber(), 5);
    }

    public function testCreateRepository()
    {
        $schema = m::mock('Doctrine\ORM\Tools\SchemaTool');
        $schema->shouldReceive('updateSchema')->with(['schema' => ['goes', 'here']]);
        $metadata = m::mock('Doctrine\ORM\Mapping\ClassMetadataFactory');
        $metadata->shouldReceive('getAllMetadata')->andReturn(['schema' => ['goes', 'here']]);
        $repository = $this->getMockRepository(m::mock('Doctrine\ORM\EntityManager'), $schema, $metadata);
        $repository->createRepository();
    }

    public function testRepositoryExists()
    {
        $schema = m::mock();
        $schema->shouldReceive('listTables')->andReturn([
            new Table('pages'),
            new Table('partials'),
            new Table('users'),
            new Table('preferences'),
            new Table('migrations')
        ]);
        $connection = m::mock('Doctrine\DBAL\Connection');
        $connection->shouldReceive('getSchemaManager')
                   ->andReturn($schema);
        $em = m::mock('Doctrine\ORM\EntityManager');
        $em->shouldReceive('getConnection')->andReturn($connection);
        $repository = $this->getMockRepository($em);
        $this->assertTrue($repository->repositoryExists());
    }
}

class Table
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
