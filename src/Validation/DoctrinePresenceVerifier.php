<?php

namespace LaravelDoctrine\ORM\Validation;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Illuminate\Validation\PresenceVerifierInterface;
use InvalidArgumentException;

class DoctrinePresenceVerifier implements PresenceVerifierInterface
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
        $builder = $this->select($collection);
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

        return $query->getSingleScalarResult();
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
        $builder = $this->select($collection);
        $builder->where($builder->expr()->in("e.{$column}", $values));

        $this->queryExtraConditions($extra, $builder);

        return $builder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string $collection
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function select($collection)
    {
        $em      = $this->getEntityManager($collection);
        $builder = $em->createQueryBuilder();

        $builder->select('count(e)')->from($collection, 'e');

        return $builder;
    }

    /**
     * @param array        $extra
     * @param QueryBuilder $builder
     */
    protected function queryExtraConditions(array $extra, QueryBuilder $builder)
    {
        foreach ($extra as $key => $extraValue) {
            $builder->andWhere("e.{$key} = :" . $this->prepareParam($key));
            $builder->setParameter($this->prepareParam($key), $extraValue);
        }
    }

    /**
     * @param string $entity
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|null
     */
    protected function getEntityManager($entity)
    {
        if (!is_null($this->connection)) {
            return $this->registry->getManager($this->connection);
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
