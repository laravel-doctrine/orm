<?php

namespace Brouwers\LaravelDoctrine\Migrations;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\SchemaTool;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;

class DoctrineMigrationRepository implements MigrationRepositoryInterface
{
    /**
     * The entity manager
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var SchemaTool
     */
    protected $schema;

    /**
     * @var ClassMetadataFactory
     */
    protected $metadata;

    /**
     * Create a new database migration repository instance.
     *
     * @param \Doctrine\ORM\EntityManagerInterface       $em
     * @param \Doctrine\ORM\Tools\SchemaTool             $schema
     * @param \Doctrine\ORM\Mapping\ClassMetadataFactory $metadata
     */
    public function __construct(EntityManagerInterface $em, SchemaTool $schema, ClassMetadataFactory $metadata)
    {
        $this->em       = $em;
        $this->schema   = $schema;
        $this->metadata = $metadata;
    }

    /**
     * Get the ran migrations.
     * @return array
     */
    public function getRan()
    {
        $migrations = $this->query()
                            ->getQuery()
                            ->getResult();

        $return = [];
        foreach ($migrations as $migration) {
            $return[] = $migration['migration'];
        }

        return $return;
    }

    /**
     * Get the last migration batch.
     * @return array
     */
    public function getLast()
    {
        return $this->query()
                    ->where('o.batch = :lastBatch')
                    ->setParameter('lastBatch', $this->getLastBatchNumber())
                    ->orderBy('o.migration', 'desc')->getQuery()->getResult();
    }

    /**
     * Log that a migration was run.
     *
     * @param string $file
     * @param int    $batch
     *
     * @return void
     */
    public function log($file, $batch)
    {
        $migration = new Migration($file, $batch);
        $this->em->persist($migration);
        $this->em->flush($migration);
    }

    /**
     * Remove a migration from the log.
     *
     * @param object $migration
     *
     * @return void
     */
    public function delete($migration)
    {
        $this->em->createQueryBuilder()
                    ->delete(Migration::class, 'o')
                    ->andWhere('o.migration = :migration')
                    ->setParameter('migration', $migration->migration)
                    ->getQuery()
                    ->execute();
    }

    /**
     * Get the next migration batch number.
     * @return int
     */
    public function getNextBatchNumber()
    {
        return $this->getLastBatchNumber() + 1;
    }

    /**
     * Get the last migration batch number.
     * @return int
     */
    public function getLastBatchNumber()
    {
        $result = $this->em->createQueryBuilder()
                            ->select('o, MAX(o.batch) as max_batch')
                            ->from(Migration::class, 'o')
                            ->getQuery()->getResult()[0]['max_batch'];

        return $result ?: 0;
    }

    /**
     * Create the migration repository data store.
     * @return void
     */
    public function createRepository()
    {
        $this->schema->updateSchema($this->metadata->getAllMetadata());
    }

    /**
     * Determine if the migration repository exists.
     * @return bool
     */
    public function repositoryExists()
    {
        $schema = $this->em->getConnection()->getSchemaManager();
        $tables = array_filter($schema->listTables(), function ($value) {
            return $value->getName() === 'migrations';
        });

        return !empty($tables);
    }

    /**
     * Get a query builder for the migration table.
     * @return QueryBuilder
     */
    protected function query()
    {
        return $this->em->createQueryBuilder()
                        ->select('o')
                        ->from(Migration::class, 'o');
    }

    /**
     * Set the information source to gather data.
     *
     * @param string $name
     *
     * @throws \Exception
     * @return void
     */
    public function setSource($name)
    {
    }
}
