<?php

namespace LaravelDoctrine\ORM\Validation;

use Doctrine\Common\Persistence\ManagerRegistry;
use Illuminate\Validation\PresenceVerifierInterface;

class DoctrinePresenceVerifier implements PresenceVerifierInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

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
        $em      = $this->getEntityManager($collection);
        $builder = $em->createQueryBuilder();

        $builder->select('count(e)')->from($collection, 'e');
        $builder->where("e.{$column} = :{$column}");

        if (!is_null($excludeId) && $excludeId != 'NULL') {
            $idColumn = $idColumn ?: 'id';
            $builder->andWhere("e.{$idColumn} <> :{$idColumn}");
        }

        foreach ($extra as $key => $extraValue) {
            $builder->andWhere("e.{$key} = :{$key}");
        }

        $query = $builder->getQuery();
        $query->setParameter($column, $value);

        if (!is_null($excludeId) && $excludeId != 'NULL') {
            $query->setParameter($idColumn, $excludeId);
        }

        foreach ($extra as $key => $extraValue) {
            $query->setParameter($key, $extraValue);
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
        $em      = $this->getEntityManager($collection);
        $builder = $em->createQueryBuilder();

        $builder->select('count(e)')->from($collection, 'e');
        $builder->where($builder->expr()->in("e.{$column}", $values));

        foreach ($extra as $key => $extraValue) {
            $builder->andWhere("e.{$key} = :{$key}");
        }

        $query = $builder->getQuery();

        foreach ($extra as $key => $extraValue) {
            $query->setParameter($key, $extraValue);
        }

        return $query->presence();
    }

    /**
     * @param string $entity
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|null
     */
    protected function getEntityManager($entity)
    {
        return $this->registry->getManagerForClass($entity);
    }
}
