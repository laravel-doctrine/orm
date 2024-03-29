<?php

namespace LaravelDoctrine\ORM\Validation;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Validation\DatabasePresenceVerifierInterface;
use InvalidArgumentException;

class DoctrinePresenceVerifier implements DatabasePresenceVerifierInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * The database connection to use.
     *
     * @var string
     */
    protected $connection = null;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Count the number of objects in a collection having the given value.
     *
     * @param string $collection
     * @param string $column
     * @param string $value
     * @param int    $excludeId
     * @param string $idColumn
     * @param array  $extra
     *
     * @return int
     */
    public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = [])
    {
        $builder = $this->selectCount($collection);
        $builder->where("e.{$column} = :" . $this->prepareParam($column));

        if (!is_null($excludeId) && $excludeId != 'NULL') {
            $idColumn = $idColumn ?: 'id';
            $builder->andWhere("e.{$idColumn} <> :" . $this->prepareParam($idColumn));
        }

        $this->queryExtraConditions($extra, $builder);

        $query = $builder->getQuery();
        $query->setParameter($this->prepareParam($column), $value);

        if (!is_null($excludeId) && $excludeId != 'NULL') {
            $query->setParameter($this->prepareParam($idColumn), $excludeId);
        }

        return (int) $query->getSingleScalarResult();
    }

    /**
     * Count the number of objects in a collection with the given values.
     *
     * @param string $collection
     * @param string $column
     * @param array  $values
     * @param array  $extra
     *
     * @return int
     */
    public function getMultiCount($collection, $column, array $values, array $extra = [])
    {
        $builder = $this->selectCount($collection);
        $builder->where($builder->expr()->in("e.{$column}", $values));

        $this->queryExtraConditions($extra, $builder);

        return (int) $builder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $entity
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function selectCount($entity)
    {
        $em      = $this->getEntityManager($entity);
        $builder = $em->createQueryBuilder();

        $builder->select('count(e)')->from($entity, 'e');

        return $builder;
    }

    /**
     * @param array        $extra
     * @param QueryBuilder $builder
     */
    protected function queryExtraConditions(array $extra, QueryBuilder $builder)
    {
        foreach ($extra as $key => $extraValue) {
            if ($extraValue === 'NULL') {
                $builder->andWhere("e.{$key} IS NULL");
            } elseif ($extraValue === 'NOT_NULL') {
                $builder->andWhere("e.{$key} IS NOT NULL");
            } elseif (\Illuminate\Support\Str::startsWith($extraValue, '!')) {
                $builder->andWhere("e.{$key} != :" . $this->prepareParam($key));
                $builder->setParameter($this->prepareParam($key), mb_substr($extraValue, 1));
            } else {
                $builder->andWhere("e.{$key} = :" . $this->prepareParam($key));
                $builder->setParameter($this->prepareParam($key), $extraValue);
            }
        }
    }

    /**
     * @param string $entity
     *
     * @return \Doctrine\Persistence\ObjectManager
     */
    protected function getEntityManager($entity)
    {
        if (!is_null($this->connection)) {
            return $this->registry->getManager($this->connection);
        }

        if (substr($entity, 0, 1) === '\\') {
            $entity = substr($entity, 1);
        }

        $em = $this->registry->getManagerForClass($entity);

        if ($em === null) {
            throw new InvalidArgumentException(sprintf("No Entity Manager could be found for [%s].", $entity));
        }

        return $em;
    }

    /**
     * @param string $column
     *
     * @return string
     */
    protected function prepareParam($column)
    {
        return str_replace('.', '', $column);
    }

    /**
     * Set the connection to be used.
     *
     * @param string $connection
     *
     * @return void
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }
}
